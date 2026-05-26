<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $db     = \Config\Database::connect();

        // Produk terbaru yang belum pernah dibeli user (max 4)
        $now = date('Y-m-d H:i:s');

        // Ambil produk_id yang sudah dimiliki user
        $produkDimiliki = $db->table('user_produk')
            ->select('produk_id')
            ->where('user_id', $userId)
            ->get()->getResultArray();
        $produkDimilikiIds = array_column($produkDimiliki, 'produk_id');

        // Rekomendasi Tryout — ambil produk yang di-highlight, aktif, punya tryout, belum dibeli
        $builder = $db->table('produk p')
            ->select('p.*')
            ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
            ->where('p.is_active', 1)
            ->where('p.is_highlight', 1)
            ->groupBy('p.id')
            ->having('COUNT(mt.id) >', 0)
            ->orderBy('p.id', 'DESC');

        if (! empty($produkDimilikiIds)) {
            $builder->whereNotIn('p.id', $produkDimilikiIds);
        }

        $produkRekomendasi = $builder->limit(4)->get()->getResultArray();

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
            $p['promosi'] = $promosi;
        }
        unset($p);

        // 5 riwayat tryout terakhir
        $riwayatTryout = [];
        if ($db->tableExists('hasil_tryout')) {
            $riwayatTryout = $db->table('hasil_tryout ht')
                ->select('ht.*, t.nama as tryout_nama, ht.skor_total, ht.created_at')
                ->join('tryout t', 't.id = ht.tryout_id')
                ->where('ht.user_id', $userId)
                ->orderBy('ht.created_at', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
        }

        // Statistik nilai rata-rata
        $avgSkor = 0;
        if (!empty($riwayatTryout)) {
            $totalSkor = array_sum(array_column($riwayatTryout, 'skor_total'));
            $avgSkor   = round($totalSkor / count($riwayatTryout), 2);
        }

        // Menu untuk sidebar
        $menus = $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        // Buku highlight untuk ditampilkan di dashboard
        $katalogBukuModel = new \App\Models\KatalogBukuModel();
        $bukuHighlight    = $katalogBukuModel->getHighlighted();

        return view('user/dashboard', [
            'produkRekomendasi' => $produkRekomendasi,
            'riwayatTryout'     => $riwayatTryout,
            'avgSkor'           => $avgSkor,
            'bukuHighlight'     => $bukuHighlight,
            'menus'             => $menus,
        ]);
    }
}
