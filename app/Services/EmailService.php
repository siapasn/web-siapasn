<?php

namespace App\Services;

use CodeIgniter\Email\Email;
use Config\Services;

class EmailService
{
    protected Email $email;

    public function __construct()
    {
        $this->email = $this->buildEmailInstance();
    }

    /**
     * Bangun instance Email dengan konfigurasi dari tabel master_aplikasi.
     * Fallback ke konfigurasi .env jika tabel belum ada atau kosong.
     */
    protected function buildEmailInstance(): Email
    {
        $config = $this->loadSmtpConfig();

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
        $emailConfig->wordWrap    = true;

        return Services::email($emailConfig, false);
    }

    /**
     * Muat konfigurasi SMTP dari tabel master_aplikasi.
     * Fallback ke nilai default jika tidak tersedia.
     */
    protected function loadSmtpConfig(): array
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

            // Cek apakah tabel master_aplikasi sudah ada
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
            // Jika DB belum tersedia (misal saat testing), gunakan default
            return $defaults;
        }
    }

    protected function getFromAddress(): string
    {
        return $this->loadSmtpConfig()['from'];
    }

    protected function getFromName(): string
    {
        return $this->loadSmtpConfig()['from_name'];
    }

    public function sendVerification(string $toEmail, string $nama, string $verifyUrl): bool
    {
        $config = $this->loadSmtpConfig();
        $this->email->clear();
        $this->email->setFrom($config['from'], $config['from_name']);
        $this->email->setTo($toEmail);
        $this->email->setSubject('Verifikasi Email - ' . $config['from_name']);
        $this->email->setMessage(view('emails/verification', ['nama' => $nama, 'verifyUrl' => $verifyUrl]));
        $this->email->setMailType('html');
        return $this->email->send(false);
    }

    public function sendPasswordReset(string $toEmail, string $nama, string $resetUrl): bool
    {
        $config = $this->loadSmtpConfig();
        $this->email->clear();
        $this->email->setFrom($config['from'], $config['from_name']);
        $this->email->setTo($toEmail);
        $this->email->setSubject('Reset Password - ' . $config['from_name']);
        $this->email->setMessage(view('emails/password_reset', ['nama' => $nama, 'resetUrl' => $resetUrl]));
        $this->email->setMailType('html');
        return $this->email->send(false);
    }

    public function sendPurchaseConfirmation(string $toEmail, string $nama, array $transaksi): bool
    {
        $config = $this->loadSmtpConfig();
        $this->email->clear();
        $this->email->setFrom($config['from'], $config['from_name']);
        $this->email->setTo($toEmail);
        $this->email->setSubject('Konfirmasi Pembelian - ' . $config['from_name']);
        $this->email->setMessage(view('emails/purchase_confirmation', ['nama' => $nama, 'transaksi' => $transaksi]));
        $this->email->setMailType('html');
        return $this->email->send(false);
    }
}
