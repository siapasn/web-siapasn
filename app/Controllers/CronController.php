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
}
