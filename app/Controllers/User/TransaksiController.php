<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\TransaksiModel;
use App\Models\UserProdukModel;
use App\Services\MidtransService;
use App\Services\VoucherService;

class TransaksiController extends BaseController
{
    protected TransaksiModel  $transaksiModel;
    protected ProdukModel     $produkModel;
    protected UserProdukModel $userProdukModel;

    public function __construct()
    {
        $this->transaksiModel  = new TransaksiModel();
        $this->produkModel     = new ProdukModel();
        $this->userProdukModel = new UserProdukModel();
    }

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
     * Tampilkan daftar transaksi user.
     */
    public function index()
    {
        $userId       = session()->get('user_id');
        $db           = \Config\Database::connect();
        $statusFilter = $this->request->getGet('status');

        $builder = $db->table('transaksi t')
            ->select('t.*, p.nama as produk_nama')
            ->join('produk p', 'p.id = t.produk_id')
            ->where('t.user_id', $userId)
            ->orderBy('t.created_at', 'DESC');

        $validStatuses = ['pending', 'success', 'failed', 'expired'];
        if ($statusFilter && in_array($statusFilter, $validStatuses)) {
            $builder->where('t.status', $statusFilter);
        }

        $transaksi = $builder->get()->getResultArray();

        return view('user/transaksi/index', [
            'transaksi'    => $transaksi,
            'statusFilter' => $statusFilter,
            'menus'        => $this->getMenus(),
        ]);
    }

    public function riwayat()
    {
        return $this->index();
    }

    /**
     * Halaman pilih metode pembayaran sebelum checkout.
     * GET user/transaksi/pilih-metode/{produkId}
     */
    public function pilihMetode(int $produkId)
    {
        $userId = session()->get('user_id');

        $produk = $this->produkModel->find($produkId);
        if (! $produk || ! $produk['is_active']) {
            return redirect()->to(base_url('user/produk'))->with('error', 'Produk tidak ditemukan.');
        }

        if ($this->userProdukModel->hasAccess($userId, $produkId)) {
            return redirect()->to(base_url('user/produk/' . ($produk['slug'] ?: $produkId)))
                ->with('error', 'Anda sudah memiliki akses ke produk ini.');
        }

        // Hitung harga promo jika ada
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $promosi = $db->table('promosi')
            ->where('produk_id', $produkId)
            ->where('is_active', 1)
            ->where('mulai_at <=', $now)
            ->where('berakhir_at >=', $now)
            ->get()->getResultArray();

        $hargaPromo = null;
        if (! empty($promosi)) {
            $diskonTerbesar = 0;
            foreach ($promosi as $pr) {
                $d = $pr['jenis_diskon'] === 'persentase'
                    ? $produk['harga'] * ($pr['nilai_diskon'] / 100)
                    : min((float)$pr['nilai_diskon'], $produk['harga']);
                if ($d > $diskonTerbesar) $diskonTerbesar = $d;
            }
            $hargaPromo = max(0, $produk['harga'] - $diskonTerbesar);
        }

        return view('user/transaksi/pilih-metode', [
            'produk'         => $produk,
            'hargaPromo'     => $hargaPromo,
            'paymentMethods' => MidtransService::PAYMENT_METHODS,
            'menus'          => $this->getMenus(),
        ]);
    }

    /**
     * Proses pembelian: buat transaksi + snap token dengan metode yang dipilih.
     * POST user/transaksi/beli/{produkId}
     */
    public function beli(int $produkId)
    {
        $userId        = session()->get('user_id');
        $db            = \Config\Database::connect();
        $paymentMethod = $this->request->getPost('payment_method') ?? '';

        // Validasi metode pembayaran
        if ($paymentMethod !== '' && ! isset(MidtransService::PAYMENT_METHODS[$paymentMethod])) {
            return redirect()->back()->with('error', 'Metode pembayaran tidak valid.');
        }

        $produk = $this->produkModel->find($produkId);
        if (! $produk || ! $produk['is_active']) {
            return redirect()->to(base_url('user/produk'))->with('error', 'Produk tidak ditemukan atau tidak aktif.');
        }

        if ($this->userProdukModel->hasAccess($userId, $produkId)) {
            return redirect()->to(base_url('user/produk/' . ($produk['slug'] ?: $produkId)))
                ->with('error', 'Anda sudah memiliki akses ke produk ini.');
        }

        $hargaAsli = (float) $produk['harga'];
        $diskon    = 0.0;
        $voucherId = null;

        // Hitung diskon dari promosi aktif (otomatis, tidak perlu kode)
        $now = date('Y-m-d H:i:s');
        $promosiAktif = $db->table('promosi')
            ->where('produk_id', $produkId)
            ->where('is_active', 1)
            ->where('mulai_at <=', $now)
            ->where('berakhir_at >=', $now)
            ->get()->getResultArray();

        if (! empty($promosiAktif)) {
            $diskonPromoTerbesar = 0;
            foreach ($promosiAktif as $pr) {
                $d = $pr['jenis_diskon'] === 'persentase'
                    ? $hargaAsli * ($pr['nilai_diskon'] / 100)
                    : min((float)$pr['nilai_diskon'], $hargaAsli);
                if ($d > $diskonPromoTerbesar) $diskonPromoTerbesar = $d;
            }
            $diskon = $diskonPromoTerbesar;
        }

        // Voucher — tambahan diskon di atas promosi
        $kodeVoucher = $this->request->getPost('voucher_code');
        if (! empty($kodeVoucher)) {
            $voucherService = new VoucherService();
            $voucher        = $voucherService->validate($kodeVoucher, $userId);
            if (! $voucher) {
                return redirect()->back()->withInput()
                    ->with('error', 'Kode voucher tidak valid, sudah kedaluwarsa, sudah mencapai batas penggunaan, atau sudah pernah Anda gunakan.');
            }
            $diskonVoucher = $voucherService->hitungDiskon($hargaAsli, $voucher);
            // Ambil diskon terbesar antara promosi dan voucher
            if ($diskonVoucher > $diskon) {
                $diskon    = $diskonVoucher;
                $voucherId = $voucher['id'];
            }
        }

        $hargaBayar = max(0, $hargaAsli - $diskon);
        $user       = $db->table('users')->where('id', $userId)->get()->getRowArray();

        $kodeTransaksi = $this->transaksiModel->generateKode();

        $transaksiData = [
            'user_id'           => $userId,
            'produk_id'         => $produkId,
            'voucher_id'        => $voucherId,
            'kode_transaksi'    => $kodeTransaksi,
            'midtrans_order_id' => $kodeTransaksi,
            'harga_asli'        => $hargaAsli,
            'diskon'            => $diskon,
            'harga_bayar'       => $hargaBayar,
            'status'            => 'pending',
            'payment_method'    => $paymentMethod ?: null,
        ];

        $transaksiId = $this->transaksiModel->insert($transaksiData);
        if (! $transaksiId) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi. Silakan coba lagi.');
        }

        $transaksiData['id'] = $transaksiId;

        if ($voucherId) {
            (new VoucherService())->apply($voucherId);
        }

        // Buat Snap token dengan metode yang dipilih
        try {
            $midtransService = new MidtransService();
            $snapToken       = $midtransService->createSnapToken($transaksiData, $user, $produk, $paymentMethod);
            $this->transaksiModel->update($transaksiId, ['snap_token' => $snapToken]);
        } catch (\RuntimeException $e) {
            log_message('error', 'Midtrans createSnapToken error: ' . $e->getMessage());
            return redirect()->to(base_url('user/transaksi/' . $transaksiId))
                ->with('error', 'Gagal menghubungi payment gateway. Silakan coba lagi dari halaman transaksi.');
        }

        // Notifikasi: menunggu pembayaran
        \App\Models\NotifikasiModel::kirim(
            $userId,
            'transaksi',
            'Menunggu Pembayaran',
            'Segera selesaikan pembayaran untuk ' . $produk['nama'],
            'user/transaksi/' . $transaksiId
        );

        return redirect()->to(base_url('user/transaksi/' . $transaksiId));
    }

    /**
     * GET AJAX: Cek status transaksi langsung ke Midtrans API.
     * Digunakan sebagai fallback saat webhook tidak diterima (localhost/dev).
     * URL: user/transaksi/{id}/cek-status
     */
    public function cekStatus(int $id)
    {
        $userId    = session()->get('user_id');
        $db        = \Config\Database::connect();

        $transaksi = $db->table('transaksi')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (! $transaksi) {
            return $this->response->setJSON(['status' => false, 'message' => 'Transaksi tidak ditemukan.']);
        }

        // Jika sudah final, tidak perlu cek ke Midtrans
        if (in_array($transaksi['status'], ['success', 'failed', 'expired'])) {
            return $this->response->setJSON([
                'status'           => true,
                'transaction_status' => $transaksi['status'],
                'redirect'         => $transaksi['status'] === 'success'
                    ? base_url('user/dashboard')
                    : null,
            ]);
        }

        // Cek ke Midtrans Status API
        try {
            $midtransService = new MidtransService();
            $result          = $midtransService->checkTransactionStatus($transaksi['midtrans_order_id']);
        } catch (\Exception $e) {
            log_message('error', 'cekStatus Midtrans error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal cek status ke Midtrans.']);
        }

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
            $newStatus = 'pending';
        }

        if ($newStatus && $newStatus !== $transaksi['status']) {
            $transaksiModel = new \App\Models\TransaksiModel();
            $transaksiModel->updateStatus($transaksi['id'], $newStatus);

            // Simpan payment info
            $paymentInfo = $midtransService->detectPaymentInfo($result);
            $transaksiModel->update($transaksi['id'], [
                'payment_method'  => $paymentInfo['method']  ?: $transaksi['payment_method'],
                'payment_channel' => $paymentInfo['channel'] ?: $transaksi['payment_channel'],
            ]);

            // Aktifkan akses jika sukses
            if ($newStatus === 'success') {
                $userProdukModel = new \App\Models\UserProdukModel();
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
                    log_message('error', 'cekStatus: gagal hapus item keranjang: ' . $e->getMessage());
                }
            }
        }

        return $this->response->setJSON([
            'status'             => true,
            'transaction_status' => $newStatus ?? $transaksi['status'],
            'redirect'           => ($newStatus === 'success') ? base_url('user/dashboard') : null,
        ]);
    }
    public function show(int $id)
    {
        $userId = session()->get('user_id');
        $db     = \Config\Database::connect();

        $transaksi = $db->table('transaksi t')
            ->select('t.*, p.nama as produk_nama, p.deskripsi as produk_deskripsi, p.thumbnail as produk_thumbnail')
            ->join('produk p', 'p.id = t.produk_id')
            ->where('t.id', $id)
            ->where('t.user_id', $userId)
            ->get()->getRowArray();

        if (! $transaksi) {
            return redirect()->to(base_url('user/transaksi'))->with('error', 'Transaksi tidak ditemukan.');
        }

        $clientKey  = '';
        $isProduction = false;
        try {
            $midtransService = new MidtransService();
            $clientKey       = $midtransService->getClientKey();
            $isProduction    = $midtransService->isProduction();
        } catch (\Exception $e) {
            log_message('error', 'MidtransService init error: ' . $e->getMessage());
        }

        return view('user/transaksi/show', [
            'transaksi'    => $transaksi,
            'clientKey'    => $clientKey,
            'isProduction' => $isProduction,
            'menus'        => $this->getMenus(),
        ]);
    }
}
