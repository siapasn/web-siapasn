<?php

namespace App\Controllers;

use App\Models\WebContentModel;

class HomeController extends BaseController
{
    protected WebContentModel $contentModel;

    public function __construct()
    {
        $this->contentModel = new WebContentModel();
    }

    // -------------------------------------------------------------------------
    // index() — Landing page utama (single page)
    // -------------------------------------------------------------------------

    public function index()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (session()->get('user_id')) {
            $role = session()->get('role');
            if ($role === 'admin' || $role === 'super_admin') {
                return redirect()->to(base_url('admin/dashboard'));
            }
            return redirect()->to(base_url('user/dashboard'));
        }

        $content = $this->contentModel->getMultiple([
            'hero_tagline',
            'hero_deskripsi',
            'stat_pengguna',
            'stat_soal',
            'stat_paket',
        ]);

        // Ambil jumlah produk aktif untuk ditampilkan di homepage
        $db          = \Config\Database::connect();
        $totalProduk = $db->table('produk')->where('is_active', 1)->countAllResults();

        return view('home/index', [
            'content'     => $content,
            'totalProduk' => $totalProduk,
        ]);
    }

    // -------------------------------------------------------------------------
    // syarat() — Halaman Syarat dan Ketentuan
    // -------------------------------------------------------------------------

    public function syarat()
    {
        $data = $this->contentModel->getBySlug('syarat-ketentuan');
        return view('home/page', [
            'judul'  => $data['judul'] ?? 'Syarat dan Ketentuan',
            'konten' => $data['konten'] ?? '',
            'icon'   => 'bi-file-earmark-text',
        ]);
    }

    // -------------------------------------------------------------------------
    // privasi() — Halaman Kebijakan Privasi
    // -------------------------------------------------------------------------

    public function privasi()
    {
        $data = $this->contentModel->getBySlug('kebijakan-privasi');
        return view('home/page', [
            'judul'  => $data['judul'] ?? 'Kebijakan Privasi',
            'konten' => $data['konten'] ?? '',
            'icon'   => 'bi-shield-check',
        ]);
    }

    // -------------------------------------------------------------------------
    // kontak() — Halaman Hubungi Kami
    // -------------------------------------------------------------------------

    public function kontak()
    {
        $content = $this->contentModel->getMultiple([
            'hubungi-kami',
            'kontak_email',
            'kontak_whatsapp',
            'kontak_alamat',
        ]);

        $data = $this->contentModel->getBySlug('hubungi-kami');

        return view('home/kontak', [
            'judul'   => $data['judul'] ?? 'Hubungi Kami',
            'konten'  => $data['konten'] ?? '',
            'email'   => $content['kontak_email']     ?? 'info@siapasn.id',
            'wa'      => $content['kontak_whatsapp']  ?? '',
            'alamat'  => $content['kontak_alamat']    ?? '',
        ]);
    }
}
