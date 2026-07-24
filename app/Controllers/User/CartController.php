<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\UserProdukModel;
use App\Services\CartService;

class CartController extends BaseController
{
    protected CartService    $cartService;
    protected ProdukModel    $produkModel;
    protected UserProdukModel $userProdukModel;

    public function __construct()
    {
        $this->cartService     = new CartService();
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
     * Tampilkan halaman keranjang belanja.
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $items  = $this->cartService->getItems($userId);

        $produkList = [];
        if (! empty($items)) {
            $db  = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');

            foreach ($items as $produkId) {
                $p = $this->produkModel->find($produkId);
                if (! $p || ! $p['is_active']) continue;

                // Otomatis hapus dari keranjang jika user sudah memiliki produk ini
                if ($this->userProdukModel->hasAccess($userId, $produkId)) {
                    $this->cartService->removeItem($userId, $produkId);
                    continue;
                }

                // Promosi aktif
                $promosi = $db->table('promosi')
                    ->where('produk_id', $produkId)
                    ->where('is_active', 1)
                    ->where('mulai_at <=', $now)
                    ->where('berakhir_at >=', $now)
                    ->get()->getResultArray();

                $p['promosi']    = $promosi;
                $p['harga_promo'] = null;
                if (! empty($promosi)) {
                    $diskonTerbesar = 0;
                    foreach ($promosi as $pr) {
                        $d = $pr['jenis_diskon'] === 'persentase'
                            ? $p['harga'] * ($pr['nilai_diskon'] / 100)
                            : min((float)$pr['nilai_diskon'], $p['harga']);
                        if ($d > $diskonTerbesar) $diskonTerbesar = $d;
                    }
                    $p['harga_promo'] = max(0, $p['harga'] - $diskonTerbesar);
                }

                $p['sudah_beli'] = false;
                $produkList[]    = $p;
            }
        }

        // Hitung total
        $total = 0;
        foreach ($produkList as $p) {
            $total += $p['harga_promo'] ?? $p['harga'];
        }

        return view('user/cart/index', [
            'produkList' => $produkList,
            'total'      => $total,
            'menus'      => $this->getMenus(),
        ]);
    }

    /**
     * POST AJAX: Tambah produk ke keranjang.
     */
    public function add()
    {
        $userId   = (int) session()->get('user_id');
        $produkId = (int) $this->request->getPost('produk_id');

        if ($produkId <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Produk tidak valid.']);
        }

        $produk = $this->produkModel->find($produkId);
        if (! $produk || ! $produk['is_active']) {
            return $this->response->setJSON(['status' => false, 'message' => 'Produk tidak ditemukan.']);
        }

        if ($this->userProdukModel->hasAccess($userId, $produkId)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda sudah memiliki produk ini.']);
        }

        $added = $this->cartService->addItem($userId, $produkId);
        $count = $this->cartService->count($userId);

        if (! $added) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Produk sudah ada di keranjang.',
                'count'   => $count,
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => esc($produk['nama']) . ' ditambahkan ke keranjang.',
            'count'   => $count,
        ]);
    }

    /**
     * POST AJAX: Hapus produk dari keranjang.
     */
    public function remove()
    {
        $userId   = (int) session()->get('user_id');
        $produkId = (int) $this->request->getPost('produk_id');

        $this->cartService->removeItem($userId, $produkId);
        $count = $this->cartService->count($userId);

        return $this->response->setJSON(['status' => true, 'count' => $count]);
    }

    /**
     * POST: Checkout — arahkan ke halaman pilih metode pembayaran
     * untuk item pertama yang belum dibeli.
     */
    public function checkout()
    {
        $userId = (int) session()->get('user_id');
        $items  = $this->cartService->getItems($userId);

        if (empty($items)) {
            return redirect()->to(base_url('user/cart'))->with('error', 'Keranjang kosong.');
        }

        // Cari item pertama yang belum dibeli → arahkan ke pilih metode pembayaran
        foreach ($items as $produkId) {
            if (! $this->userProdukModel->hasAccess($userId, $produkId)) {
                return redirect()->to(base_url('user/transaksi/pilih-metode/' . $produkId));
            }
        }

        return redirect()->to(base_url('user/cart'))->with('info', 'Semua produk di keranjang sudah Anda miliki.');
    }
}
