<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Spark command: kirim 1 email dari blast_email queue by ID.
 *
 * Usage: php spark email:send {id}
 *
 * Dirancang untuk dipanggil secara paralel oleh cron processor.
 */
class SendSingleEmail extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:send';
    protected $description = 'Kirim 1 email dari blast_email queue by ID';
    protected $usage       = 'email:send <id>';
    protected $arguments   = [
        'id' => 'ID record blast_email yang akan dikirim',
    ];

    public function run(array $params)
    {
        $id = (int) ($params[0] ?? 0);
        if ($id <= 0) {
            CLI::error('ID tidak valid.');
            return;
        }

        $db  = \Config\Database::connect();
        $job = $db->table('blast_email')->where('id', $id)->get()->getRowArray();

        if (! $job || $job['status'] !== 'sending') {
            // Sudah diproses atau tidak ada
            return;
        }

        $toEmail = $job['target_email'];
        $nama    = $job['target_nama'] ?? 'User';

        if (empty($toEmail) && ! empty($job['target_user_id'])) {
            $user = $db->table('users')->select('email, nama')->where('id', $job['target_user_id'])->get()->getRowArray();
            if ($user) {
                $toEmail = $user['email'];
                $nama    = $user['nama'];
            }
        }

        if (empty($toEmail)) {
            $db->table('blast_email')->where('id', $id)->update([
                'status'       => 'failed',
                'total_failed' => 1,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        $success = $this->sendEmail($toEmail, $nama, $job['subject'], $job['body']);

        $db->table('blast_email')->where('id', $id)->update([
            'status'       => $success ? 'done' : 'failed',
            'total_sent'   => $success ? 1 : 0,
            'total_failed' => $success ? 0 : 1,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($success) {
            CLI::write("✓ Sent to {$toEmail}", 'green');
        } else {
            CLI::write("✗ Failed to {$toEmail}", 'red');
        }
    }

    private function sendEmail(string $toEmail, string $nama, string $subject, string $body): bool
    {
        try {
            $config = $this->getSmtpConfig();

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
            log_message('error', "SendSingleEmail gagal ke {$toEmail}: " . $e->getMessage());
            return false;
        }
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
            'from_name'  => env('email.from_name', 'SiapASN Simulation Center'),
            'timeout'    => env('email.timeout', '10'),
        ];

        try {
            $db   = \Config\Database::connect();
            $rows = $db->table('master_aplikasi')
                ->whereIn('config_key', [
                    'email_host', 'email_port', 'email_username',
                    'email_password', 'email_encryption', 'email_from', 'email_from_name',
                ])
                ->get()->getResultArray();

            if (empty($rows)) return $defaults;

            $cfg = array_column($rows, 'config_value', 'config_key');
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
}
