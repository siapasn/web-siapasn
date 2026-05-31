<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\EmailService;

class RequestFormasiController extends BaseController
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
     * Daftar semua request formasi.
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $requests = $db->table('request_formasi rf')
            ->select('rf.*, u.nama AS user_nama, u.email AS user_email,
                      f.nama AS formasi_nama, kf.nama AS kategori_formasi_nama')
            ->join('users u', 'u.id = rf.user_id')
            ->join('formasi f', 'f.id = rf.formasi_id')
            ->join('kategori_formasi kf', 'kf.id = f.kategori_formasi_id', 'left')
            ->orderBy('rf.status', 'ASC')
            ->orderBy('rf.created_at', 'DESC')
            ->get()->getResultArray();

        // Hitung statistik
        $totalPending  = count(array_filter($requests, fn($r) => $r['status'] === 'pending'));
        $totalApproved = count(array_filter($requests, fn($r) => $r['status'] === 'approved'));
        $totalRejected = count(array_filter($requests, fn($r) => $r['status'] === 'rejected'));

        return view('admin/request-formasi/index', [
            'requests'      => $requests,
            'totalPending'  => $totalPending,
            'totalApproved' => $totalApproved,
            'totalRejected' => $totalRejected,
            'menus'         => $this->getMenus(),
        ]);
    }

    /**
     * Approve request dan kirim notifikasi email ke user.
     */
    public function approve(int $id)
    {
        $db = \Config\Database::connect();

        $request = $db->table('request_formasi rf')
            ->select('rf.*, u.nama AS user_nama, u.email AS user_email, f.nama AS formasi_nama')
            ->join('users u', 'u.id = rf.user_id')
            ->join('formasi f', 'f.id = rf.formasi_id')
            ->where('rf.id', $id)
            ->get()->getRowArray();

        if (! $request) {
            return redirect()->back()->with('error', 'Request tidak ditemukan.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Request sudah diproses sebelumnya.');
        }

        $adminNote = $this->request->getPost('admin_note') ?: null;

        // Update status
        $db->table('request_formasi')->where('id', $id)->update([
            'status'     => 'approved',
            'admin_note' => $adminNote,
            'handled_by' => session()->get('user_id'),
            'handled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Notifikasi ke user: request disetujui
        \App\Models\NotifikasiModel::kirim(
            (int) $request['user_id'],
            'request_formasi',
            'Request Formasi Disetujui!',
            'Tryout untuk formasi ' . $request['formasi_nama'] . ' sudah tersedia.',
            'user/produk'
        );

        // Kirim email notifikasi ke user
        try {
            $emailService = new EmailService();
            $this->sendApprovalEmail($emailService, $request, $adminNote);

            $db->table('request_formasi')->where('id', $id)->update([
                'notified_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RequestFormasi: Gagal kirim email approval: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/request-formasi'))
            ->with('success', "Request dari {$request['user_nama']} berhasil di-approve dan notifikasi email telah dikirim.");
    }

    /**
     * Reject request.
     */
    public function reject(int $id)
    {
        $db = \Config\Database::connect();

        $request = $db->table('request_formasi')->where('id', $id)->get()->getRowArray();
        if (! $request || $request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Request tidak ditemukan atau sudah diproses.');
        }

        $adminNote = $this->request->getPost('admin_note') ?: null;

        $db->table('request_formasi')->where('id', $id)->update([
            'status'     => 'rejected',
            'admin_note' => $adminNote,
            'handled_by' => session()->get('user_id'),
            'handled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Notifikasi ke user: request ditolak
        $request2 = $db->table('request_formasi rf')
            ->select('rf.user_id, f.nama AS formasi_nama')
            ->join('formasi f', 'f.id = rf.formasi_id')
            ->where('rf.id', $id)
            ->get()->getRowArray();

        if ($request2) {
            \App\Models\NotifikasiModel::kirim(
                (int) $request2['user_id'],
                'request_formasi',
                'Request Formasi Ditolak',
                'Request tryout ' . $request2['formasi_nama'] . ' belum dapat dipenuhi.' . ($adminNote ? ' Alasan: ' . $adminNote : ''),
                'user/produk'
            );
        }

        return redirect()->to(base_url('admin/request-formasi'))
            ->with('success', 'Request berhasil ditolak.');
    }

    /**
     * Kirim email approval ke user.
     */
    private function sendApprovalEmail(EmailService $emailService, array $request, ?string $adminNote): bool
    {
        $config = $this->getSmtpConfig();

        $email = \Config\Services::email();
        $emailConfig = new \Config\Email();
        $emailConfig->protocol    = 'smtp';
        $emailConfig->SMTPHost    = $config['host'];
        $emailConfig->SMTPPort    = (int) $config['port'];
        $emailConfig->SMTPUser    = $config['username'];
        $emailConfig->SMTPPass    = $config['password'];
        $emailConfig->SMTPCrypto  = ($config['encryption'] !== 'none') ? $config['encryption'] : '';
        $emailConfig->SMTPTimeout = 10;
        $emailConfig->mailType    = 'html';
        $emailConfig->charset     = 'utf-8';

        $email = \Config\Services::email($emailConfig, false);
        $email->clear();
        $email->setFrom($config['from'], $config['from_name']);
        $email->setTo($request['user_email']);
        $email->setSubject('Tryout Formasi ' . $request['formasi_nama'] . ' Sudah Tersedia! - ' . $config['from_name']);
        $email->setMessage(view('emails/request_formasi_approved', [
            'nama'         => $request['user_nama'],
            'formasi_nama' => $request['formasi_nama'],
            'admin_note'   => $adminNote,
        ]));
        $email->setMailType('html');

        return $email->send(false);
    }

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
        ];

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('master_aplikasi')) return $defaults;

            $rows = $db->table('master_aplikasi')
                ->whereIn('config_key', ['email_host','email_port','email_username','email_password','email_encryption','email_from','email_from_name'])
                ->get()->getResultArray();

            if (empty($rows)) return $defaults;

            $cfg = [];
            foreach ($rows as $row) $cfg[$row['config_key']] = $row['config_value'];

            return [
                'host'       => $cfg['email_host']       ?? $defaults['host'],
                'port'       => $cfg['email_port']       ?? $defaults['port'],
                'username'   => $cfg['email_username']   ?? $defaults['username'],
                'password'   => $cfg['email_password']   ?? $defaults['password'],
                'encryption' => $cfg['email_encryption'] ?? $defaults['encryption'],
                'from'       => $cfg['email_from']       ?? $defaults['from'],
                'from_name'  => $cfg['email_from_name']  ?? $defaults['from_name'],
            ];
        } catch (\Throwable $e) {
            return $defaults;
        }
    }
}
