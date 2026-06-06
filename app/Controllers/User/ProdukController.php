<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\ProdukMateriModel;
use App\Models\UserProdukModel;

class ProdukController extends BaseController
{
    protected ProdukModel       $produkModel;
    protected ProdukMateriModel $materiModel;
    protected UserProdukModel   $userProdukModel;

    public function __construct()
    {
        $this->produkModel     = new ProdukModel();
        $this->materiModel     = new ProdukMateriModel();
        $this->userProdukModel = new UserProdukModel();
    }

    /**
     * Tampilkan katalog produk aktif beserta promosi yang sedang berjalan.
     * Produk dikelompokkan per kategori untuk ditampilkan sebagai tab.
     * Semua kategori level 1 selalu ditampilkan, meski belum ada produk.
     */
    public function index()
    {
        $userId  = session()->get('user_id');
        $db      = \Config\Database::connect();
        $now     = date('Y-m-d H:i:s');

        // Ambil semua kategori level 1 (tidak punya parent) sebagai tab, urut by id
        $semuaKategori = $db->table('kategori')
            ->where('parent_id IS NULL', null, false)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        // Ambil semua kategori formasi aktif untuk filter
        $kategoriFormasi = $db->table('kategori_formasi')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        // Ambil semua formasi aktif (hanya yang punya referensi untuk filter user)
        $formasiList = $db->table('formasi f')
            ->select('f.*, kf.nama AS kategori_formasi_nama')
            ->join('kategori_formasi kf', 'kf.id = f.kategori_formasi_id', 'left')
            ->where('f.is_active', 1)
            ->where('f.referensi IS NOT NULL', null, false)
            ->where('f.referensi >', 0)
            ->orderBy('kf.urutan', 'ASC')
            ->orderBy('f.nama', 'ASC')
            ->get()->getResultArray();

        // ID kategori yang memerlukan formasi (SKB, PPPK)
        $kategoriWithFormasi = $db->table('kategori')
            ->select('id')
            ->where('parent_id IS NULL', null, false)
            ->groupStart()
                ->like('nama', 'SKB')
                ->orLike('nama', 'PPPK')
            ->groupEnd()
            ->get()->getResultArray();
        $kategoriWithFormasiIds = array_column($kategoriWithFormasi, 'id');

        // Ambil produk aktif yang sudah memiliki minimal 1 tryout ter-mapping
        $produkRaw = $db->table('produk p')
            ->select('p.*, p.kategori_id AS kat_id')
            ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
            ->where('p.is_active', 1)
            ->groupBy('p.id')
            ->having('COUNT(mt.id) >', 0)
            ->orderBy('p.id', 'DESC')
            ->get()->getResultArray();

        // Enrich setiap produk
        $produkEnriched = [];
        foreach ($produkRaw as $p) {
            $p['jumlah_tryout'] = $db->table('mapping_tryout')
                ->where('produk_id', $p['id'])
                ->countAllResults();

            $firstTryout = $db->table('mapping_tryout mt')
                ->select('mt.tryout_id')
                ->join('tryout t', 't.id = mt.tryout_id')
                ->where('mt.produk_id', $p['id'])
                ->where('t.is_active', 1)
                ->orderBy('mt.urutan', 'ASC')
                ->limit(1)
                ->get()->getRowArray();
            $p['first_tryout_id'] = $firstTryout ? $firstTryout['tryout_id'] : 0;

            $p['sudah_beli'] = $this->userProdukModel->hasAccess($userId, $p['id']);

            // Ambil nama formasi jika ada
            $p['formasi_nama'] = '';
            $p['kategori_formasi_nama'] = '';
            $p['kategori_formasi_id'] = '';
            if (! empty($p['formasi_id'])) {
                foreach ($formasiList as $fl) {
                    if ((int)$fl['id'] === (int)$p['formasi_id']) {
                        $p['formasi_nama'] = $fl['nama'];
                        $p['kategori_formasi_nama'] = $fl['kategori_formasi_nama'] ?? '';
                        $p['kategori_formasi_id'] = $fl['kategori_formasi_id'] ?? '';
                        break;
                    }
                }
            }

            $promosi = [];
            if (! $p['sudah_beli'] && $db->tableExists('promosi')) {
                $promosi = $db->table('promosi')
                    ->where('produk_id', $p['id'])
                    ->where('is_active', 1)
                    ->where('mulai_at <=', $now)
                    ->where('berakhir_at >=', $now)
                    ->get()->getResultArray();
            }
            $p['promosi'] = $promosi;

            $p['harga_promo'] = null;
            if (! $p['sudah_beli'] && ! empty($promosi)) {
                $diskonTerbesar = 0;
                foreach ($promosi as $pr) {
                    $d = $pr['jenis_diskon'] === 'persentase'
                        ? $p['harga'] * ($pr['nilai_diskon'] / 100)
                        : min((float)$pr['nilai_diskon'], $p['harga']);
                    if ($d > $diskonTerbesar) $diskonTerbesar = $d;
                }
                $p['harga_promo'] = max(0, $p['harga'] - $diskonTerbesar);
            }

            $produkEnriched[] = $p;
        }

        // Filter: hanya tampilkan produk yang BELUM dibeli
        $produkEnriched = array_values(array_filter($produkEnriched, fn($p) => ! $p['sudah_beli']));

        // Bangun struktur tab: semua kategori level 1, produk dimasukkan sesuai kategori_id
        // Hitung juga total produk asli (sebelum filter) per kategori untuk empty state
        $totalPerKat = [];
        foreach ($produkRaw as $p) {
            $kid = (int)($p['kat_id'] ?? 0);
            $totalPerKat[$kid] = ($totalPerKat[$kid] ?? 0) + 1;
        }

        $produkByKategori = [];
        foreach ($semuaKategori as $kat) {
            $produkKat = array_values(array_filter(
                $produkEnriched, fn($p) => (int)($p['kat_id'] ?? 0) === (int)$kat['id']
            ));

            $totalAsli = $totalPerKat[(int)$kat['id']] ?? 0;

            $produkByKategori[] = [
                'kat_id'           => $kat['id'],
                'kat_nama'         => $kat['nama'],
                'produk'           => $produkKat,
                'semua_sudah_beli' => $totalAsli > 0 && empty($produkKat),
            ];
        }

        // Produk tanpa kategori → masuk tab "Lainnya" jika ada
        $produkTanpaKat = array_values(array_filter($produkEnriched, fn($p) => empty($p['kat_id'])));
        if (! empty($produkTanpaKat)) {
            $produkByKategori[] = [
                'kat_id'           => 0,
                'kat_nama'         => 'Lainnya',
                'produk'           => $produkTanpaKat,
                'semua_sudah_beli' => false,
            ];
        }

        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/produk/index', [
            'produkByKategori'       => $produkByKategori,
            'kategoriFormasi'        => $kategoriFormasi,
            'formasiList'            => $formasiList,
            'kategoriWithFormasiIds' => $kategoriWithFormasiIds,
            'menus'                  => $menus,
        ]);
    }

    /**
     * Tampilkan detail produk beserta daftar tryout yang termasuk.
     */
    public function show(string $slug)
    {
        $userId = session()->get('user_id');
        $db     = \Config\Database::connect();

        // Cari produk by slug (backward-compat: juga coba by ID jika slug adalah angka)
        $produk = ctype_digit($slug)
            ? $this->produkModel->find((int) $slug)
            : $this->produkModel->findBySlug($slug);

        if (!$produk || !$produk['is_active']) {
            return redirect()->to(base_url('user/produk'))->with('error', 'Produk tidak ditemukan.');
        }

        // Jika diakses via ID (angka), redirect ke URL slug
        if (ctype_digit($slug) && ! empty($produk['slug'])) {
            return redirect()->to(base_url('user/produk/' . $produk['slug']), 301);
        }

        $id = (int) $produk['id'];

        // Ambil info formasi jika ada
        $formasiInfo = null;
        if (! empty($produk['formasi_id'])) {
            $formasiInfo = $db->table('formasi f')
                ->select('f.nama AS formasi_nama, kf.nama AS kategori_formasi_nama, kf.icon AS kategori_formasi_icon')
                ->join('kategori_formasi kf', 'kf.id = f.kategori_formasi_id', 'left')
                ->where('f.id', $produk['formasi_id'])
                ->get()->getRowArray();
        }

        // Daftar tryout dalam produk
        $tryouts = [];
        if ($db->tableExists('mapping_tryout')) {
            $tryouts = $db->table('mapping_tryout mt')
                ->select('mt.urutan, t.id, t.nama, t.durasi, t.is_active')
                ->join('tryout t', 't.id = mt.tryout_id')
                ->where('mt.produk_id', $id)
                ->orderBy('mt.urutan', 'ASC')
                ->get()->getResultArray();

            // Hitung jumlah soal dari mapping_soal per tryout
            foreach ($tryouts as &$tr) {
                $tr['jumlah_soal'] = $db->table('mapping_soal')
                    ->where('tryout_id', $tr['id'])
                    ->countAllResults();
            }
            unset($tr);
        }

        $sudahBeli = $this->userProdukModel->hasAccess($userId, $id);

        // Ambil expired_at jika sudah beli
        $expiredAt = null;
        if ($sudahBeli) {
            $userProduk = $db->table('user_produk')
                ->select('expired_at')
                ->where('user_id', $userId)
                ->where('produk_id', $id)
                ->get()->getRowArray();
            $expiredAt = $userProduk['expired_at'] ?? null;
        }

        // Jika sudah beli, tambahkan status sesi per tryout
        if ($sudahBeli) {
            $sesiModel = new \App\Models\SesiTryoutModel();
            foreach ($tryouts as &$tr) {
                $tr['sudah_selesai'] = $sesiModel->sudahSelesai($userId, (int) $tr['id']);
                $sesiAktif           = $sesiModel->getAktif($userId, (int) $tr['id']);
                $tr['sesi_aktif_id'] = $sesiAktif ? $sesiAktif['id'] : null;
            }
            unset($tr);
        }

        // Promosi aktif — filter yang belum pernah dipakai user ini
        $now     = date('Y-m-d H:i:s');
        $promosi = [];
        if ($db->tableExists('promosi')) {
            $semuaPromosi = $db->table('promosi')
                ->where('produk_id', $id)
                ->where('is_active', 1)
                ->where('mulai_at <=', $now)
                ->where('berakhir_at >=', $now)
                ->get()->getResultArray();

            // Ambil produk_id yang sudah pernah dibeli user (success/pending)
            // untuk menentukan apakah promosi masih relevan
            $sudahTransaksi = $db->table('transaksi')
                ->where('user_id', $userId)
                ->where('produk_id', $id)
                ->whereIn('status', ['success', 'pending'])
                ->countAllResults();

            // Jika user belum pernah transaksi produk ini, tampilkan semua promosi aktif
            // Jika sudah pernah (tapi belum punya akses), tetap tampilkan promosi
            // Promosi hanya disembunyikan jika user sudah punya akses (sudahBeli)
            $promosi = $sudahBeli ? [] : $semuaPromosi;
        }

        // Menu sidebar
        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/produk/show', [
            'produk'      => $produk,
            'formasiInfo' => $formasiInfo,
            'tryouts'     => $tryouts,
            'promosi'     => $promosi,
            'sudahBeli'   => $sudahBeli,
            'expiredAt'   => $expiredAt,
            'materi'      => $sudahBeli ? $this->materiModel->getByProduk($id) : [],
            'ulasans'     => (new \App\Models\UlasanModel())->getByProduk($id),
            'avgRating'   => (new \App\Models\UlasanModel())->getAvgRating($id),
            'hasReviewed' => $sudahBeli ? (new \App\Models\UlasanModel())->hasReviewed($userId, $id) : true,
            'menus'       => $menus,
        ]);
    }
}
