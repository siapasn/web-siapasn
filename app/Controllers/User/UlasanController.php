<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UlasanModel;

class UlasanController extends BaseController
{
    protected UlasanModel $ulasanModel;

    public function __construct()
    {
        $this->ulasanModel = new UlasanModel();
    }

    /**
     * Simpan ulasan dari user.
     */
    public function store()
    {
        $userId   = (int) session()->get('user_id');
        $produkId = (int) $this->request->getPost('produk_id');
        $rating   = (int) $this->request->getPost('rating');
        $komentar = $this->request->getPost('komentar');

        // Validasi
        if ($produkId <= 0 || $rating < 1 || $rating > 5) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }

        $db = \Config\Database::connect();
        $produk = $db->table('produk')->where('id', $produkId)->get()->getRowArray();
        if (! $produk) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        // Cek apakah sudah pernah review
        if ($this->ulasanModel->hasReviewed($userId, $produkId)) {
            return redirect()->back()->with('error', 'Anda sudah pernah memberikan ulasan untuk produk ini.');
        }

        // Cek apakah sudah pernah mengerjakan tryout di produk ini.
        // Ini juga mengizinkan peserta event gratis yang menyelesaikan tryout terkait produk.
        $hasAttempt = $db->table('sesi_tryout st')
            ->join('mapping_tryout mt', 'mt.tryout_id = st.tryout_id')
            ->where('st.user_id', $userId)
            ->where('mt.produk_id', $produkId)
            ->whereIn('st.status', ['selesai', 'timeout'])
            ->countAllResults() > 0;

        if (! $hasAttempt) {
            return redirect()->back()->with('error', 'Anda harus menyelesaikan minimal 1 tryout sebelum memberikan ulasan.');
        }

        $this->ulasanModel->insert([
            'user_id'   => $userId,
            'produk_id' => $produkId,
            'rating'    => $rating,
            'komentar'  => $komentar ?: null,
        ]);

        return redirect()->back()->with('success', 'Terima kasih! Ulasan Anda berhasil disimpan.');
    }
}
