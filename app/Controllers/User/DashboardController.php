<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $db     = \Config\Database::connect();

        // Paket aktif: user_produk joined with produk, where expired_at IS NULL or > now
        $paketAktif = [];
        if ($db->tableExists('user_produk')) {
            $paketAktif = $db->table('user_produk up')
                ->select('up.*, p.nama as produk_nama, p.deskripsi, p.thumbnail, up.expired_at')
                ->join('produk p', 'p.id = up.produk_id')
                ->where('up.user_id', $userId)
                ->where('(up.expired_at IS NULL OR up.expired_at > NOW())')
                ->get()->getResultArray();

            // Tambah info tryout per paket
            foreach ($paketAktif as &$paket) {
                $tryouts = $db->table('mapping_tryout mt')
                    ->select('t.id, t.durasi')
                    ->join('tryout t', 't.id = mt.tryout_id')
                    ->where('mt.produk_id', $paket['produk_id'])
                    ->where('t.is_active', 1)
                    ->get()->getResultArray();

                $paket['jumlah_tryout'] = count($tryouts);
                $paket['total_durasi']  = array_sum(array_column($tryouts, 'durasi'));

                // Hitung total soal dari mapping_soal per tryout
                $totalSoal = 0;
                foreach ($tryouts as $tr) {
                    $totalSoal += $db->table('mapping_soal')
                        ->where('tryout_id', $tr['id'])
                        ->countAllResults();
                }
                $paket['total_soal'] = $totalSoal;
            }
            unset($paket);
        }

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

        return view('user/dashboard', [
            'paketAktif'    => $paketAktif,
            'riwayatTryout' => $riwayatTryout,
            'avgSkor'       => $avgSkor,
            'menus'         => $menus,
        ]);
    }
}
