<?php

namespace App\Controllers;

use App\Models\TransaksiModel;
use App\Models\UserProdukModel;
use App\Services\EmailService;
use App\Services\MidtransService;
use CodeIgniter\HTTP\ResponseInterface;

class WebhookController extends BaseController
{
    /**
     * Terima notifikasi webhook dari Midtrans.
     *
     * Alur:
     * 1. Ambil raw JSON body
     * 2. Verifikasi signature via MidtransService::verifyWebhookSignature()
     * 3. Jika tidak valid: return HTTP 403
     * 4. Cari transaksi berdasarkan order_id
     * 5. Cek idempotency (jika sudah success, return 200)
     * 6. Update status berdasarkan transaction_status
     * 7. Jika success: buat user_produk, kirim email konfirmasi
     * 8. Return HTTP 200
     */
    public function midtrans()
    {
        $rawBody = $this->request->getBody();
        $payload = json_decode($rawBody, true);

        if (!$payload) {
            return $this->response->setStatusCode(400)->setBody('Bad Request');
        }

        // Verifikasi signature
        try {
            $midtransService = new MidtransService();
        } catch (\Exception $e) {
            log_message('error', 'WebhookController: MidtransService init error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }

        if (!$midtransService->verifyWebhookSignature($payload)) {
            log_message('warning', 'WebhookController: Invalid Midtrans signature for order_id: ' . ($payload['order_id'] ?? 'unknown'));
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $orderId = $payload['order_id'] ?? '';

        // Cari transaksi
        $transaksiModel = new TransaksiModel();
        $transaksi      = $transaksiModel->getByMidtransOrderId($orderId);

        if (!$transaksi) {
            // Coba cari berdasarkan kode_transaksi juga
            $transaksi = $transaksiModel->getByKode($orderId);
        }

        if (!$transaksi) {
            log_message('warning', 'WebhookController: Transaksi tidak ditemukan untuk order_id: ' . $orderId);
            return $this->response->setStatusCode(200)->setBody('OK');
        }

        // Idempotency: jika sudah success, tidak perlu diproses ulang
        if ($transaksi['status'] === 'success') {
            return $this->response->setStatusCode(200)->setBody('OK');
        }

        // Tentukan status baru berdasarkan transaction_status dari Midtrans
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';

        $newStatus = null;

        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            // capture hanya valid jika fraud_status bukan 'challenge'
            if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                $newStatus = 'pending';
            } else {
                $newStatus = 'success';
            }
        } elseif ($transactionStatus === 'deny') {
            $newStatus = 'failed';
        } elseif ($transactionStatus === 'cancel') {
            $newStatus = 'failed';
        } elseif ($transactionStatus === 'expire') {
            $newStatus = 'expired';
        } elseif ($transactionStatus === 'pending') {
            $newStatus = 'pending';
        }

        if ($newStatus === null) {
            log_message('info', 'WebhookController: Unhandled transaction_status: ' . $transactionStatus);
            return $this->response->setStatusCode(200)->setBody('OK');
        }

        // Update status transaksi
        $transaksiModel->updateStatus($transaksi['id'], $newStatus);

        // Simpan payment method & channel dari webhook
        try {
            $midtransService = new MidtransService();
            $paymentInfo     = $midtransService->detectPaymentInfo($payload);
            $transaksiModel->update($transaksi['id'], [
                'payment_method'  => $paymentInfo['method'],
                'payment_channel' => $paymentInfo['channel'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'WebhookController: detectPaymentInfo error: ' . $e->getMessage());
        }

        // Jika berhasil: aktifkan akses user ke produk dan kirim email
        if ($newStatus === 'success') {
            $userProdukModel = new UserProdukModel();
            $userProdukModel->aktivasiAkses(
                $transaksi['user_id'],
                $transaksi['produk_id'],
                $transaksi['id']
            );

            // Hapus produk dari keranjang setelah pembayaran sukses
            try {
                $cartService = new \App\Services\CartService();
                $cartService->removeItem((int) $transaksi['user_id'], (int) $transaksi['produk_id']);
            } catch (\Exception $e) {
                log_message('error', 'WebhookController: gagal hapus item keranjang: ' . $e->getMessage());
            }

            // Kirim email konfirmasi pembelian
            try {
                $db      = \Config\Database::connect();
                $user    = $db->table('users')->where('id', $transaksi['user_id'])->get()->getRowArray();
                $produk  = $db->table('produk')->where('id', $transaksi['produk_id'])->get()->getRowArray();

                if ($user && $produk) {
                    $emailService = new EmailService();
                    $emailService->sendPurchaseConfirmation($user['email'], $user['nama'], [
                        'kode_transaksi' => $transaksi['kode_transaksi'],
                        'nama_produk'    => $produk['nama'],
                        'tanggal'        => date('d M Y H:i', strtotime($transaksi['created_at'])),
                        'harga_asli'     => $transaksi['harga_asli'],
                        'diskon'         => $transaksi['diskon'],
                        'harga_bayar'    => $transaksi['harga_bayar'],
                    ]);

                    // Notifikasi ke user: pembayaran berhasil
                    \App\Models\NotifikasiModel::kirim(
                        (int) $transaksi['user_id'],
                        'transaksi',
                        'Pembayaran Berhasil!',
                        'Pembelian ' . $produk['nama'] . ' berhasil. Selamat belajar!',
                        'user/tryout'
                    );

                    // Notifikasi ke admin: ada transaksi baru
                    \App\Models\NotifikasiModel::kirimKeRole(
                        'admin',
                        'transaksi',
                        'Transaksi Baru',
                        $user['nama'] . ' membeli ' . $produk['nama'],
                        'admin/laporan/transaksi'
                    );
                    \App\Models\NotifikasiModel::kirimKeRole(
                        'super_admin',
                        'transaksi',
                        'Transaksi Baru',
                        $user['nama'] . ' membeli ' . $produk['nama'],
                        'admin/laporan/transaksi'
                    );
                }
            } catch (\Exception $e) {
                log_message('error', 'WebhookController: Gagal kirim email konfirmasi: ' . $e->getMessage());
                // Tidak gagalkan webhook karena email gagal
            }
        }

        // Notifikasi untuk status gagal/expired ke user
        if (in_array($newStatus, ['failed', 'expired'])) {
            $db     = \Config\Database::connect();
            $produk = $db->table('produk')->where('id', $transaksi['produk_id'])->get()->getRowArray();
            $namaProduk = $produk['nama'] ?? 'Produk';

            if ($newStatus === 'failed') {
                \App\Models\NotifikasiModel::kirim(
                    (int) $transaksi['user_id'],
                    'transaksi',
                    'Pembayaran Gagal',
                    'Pembayaran untuk ' . $namaProduk . ' gagal. Silakan coba lagi.',
                    'user/transaksi/' . $transaksi['id']
                );
            } elseif ($newStatus === 'expired') {
                \App\Models\NotifikasiModel::kirim(
                    (int) $transaksi['user_id'],
                    'transaksi',
                    'Pembayaran Kedaluwarsa',
                    'Pembayaran untuk ' . $namaProduk . ' telah melewati batas waktu.',
                    'user/transaksi/' . $transaksi['id']
                );
            }
        }

        return $this->response->setStatusCode(200)->setBody('OK');
    }
}
