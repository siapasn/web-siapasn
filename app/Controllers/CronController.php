<?php

namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\UserProdukModel;
use App\Services\MidtransService;

/**
 * CronController
 *
 * Endpoint untuk cron job — tidak memerlukan session/login.
 * Diamankan dengan API key via header X-Cron-Key atau query param ?key=...
 *
 * Contoh cron job (setiap 5 menit):
 *   curl -s -H "X-Cron-Key: {API_KEY}" https://yourdomain.com/cron/sync-payment-status
 *
 * Atau via query param:
 *   curl -s "https://yourdomain.com/cron/sync-payment-status?key={API_KEY}"
 */
class CronController extends BaseController
{
    /**
     * Verifikasi API key dari header atau query param.
     */
    private function authorize(): bool
    {
        $db     = \Config\Database::connect();
        $row    = $db->table('master_aplikasi')
            ->where('config_key', 'cron_api_key')
            ->get()->getRowArray();

        $validKey = $row['config_value'] ?? '';
        if ($validKey === '') return false;

        // Cek dari header X-Cron-Key
        $headerKey = $this->request->getHeaderLine('X-Cron-Key');
        if ($headerKey !== '' && hash_equals($validKey, $headerKey)) return true;

        // Cek dari query param ?key=...
        $queryKey = $this->request->getGet('key') ?? '';
        if ($queryKey !== '' && hash_equals($validKey, $queryKey)) return true;

        return false;
    }

    /**
     * GET /cron/sync-payment-status
     *
     * Cek semua transaksi berstatus 'pending' ke Midtrans Status API,
     * update status di DB, dan aktifkan akses produk jika sukses.
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "processed": 5,
     *   "updated": 2,
     *   "results": [
     *     { "id": 1, "kode": "TRX-...", "old_status": "pending", "new_status": "success" },
     *     ...
     *   ],
     *   "errors": [],
     *   "duration_ms": 1234
     * }
     */
    public function syncPaymentStatus()
    {
        $startTime = microtime(true);

        if (! $this->authorize()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Unauthorized. API key tidak valid.']);
        }

        $db             = \Config\Database::connect();
        $transaksiModel = new TransaksiModel();
        $userProdukModel = new UserProdukModel();

        // Inisialisasi Midtrans
        try {
            $midtransService = new MidtransService();
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'MidtransService error: ' . $e->getMessage()]);
        }

        // Ambil semua transaksi pending yang dibuat lebih dari 1 menit lalu
        // (hindari race condition dengan transaksi yang baru saja dibuat)
        $pendingTransaksi = $db->table('transaksi')
            ->where('status', 'pending')
            ->where('midtrans_order_id IS NOT NULL', null, false)
            ->where('created_at <', date('Y-m-d H:i:s', strtotime('-1 minute')))
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        $processed = count($pendingTransaksi);
        $updated   = 0;
        $results   = [];
        $errors    = [];

        foreach ($pendingTransaksi as $transaksi) {
            try {
                $result = $midtransService->checkTransactionStatus($transaksi['midtrans_order_id']);

                $transactionStatus = $result['transaction_status'] ?? '';
                $fraudStatus       = $result['fraud_status']       ?? '';

                // Tentukan status baru
                $newStatus = null;
                if (in_array($transactionStatus, ['settlement', 'capture'])) {
                    $newStatus = ($transactionStatus === 'capture' && $fraudStatus === 'challenge')
                        ? 'pending' : 'success';
                } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
                    $newStatus = 'failed';
                } elseif ($transactionStatus === 'expire') {
                    $newStatus = 'expired';
                } elseif ($transactionStatus === 'pending') {
                    $newStatus = 'pending'; // tidak berubah
                }

                // Hanya update jika status berubah
                if ($newStatus && $newStatus !== 'pending') {
                    $transaksiModel->updateStatus($transaksi['id'], $newStatus);

                    // Simpan payment info
                    $paymentInfo = $midtransService->detectPaymentInfo($result);
                    $transaksiModel->update($transaksi['id'], [
                        'payment_method'  => $paymentInfo['method']  ?: ($transaksi['payment_method']  ?? null),
                        'payment_channel' => $paymentInfo['channel'] ?: ($transaksi['payment_channel'] ?? null),
                    ]);

                    // Aktifkan akses jika sukses
                    if ($newStatus === 'success') {
                        $userProdukModel->aktivasiAkses(
                            $transaksi['user_id'],
                            $transaksi['produk_id'],
                            $transaksi['id']
                        );

                        $produkNama = $db->table('produk')->select('nama')->where('id', $transaksi['produk_id'])->get()->getRowArray()['nama'] ?? 'Produk';
                        \App\Models\NotifikasiModel::kirim(
                            (int) $transaksi['user_id'], 'transaksi', 'Pembayaran Berhasil!',
                            'Pembelian ' . $produkNama . ' berhasil. Selamat belajar!', 'user/tryout'
                        );
                    } elseif ($newStatus === 'failed') {
                        $produkNama = $db->table('produk')->select('nama')->where('id', $transaksi['produk_id'])->get()->getRowArray()['nama'] ?? 'Produk';
                        \App\Models\NotifikasiModel::kirim(
                            (int) $transaksi['user_id'], 'transaksi', 'Pembayaran Gagal',
                            'Pembayaran untuk ' . $produkNama . ' gagal.', 'user/transaksi/' . $transaksi['id']
                        );
                    } elseif ($newStatus === 'expired') {
                        $produkNama = $db->table('produk')->select('nama')->where('id', $transaksi['produk_id'])->get()->getRowArray()['nama'] ?? 'Produk';
                        \App\Models\NotifikasiModel::kirim(
                            (int) $transaksi['user_id'], 'transaksi', 'Pembayaran Kedaluwarsa',
                            'Pembayaran untuk ' . $produkNama . ' telah melewati batas waktu.', 'user/transaksi/' . $transaksi['id']
                        );
                    }

                    $updated++;
                    $results[] = [
                        'id'         => $transaksi['id'],
                        'kode'       => $transaksi['kode_transaksi'],
                        'old_status' => 'pending',
                        'new_status' => $newStatus,
                    ];
                }

            } catch (\Exception $e) {
                $errors[] = [
                    'id'    => $transaksi['id'],
                    'kode'  => $transaksi['kode_transaksi'],
                    'error' => $e->getMessage(),
                ];
                log_message('error', 'CronController::syncPaymentStatus — transaksi #'
                    . $transaksi['id'] . ': ' . $e->getMessage());
            }
        }

        $durationMs = round((microtime(true) - $startTime) * 1000);

        log_message('info', sprintf(
            'CronController::syncPaymentStatus — processed: %d, updated: %d, errors: %d, duration: %dms',
            $processed, $updated, count($errors), $durationMs
        ));

        return $this->response->setJSON([
            'success'     => true,
            'processed'   => $processed,
            'updated'     => $updated,
            'results'     => $results,
            'errors'      => $errors,
            'duration_ms' => $durationMs,
            'timestamp'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * GET /cron/process-blast-email
     *
     * Proses antrian blast email yang berstatus 'pending'.
     * Spawn multiple background PHP processes secara paralel (mirip goroutine).
     * Setiap process mengirim 1 email secara independen.
     */
    public function processBlastEmail()
    {
        set_time_limit(300);

        $startTime    = microtime(true);
        $batchSize    = 10; // jumlah email yang di-spawn paralel per cron call
        $maxParallel  = 5;  // jumlah proses paralel bersamaan

        if (! $this->authorize()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $db = \Config\Database::connect();

        // Ambil batch email pending
        $jobs = $db->table('blast_email')
            ->where('status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->limit($batchSize)
            ->get()->getResultArray();

        if (empty($jobs)) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => 'Tidak ada antrian email.',
                'processed' => 0,
            ]);
        }

        // Tandai semua sebagai 'sending' agar tidak di-pick cron berikutnya
        $jobIds = array_column($jobs, 'id');
        $db->table('blast_email')
            ->whereIn('id', $jobIds)
            ->update(['status' => 'sending', 'updated_at' => date('Y-m-d H:i:s')]);

        // Deteksi path PHP dan spark
        $phpBin   = $this->detectPhpBinary();
        $sparkPath = ROOTPATH . 'spark';

        // Cek apakah bisa spawn background process
        $canSpawn = function_exists('proc_open') && is_file($sparkPath);

        if ($canSpawn) {
            // === MODE PARALEL: spawn background processes ===
            $processes = [];
            $dispatched = 0;

            foreach ($jobs as $job) {
                // Spawn: php spark email:send {id} (background, non-blocking)
                $cmd = sprintf('%s %s email:send %d', $phpBin, escapeshellarg($sparkPath), (int) $job['id']);

                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows: start /B
                    pclose(popen("start /B {$cmd}", 'r'));
                } else {
                    // Linux/Mac: nohup ... &
                    pclose(popen("nohup {$cmd} > /dev/null 2>&1 &", 'r'));
                }

                $dispatched++;

                // Throttle: jangan spawn terlalu banyak sekaligus
                if ($dispatched % $maxParallel === 0) {
                    usleep(500000); // 0.5 detik delay setiap 5 proses
                }
            }

            $durationMs = round((microtime(true) - $startTime) * 1000);

            log_message('info', sprintf(
                'CronController::processBlastEmail — dispatched %d parallel processes, duration=%dms',
                $dispatched, $durationMs
            ));

            return $this->response->setJSON([
                'success'     => true,
                'mode'        => 'parallel',
                'dispatched'  => $dispatched,
                'duration_ms' => $durationMs,
                'timestamp'   => date('Y-m-d H:i:s'),
            ]);

        } else {
            // === MODE SEQUENTIAL (fallback): kirim langsung satu per satu ===
            $totalSent   = 0;
            $totalFailed = 0;

            foreach ($jobs as $job) {
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
                    $db->table('blast_email')->where('id', $job['id'])->update([
                        'status'       => 'failed',
                        'total_failed' => 1,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                    $totalFailed++;
                    continue;
                }

                $success = $this->sendBlastEmail($toEmail, $nama, $job['subject'], $job['body']);

                $db->table('blast_email')->where('id', $job['id'])->update([
                    'status'       => $success ? 'done' : 'failed',
                    'total_sent'   => $success ? 1 : 0,
                    'total_failed' => $success ? 0 : 1,
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);

                if ($success) $totalSent++;
                else $totalFailed++;
            }

            $durationMs = round((microtime(true) - $startTime) * 1000);

            log_message('info', sprintf(
                'CronController::processBlastEmail [sequential] — sent=%d, failed=%d, duration=%dms',
                $totalSent, $totalFailed, $durationMs
            ));

            return $this->response->setJSON([
                'success'      => true,
                'mode'         => 'sequential',
                'batch_sent'   => $totalSent,
                'batch_failed' => $totalFailed,
                'duration_ms'  => $durationMs,
                'timestamp'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Deteksi path PHP binary.
     */
    private function detectPhpBinary(): string
    {
        if (defined('PHP_BINARY') && PHP_BINARY !== '') {
            return PHP_BINARY;
        }
        // Fallback umum di shared hosting
        $paths = ['/usr/local/bin/php', '/usr/bin/php', 'php'];
        foreach ($paths as $p) {
            if (@is_executable($p)) return $p;
        }
        return 'php';
    }

    /**
     * Kirim satu email blast.
     */
    private function sendBlastEmail(string $toEmail, string $nama, string $subject, string $body): bool
    {
        try {
            $config = $this->getBlastSmtpConfig();
            $emailConfig = new \Config\Email();
            $emailConfig->protocol    = 'smtp';
            $emailConfig->SMTPHost    = $config['host'];
            $emailConfig->SMTPPort    = (int) $config['port'];
            $emailConfig->SMTPUser    = $config['username'];
            $emailConfig->SMTPPass    = $config['password'];
            $emailConfig->SMTPCrypto  = ($config['encryption'] !== 'none') ? $config['encryption'] : '';
            $emailConfig->SMTPTimeout = 5; // 5 detik timeout per email (agar batch cepat)
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
            log_message('error', "Blast email cron gagal ke {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load SMTP config untuk blast email.
     */
    private function getBlastSmtpConfig(): array
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
            $db = \Config\Database::connect();
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
