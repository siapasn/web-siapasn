<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\TransaksiModel;
use App\Models\UserProdukModel;
use App\Services\MidtransService;

/**
 * PaymentStatusController
 *
 * Menerima redirect dari Midtrans setelah user selesai/batal/gagal bayar.
 * URL: /user/payment/status?order_id=TRX-xxx&status_code=200&transaction_status=settlement
 *
 * Konfigurasi di Midtrans Dashboard:
 *   Finish Redirect URL:   https://domain.com/user/payment/status
 *   Unfinish Redirect URL: https://domain.com/user/payment/status
 *   Error Redirect URL:    https://domain.com/user/payment/status
 */
class PaymentStatusController extends BaseController
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
     * Halaman status pembayaran setelah redirect dari Midtrans.
     */
    public function index()
    {
        $userId  = (int) session()->get('user_id');
        $orderId = $this->request->getGet('order_id');

        // Jika tidak ada order_id, redirect ke riwayat transaksi
        if (empty($orderId)) {
            return redirect()->to(base_url('user/transaksi'));
        }

        // Cari transaksi berdasarkan order_id (kode_transaksi atau midtrans_order_id)
        $transaksiModel = new TransaksiModel();
        $transaksi      = $transaksiModel->getByMidtransOrderId($orderId);

        if (! $transaksi) {
            $transaksi = $transaksiModel->getByKode($orderId);
        }

        // Validasi: transaksi harus milik user yang login
        if (! $transaksi || (int) $transaksi['user_id'] !== $userId) {
            return redirect()->to(base_url('user/transaksi'))
                ->with('error', 'Transaksi tidak ditemukan.');
        }

        // Cek status terbaru ke Midtrans API (karena webhook mungkin belum sampai)
        $statusFromMidtrans = null;
        if ($transaksi['status'] === 'pending') {
            try {
                $midtransService = new MidtransService();
                $result          = $midtransService->checkTransactionStatus($orderId);

                $transactionStatus = $result['transaction_status'] ?? '';
                $fraudStatus       = $result['fraud_status'] ?? '';

                if (in_array($transactionStatus, ['settlement', 'capture'])) {
                    $statusFromMidtrans = ($transactionStatus === 'capture' && $fraudStatus === 'challenge')
                        ? 'pending' : 'success';
                } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
                    $statusFromMidtrans = 'failed';
                } elseif ($transactionStatus === 'expire') {
                    $statusFromMidtrans = 'expired';
                } elseif ($transactionStatus === 'pending') {
                    $statusFromMidtrans = 'pending';
                }

                // Update status jika berubah
                if ($statusFromMidtrans && $statusFromMidtrans !== $transaksi['status']) {
                    $transaksiModel->updateStatus($transaksi['id'], $statusFromMidtrans);
                    $transaksi['status'] = $statusFromMidtrans;

                    // Simpan payment info
                    $paymentInfo = $midtransService->detectPaymentInfo($result);
                    $transaksiModel->update($transaksi['id'], [
                        'payment_method'  => $paymentInfo['method']  ?: $transaksi['payment_method'],
                        'payment_channel' => $paymentInfo['channel'] ?: ($transaksi['payment_channel'] ?? null),
                    ]);

                    // Aktifkan akses jika sukses
                    if ($statusFromMidtrans === 'success') {
                        $userProdukModel = new UserProdukModel();
                        $userProdukModel->aktivasiAkses(
                            $transaksi['user_id'],
                            $transaksi['produk_id'],
                            $transaksi['id']
                        );

                        try {
                            $cartService = new \App\Services\CartService();
                            $cartService->removeItem((int) $transaksi['user_id'], (int) $transaksi['produk_id']);
                        } catch (\Exception $e) {
                            // ignore
                        }

                        $produkNama = \Config\Database::connect()->table('produk')->select('nama')->where('id', $transaksi['produk_id'])->get()->getRowArray()['nama'] ?? 'Produk';
                        \App\Models\NotifikasiModel::kirim(
                            (int) $transaksi['user_id'], 'transaksi', 'Pembayaran Berhasil!',
                            'Pembelian ' . $produkNama . ' berhasil. Selamat belajar!', 'user/tryout'
                        );
                    } elseif (in_array($statusFromMidtrans, ['failed', 'expired'])) {
                        $produkNama = \Config\Database::connect()->table('produk')->select('nama')->where('id', $transaksi['produk_id'])->get()->getRowArray()['nama'] ?? 'Produk';
                        $judul = $statusFromMidtrans === 'failed' ? 'Pembayaran Gagal' : 'Pembayaran Kedaluwarsa';
                        $pesan = $statusFromMidtrans === 'failed'
                            ? 'Pembayaran untuk ' . $produkNama . ' gagal.'
                            : 'Pembayaran untuk ' . $produkNama . ' telah melewati batas waktu.';
                        \App\Models\NotifikasiModel::kirim(
                            (int) $transaksi['user_id'], 'transaksi', $judul, $pesan, 'user/transaksi/' . $transaksi['id']
                        );
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'PaymentStatus: Midtrans check error: ' . $e->getMessage());
                // Lanjut tampilkan status dari DB
            }
        }

        // Ambil data produk
        $db     = \Config\Database::connect();
        $produk = $db->table('produk')->where('id', $transaksi['produk_id'])->get()->getRowArray();

        return view('user/payment/status', [
            'transaksi' => $transaksi,
            'produk'    => $produk,
            'menus'     => $this->getMenus(),
        ]);
    }
}
