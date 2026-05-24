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
     */
    public function index()
    {
        $userId  = session()->get('user_id');
        $db      = \Config\Database::connect();
        $now     = date('Y-m-d H:i:s');

        // Ambil produk aktif yang sudah memiliki minimal 1 tryout ter-mapping
        $produkRaw = $db->table('produk p')
            ->select('p.*')
            ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
            ->where('p.is_active', 1)
            ->groupBy('p.id')
            ->having('COUNT(mt.id) >', 0)
            ->orderBy('p.id', 'DESC')
            ->get()->getResultArray();

        $produk = [];
        foreach ($produkRaw as $p) {
            // Hitung jumlah tryout
            $p['jumlah_tryout'] = $db->table('mapping_tryout')
                ->where('produk_id', $p['id'])
                ->countAllResults();

            // Ambil tryout_id pertama untuk link "Lihat Sesi"
            $firstTryout = $db->table('mapping_tryout mt')
                ->select('mt.tryout_id')
                ->join('tryout t', 't.id = mt.tryout_id')
                ->where('mt.produk_id', $p['id'])
                ->where('t.is_active', 1)
                ->orderBy('mt.urutan', 'ASC')
                ->limit(1)
                ->get()->getRowArray();
            $p['first_tryout_id'] = $firstTryout ? $firstTryout['tryout_id'] : 0;

            // Sudah beli — cek dulu sebelum fetch promosi
            $p['sudah_beli'] = $this->userProdukModel->hasAccess($userId, $p['id']);

            // Promosi aktif — hanya tampilkan jika user belum memiliki produk ini
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

            // Harga promo — hanya jika belum beli dan ada promosi
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

            $produk[] = $p;
        }

        // Sort: produk yang belum dibeli tampil duluan, sudah dibeli di belakang
        usort($produk, fn($a, $b) => $a['sudah_beli'] <=> $b['sudah_beli']);

        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/produk/index', [
            'produk' => $produk,
            'menus'  => $menus,
        ]);
    }

    /**
     * Tampilkan detail produk beserta daftar tryout yang termasuk.
     */
    public function show(int $id)
    {
        $userId = session()->get('user_id');
        $db     = \Config\Database::connect();

        $produk = $this->produkModel->find($id);
        if (!$produk || !$produk['is_active']) {
            return redirect()->to(base_url('user/produk'))->with('error', 'Produk tidak ditemukan.');
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
            'produk'    => $produk,
            'tryouts'   => $tryouts,
            'promosi'   => $promosi,
            'sudahBeli' => $sudahBeli,
            'materi'    => $sudahBeli ? $this->materiModel->getByProduk($id) : [],
            'menus'     => $menus,
        ]);
    }
}
