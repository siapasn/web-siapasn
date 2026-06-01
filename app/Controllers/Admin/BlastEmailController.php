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
     * Proses kirim email.
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

        $emailService = new EmailService();
        $totalSent    = 0;
        $totalFailed  = 0;
        $targetEmail  = null;
        $targetUserId = null;

        if ($tipe === 'single') {
            // Kirim ke 1 user
            $targetUserId = (int) $this->request->getPost('target_user_id');
            $user = $db->table('users')->where('id', $targetUserId)->get()->getRowArray();

            if (! $user) {
                return redirect()->back()->withInput()->with('error', 'User tidak ditemukan.');
            }

            $targetEmail = $user['email'];
            $result = $this->sendSingleEmail($emailService, $user['email'], $user['nama'], $subject, $body);

            if ($result) {
                $totalSent = 1;
            } else {
                $totalFailed = 1;
            }
        } elseif ($tipe === 'subscribe') {
            // Kirim ke semua subscriber dari tabel users_subscribe
            $subscribers = $db->table('users_subscribe')
                ->select('email, name')
                ->get()->getResultArray();

            foreach ($subscribers as $sub) {
                $result = $this->sendSingleEmail(
                    $emailService,
                    $sub['email'],
                    $sub['name'] ?: 'Subscriber',
                    $subject,
                    $body
                );
                if ($result) {
                    $totalSent++;
                } else {
                    $totalFailed++;
                }
            }
        } elseif ($tipe === 'subscribe_single') {
            // Kirim ke subscriber tertentu yang dipilih
            $subscriberIds = $this->request->getPost('target_subscriber_ids') ?? [];
            if (empty($subscriberIds)) {
                return redirect()->back()->withInput()->with('error', 'Pilih minimal satu subscriber.');
            }

            $selectedSubs = $db->table('users_subscribe')
                ->whereIn('id', $subscriberIds)
                ->get()->getResultArray();

            foreach ($selectedSubs as $sub) {
                $result = $this->sendSingleEmail(
                    $emailService,
                    $sub['email'],
                    $sub['name'] ?: 'Subscriber',
                    $subject,
                    $body
                );
                if ($result) $totalSent++;
                else $totalFailed++;
            }
        } else {
            // Kirim ke semua user terdaftar
            $allUsers = $db->table('users')
                ->select('id, nama, email')
                ->where('role', 'user')
                ->where('is_active', 1)
                ->where('email_verified_at IS NOT NULL', null, false)
                ->get()->getResultArray();

            foreach ($allUsers as $user) {
                $result = $this->sendSingleEmail($emailService, $user['email'], $user['nama'], $subject, $body);
                if ($result) {
                    $totalSent++;
                } else {
                    $totalFailed++;
                }
            }
        }

        // Simpan log
        $db->table('blast_email')->insert([
            'subject'        => $subject,
            'body'           => $body,
            'tipe'           => $tipe,
            'target_user_id' => $targetUserId,
            'target_email'   => $targetEmail,
            'total_sent'     => $totalSent,
            'total_failed'   => $totalFailed,
            'sent_by'        => session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $msg = "Email berhasil dikirim: {$totalSent} berhasil";
        if ($totalFailed > 0) {
            $msg .= ", {$totalFailed} gagal";
        }

        // Notifikasi ke user yang menerima blast email
        if ($tipe === 'all') {
            $allUserIds = $db->table('users')
                ->select('id')
                ->where('role', 'user')
                ->where('is_active', 1)
                ->where('email_verified_at IS NOT NULL', null, false)
                ->get()->getResultArray();

            $now2 = date('Y-m-d H:i:s');
            $batch = [];
            foreach ($allUserIds as $u) {
                $batch[] = [
                    'user_id'    => $u['id'],
                    'tipe'       => 'info',
                    'judul'      => 'Pesan dari Admin',
                    'pesan'      => $subject,
                    'url'        => null,
                    'is_read'    => 0,
                    'created_at' => $now2,
                ];
            }
            if (! empty($batch)) {
                $db->table('notifikasi')->insertBatch($batch);
            }
        } elseif ($tipe === 'single' && $targetUserId) {
            \App\Models\NotifikasiModel::kirim(
                $targetUserId, 'info', 'Pesan dari Admin', $subject, null
            );
        }

        return redirect()->to(base_url('admin/blast-email'))->with('success', $msg);
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
