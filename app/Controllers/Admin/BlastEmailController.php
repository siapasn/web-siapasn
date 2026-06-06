<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\EmailService;

class BlastEmailController extends BaseController
{
    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Halaman utama — form kirim + riwayat.
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil daftar user aktif untuk dropdown
        $users = $db->table('users')
            ->select('id, nama, email')
            ->where('role', 'user')
            ->where('is_active', 1)
            ->where('email_verified_at IS NOT NULL', null, false)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        // Jumlah subscriber
        $totalSubscriber = $db->table('users_subscribe')->countAllResults();

        // Daftar subscriber untuk pilihan tertentu
        $subscribers = $db->table('users_subscribe')
            ->select('id, name, email')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        // Riwayat blast email (20 terakhir)
        $riwayat = $db->table('blast_email be')
            ->select('be.*, u.nama AS sent_by_nama')
            ->join('users u', 'u.id = be.sent_by', 'left')
            ->orderBy('be.created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        return view('admin/blast-email/index', [
            'users'           => $users,
            'totalSubscriber' => $totalSubscriber,
            'subscribers'     => $subscribers,
            'riwayat'         => $riwayat,
            'menus'           => $this->getMenus(),
        ]);
    }

    /**
     * Proses kirim email — expand ke individual records (1 email per row).
     * Jika tipe 'all' atau 'subscribe', baca daftar penerima dari DB,
     * lalu insert 1 record per email ke blast_email dengan status pending.
     */
    public function send()
    {
        $rules = [
            'subject' => 'required|min_length[3]|max_length[255]',
            'body'    => 'required',
            'tipe'    => 'required|in_list[all,single,subscribe,subscribe_single]',
        ];

        if ($this->request->getPost('tipe') === 'single') {
            $rules['target_user_id'] = 'required|is_natural_no_zero';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db      = \Config\Database::connect();
        $subject = $this->request->getPost('subject');
        $body    = $this->request->getPost('body');
        $tipe    = $this->request->getPost('tipe');
        $sentBy  = session()->get('user_id');
        $now     = date('Y-m-d H:i:s');

        // Kumpulkan daftar penerima berdasarkan tipe
        $recipients = [];

        if ($tipe === 'single') {
            $targetUserId = (int) $this->request->getPost('target_user_id');
            $user = $db->table('users')->where('id', $targetUserId)->get()->getRowArray();
            if (! $user) {
                return redirect()->back()->withInput()->with('error', 'User tidak ditemukan.');
            }
            $recipients[] = [
                'email'   => $user['email'],
                'nama'    => $user['nama'],
                'user_id' => $user['id'],
            ];

        } elseif ($tipe === 'subscribe') {
            $subs = $db->table('users_subscribe')
                ->select('id, email, name')
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
            foreach ($subs as $s) {
                $recipients[] = [
                    'email'   => $s['email'],
                    'nama'    => $s['name'] ?: 'Subscriber',
                    'user_id' => null,
                ];
            }

        } elseif ($tipe === 'subscribe_single') {
            $subscriberIds = $this->request->getPost('target_subscriber_ids') ?? [];
            if (empty($subscriberIds)) {
                return redirect()->back()->withInput()->with('error', 'Pilih minimal satu subscriber.');
            }
            $subs = $db->table('users_subscribe')
                ->whereIn('id', $subscriberIds)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
            foreach ($subs as $s) {
                $recipients[] = [
                    'email'   => $s['email'],
                    'nama'    => $s['name'] ?: 'Subscriber',
                    'user_id' => null,
                ];
            }

        } else {
            // tipe = 'all' → semua user aktif yang sudah verifikasi email
            $users = $db->table('users')
                ->select('id, email, nama')
                ->where('role', 'user')
                ->where('is_active', 1)
                ->where('email_verified_at IS NOT NULL', null, false)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
            foreach ($users as $u) {
                $recipients[] = [
                    'email'   => $u['email'],
                    'nama'    => $u['nama'],
                    'user_id' => $u['id'],
                ];
            }
        }

        if (empty($recipients)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada penerima yang ditemukan.');
        }

        // Insert 1 record per penerima ke blast_email
        $batchData = [];
        foreach ($recipients as $r) {
            $batchData[] = [
                'subject'        => $subject,
                'body'           => $body,
                'tipe'           => 'single', // semua di-convert jadi single
                'target_user_id' => $r['user_id'],
                'target_email'   => $r['email'],
                'target_nama'    => $r['nama'],
                'total_sent'     => 0,
                'total_failed'   => 0,
                'sent_by'        => $sentBy,
                'status'         => 'pending',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // Insert batch (chunk per 100 untuk menghindari limit)
        $chunks = array_chunk($batchData, 100);
        foreach ($chunks as $chunk) {
            $db->table('blast_email')->insertBatch($chunk);
        }

        $totalInserted = count($batchData);

        return redirect()->to(base_url('admin/blast-email'))
            ->with('success', "Berhasil menambahkan {$totalInserted} email ke antrian. Akan dikirim satu per satu oleh sistem.");
    }

    /**
     * Kirim email ke satu penerima.
     */
    private function sendSingleEmail(EmailService $emailService, string $toEmail, string $nama, string $subject, string $body): bool
    {
        try {
            $config = $this->getSmtpConfig();
            $email  = \Config\Services::email();

            $emailConfig = new \Config\Email();
            $emailConfig->protocol    = 'smtp';
            $emailConfig->SMTPHost    = $config['host'];
            $emailConfig->SMTPPort    = (int) $config['port'];
            $emailConfig->SMTPUser    = $config['username'];
            $emailConfig->SMTPPass    = $config['password'];
            $emailConfig->SMTPCrypto  = ($config['encryption'] !== 'none') ? $config['encryption'] : '';
            $emailConfig->SMTPTimeout = (int) ($config['timeout'] ?? 10);
            $emailConfig->mailType    = 'html';
            $emailConfig->charset     = 'utf-8';

            $email = \Config\Services::email($emailConfig, false);
            $email->clear();
            $email->setFrom($config['from'], $config['from_name']);
            $email->setTo($toEmail);
            $email->setSubject($subject);
            $email->setMessage(view('emails/blast', [
                'nama'    => $nama,
                'subject' => $subject,
                'body'    => $body,
            ]));
            $email->setMailType('html');

            return $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', "Blast email gagal ke {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load SMTP config dari master_aplikasi atau .env.
     */
    private function getSmtpConfig(): array
    {
        $defaults = [
            'host'       => env('email.host', 'smtp.gmail.com'),
            'port'       => env('email.port', '587'),
            'username'   => env('email.username', ''),
            'password'   => env('email.password', ''),
            'encryption' => env('email.encryption', 'tls'),
            'from'       => env('email.from', 'noreply@cpns.test'),
            'from_name'  => env('email.from_name', env('app.name', 'SiapASN Simulation Center')),
            'timeout'    => env('email.timeout', '10'),
        ];

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('master_aplikasi')) {
                return $defaults;
            }

            $rows = $db->table('master_aplikasi')
                ->whereIn('config_key', [
                    'email_host', 'email_port', 'email_username',
                    'email_password', 'email_encryption', 'email_from', 'email_from_name',
                ])
                ->get()->getResultArray();

            if (empty($rows)) {
                return $defaults;
            }

            $cfg = [];
            foreach ($rows as $row) {
                $cfg[$row['config_key']] = $row['config_value'];
            }

            return [
                'host'       => $cfg['email_host']       ?? $defaults['host'],
                'port'       => $cfg['email_port']       ?? $defaults['port'],
                'username'   => $cfg['email_username']   ?? $defaults['username'],
                'password'   => $cfg['email_password']   ?? $defaults['password'],
                'encryption' => $cfg['email_encryption'] ?? $defaults['encryption'],
                'from'       => $cfg['email_from']       ?? $defaults['from'],
                'from_name'  => $cfg['email_from_name']  ?? $defaults['from_name'],
                'timeout'    => $defaults['timeout'],
            ];
        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    /**
     * Lihat detail/preview blast email yang sudah dikirim.
     */
    public function preview(int $id)
    {
        $db = \Config\Database::connect();
        $blast = $db->table('blast_email')->where('id', $id)->get()->getRowArray();

        if (! $blast) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data tidak ditemukan.');
        }

        return view('admin/blast-email/preview', [
            'blast' => $blast,
            'menus' => $this->getMenus(),
        ]);
    }
}
