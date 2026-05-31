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
        $now         = date('Y-m-d H:i:s');
        $totalProduk = $db->table('produk')->where('is_active', 1)->countAllResults();

        // Event tryout aktif (max 3)
        $eventAktif = $db->table('tryout_event te')
            ->select('te.*, t.nama AS tryout_nama, t.durasi, COUNT(tep.id) AS total_peserta')
            ->join('tryout t', 't.id = te.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.event_id = te.id', 'left')
            ->where('te.is_active', 1)
            ->where('te.tutup_pelaksanaan >=', $now)
            ->groupBy('te.id')
            ->orderBy('te.mulai_pelaksanaan', 'ASC')
            ->limit(3)
            ->get()->getResultArray();

        foreach ($eventAktif as &$ev) {
            if ($now < $ev['mulai_pendaftaran']) $ev['fase'] = 'belum_buka';
            elseif ($now <= $ev['tutup_pendaftaran']) $ev['fase'] = 'pendaftaran';
            elseif ($now < $ev['mulai_pelaksanaan']) $ev['fase'] = 'menunggu';
            elseif ($now <= $ev['tutup_pelaksanaan']) $ev['fase'] = 'pelaksanaan';
            else $ev['fase'] = 'selesai';
        }
        unset($ev);

        // Produk rekomendasi (highlight, aktif, punya tryout) — max 8
        $produkRekomendasi = $db->table('produk p')
            ->select('p.*')
            ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
            ->where('p.is_active', 1)
            ->where('p.is_highlight', 1)
            ->groupBy('p.id')
            ->having('COUNT(mt.id) >', 0)
            ->orderBy('p.id', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        foreach ($produkRekomendasi as &$p) {
            $p['jumlah_tryout'] = $db->table('mapping_tryout')
                ->where('produk_id', $p['id'])
                ->countAllResults();

            // Promosi aktif
            $promosi = $db->table('promosi')
                ->where('produk_id', $p['id'])
                ->where('is_active', 1)
                ->where('mulai_at <=', $now)
                ->where('berakhir_at >=', $now)
                ->get()->getResultArray();

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
        }
        unset($p);

        // Katalog buku highlight (max 8)
        $katalogBukuModel = new \App\Models\KatalogBukuModel();
        $bukuHighlight    = $katalogBukuModel->getHighlighted();

        return view('home/index', [
            'content'            => $content,
            'totalProduk'        => $totalProduk,
            'eventAktif'         => $eventAktif,
            'produkRekomendasi'  => $produkRekomendasi,
            'bukuHighlight'      => $bukuHighlight,
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
