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
     * Proses antrian blast email yang berstatus 'pending' atau 'processing'.
     * Kirim batch 50 email per eksekusi. Jika belum selesai, tetap status 'processing'.
     */
    public function processBlastEmail()
    {
        $startTime = microtime(true);
        $batchSize = 50;

        if (! $this->authorize()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $db = \Config\Database::connect();

        // Ambil 1 job yang sedang processing (lanjutkan) atau pending (baru)
        $job = $db->table('blast_email')
            ->whereIn('status', ['processing', 'pending'])
            ->orderBy("FIELD(status, 'processing', 'pending')", '', false)
            ->orderBy('created_at', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        if (! $job) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tidak ada antrian email.',
                'processed' => 0,
            ]);
        }

        // Set status processing jika masih pending
        if ($job['status'] === 'pending') {
            $db->table('blast_email')
                ->where('id', $job['id'])
                ->update(['status' => 'processing', 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $subject = $job['subject'];
        $body    = $job['body'];
        $tipe    = $job['tipe'];
        $offset  = (int) $job['total_sent'] + (int) $job['total_failed']; // sudah diproses sebelumnya

        // Kumpulkan SEMUA penerima
        $allRecipients = [];

        if ($tipe === 'single') {
            $user = $db->table('users')->where('id', $job['target_user_id'])->get()->getRowArray();
            if ($user) {
                $allRecipients[] = ['email' => $user['email'], 'nama' => $user['nama']];
            }
        } elseif ($tipe === 'subscribe') {
            $subs = $db->table('users_subscribe')->select('email, name')->orderBy('id', 'ASC')->get()->getResultArray();
            foreach ($subs as $s) {
                $allRecipients[] = ['email' => $s['email'], 'nama' => $s['name'] ?: 'Subscriber'];
            }
        } elseif ($tipe === 'subscribe_single') {
            $ids = json_decode($job['target_email'] ?? '[]', true);
            if (! empty($ids)) {
                $subs = $db->table('users_subscribe')->whereIn('id', $ids)->orderBy('id', 'ASC')->get()->getResultArray();
                foreach ($subs as $s) {
                    $allRecipients[] = ['email' => $s['email'], 'nama' => $s['name'] ?: 'Subscriber'];
                }
            }
        } else {
            // all
            $users = $db->table('users')
                ->select('email, nama')
                ->where('role', 'user')
                ->where('is_active', 1)
                ->where('email_verified_at IS NOT NULL', null, false)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
            foreach ($users as $u) {
                $allRecipients[] = ['email' => $u['email'], 'nama' => $u['nama']];
            }
        }

        $totalRecipients = count($allRecipients);

        // Ambil batch dari offset
        $batch = array_slice($allRecipients, $offset, $batchSize);

        if (empty($batch)) {
            // Semua sudah terkirim — finalize
            $db->table('blast_email')
                ->where('id', $job['id'])
                ->update(['status' => 'done', 'updated_at' => date('Y-m-d H:i:s')]);

            return $this->response->setJSON([
                'success' => true,
                'job_id'  => $job['id'],
                'message' => 'Job selesai. Semua email sudah terkirim.',
                'total_sent'   => (int) $job['total_sent'],
                'total_failed' => (int) $job['total_failed'],
                'status'       => 'done',
            ]);
        }

        // Kirim batch
        $batchSent   = 0;
        $batchFailed = 0;

        foreach ($batch as $r) {
            $result = $this->sendBlastEmail($r['email'], $r['nama'], $subject, $body);
            if ($result) $batchSent++;
            else $batchFailed++;
        }

        // Update counter
        $newTotalSent   = (int) $job['total_sent'] + $batchSent;
        $newTotalFailed = (int) $job['total_failed'] + $batchFailed;
        $totalProcessed = $newTotalSent + $newTotalFailed;

        // Cek apakah sudah selesai semua
        $isDone = ($totalProcessed >= $totalRecipients);
        $finalStatus = $isDone
            ? ($newTotalSent === 0 ? 'failed' : 'done')
            : 'processing';

        $db->table('blast_email')
            ->where('id', $job['id'])
            ->update([
                'total_sent'   => $newTotalSent,
                'total_failed' => $newTotalFailed,
                'status'       => $finalStatus,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        $durationMs = round((microtime(true) - $startTime) * 1000);

        log_message('info', sprintf(
            'CronController::processBlastEmail — job #%d batch: sent=%d, failed=%d, progress=%d/%d, status=%s, duration=%dms',
            $job['id'], $batchSent, $batchFailed, $totalProcessed, $totalRecipients, $finalStatus, $durationMs
        ));

        return $this->response->setJSON([
            'success'         => true,
            'job_id'          => $job['id'],
            'batch_sent'      => $batchSent,
            'batch_failed'    => $batchFailed,
            'total_sent'      => $newTotalSent,
            'total_failed'    => $newTotalFailed,
            'total_recipients'=> $totalRecipients,
            'progress'        => $totalProcessed . '/' . $totalRecipients,
            'status'          => $finalStatus,
            'duration_ms'     => $durationMs,
        ]);
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
