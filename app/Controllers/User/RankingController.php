<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class RankingController extends BaseController
{
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
     * Halaman utama ranking — daftar tryout yang bisa dilihat rankingnya.
     * Dikelompokkan per kategori.
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil semua kategori level 1
        $kategoris = $db->table('kategori')
            ->where('parent_id IS NULL', null, false)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        // Ambil semua tryout aktif yang sudah punya minimal 1 hasil
        $tryouts = $db->table('tryout t')
            ->select('t.id, t.nama, t.durasi, p.kategori_id,
                      COUNT(DISTINCT ht.user_id) AS total_peserta,
                      MAX(ht.total_nilai) AS skor_tertinggi')
            ->join('mapping_tryout mt', 'mt.tryout_id = t.id')
            ->join('produk p', 'p.id = mt.produk_id')
            ->join('hasil_tryout ht', 'ht.tryout_id = t.id', 'left')
            ->where('t.is_active', 1)
            ->groupBy('t.id')
            ->having('total_peserta >', 0)
            ->orderBy('t.nama', 'ASC')
            ->get()->getResultArray();

        // Group tryout by kategori
        $tryoutByKategori = [];
        foreach ($kategoris as $kat) {
            $items = array_values(array_filter($tryouts, fn($t) => (int)($t['kategori_id'] ?? 0) === (int)$kat['id']));
            if (! empty($items)) {
                $tryoutByKategori[] = [
                    'kat_id'   => $kat['id'],
                    'kat_nama' => $kat['nama'],
                    'tryouts'  => $items,
                ];
            }
        }

        return view('user/ranking/index', [
            'tryoutByKategori' => $tryoutByKategori,
            'menus'            => $this->getMenus(),
        ]);
    }

    /**
     * Leaderboard per tryout — ranking semua peserta.
     */
    public function leaderboard(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        $tryout = $db->table('tryout')->where('id', $tryoutId)->get()->getRowArray();
        if (! $tryout) {
            return redirect()->to(base_url('user/ranking'))
                ->with('error', 'Tryout tidak ditemukan.');
        }

        // Ambil skor terbaik per user (total_nilai tertinggi, fallback ke skor_total)
        // Hanya ambil sesi yang sudah selesai
        $rankings = $db->query("
            SELECT
                u.id AS user_id,
                u.nama,
                MAX(ht.total_nilai) AS best_total_nilai,
                MAX(ht.skor_total) AS best_skor_total,
                MAX(ht.jumlah_benar) AS best_jumlah_benar,
                COUNT(ht.id) AS total_percobaan,
                MAX(ht.status_lulus) AS status_lulus,
                MAX(ht.created_at) AS last_attempt
            FROM hasil_tryout ht
            JOIN users u ON u.id = ht.user_id
            WHERE ht.tryout_id = ?
            GROUP BY u.id, u.nama
            ORDER BY best_total_nilai DESC, best_skor_total DESC, last_attempt ASC
        ", [$tryoutId])->getResultArray();

        // Tentukan posisi user saat ini
        $myRank = null;
        $myData = null;
        foreach ($rankings as $i => $r) {
            if ((int) $r['user_id'] === $userId) {
                $myRank = $i + 1;
                $myData = $r;
                break;
            }
        }

        // Statistik
        $totalPeserta = count($rankings);
        $skorTertinggi = $totalPeserta > 0 ? (int)($rankings[0]['best_total_nilai'] ?? $rankings[0]['best_skor_total'] ?? 0) : 0;
        $allScores = array_map(fn($r) => (int)($r['best_total_nilai'] ?: $r['best_skor_total']), $rankings);
        $skorRataRata = $totalPeserta > 0 ? round(array_sum($allScores) / $totalPeserta) : 0;

        return view('user/ranking/leaderboard', [
            'tryout'        => $tryout,
            'rankings'      => $rankings,
            'myRank'        => $myRank,
            'myData'        => $myData,
            'totalPeserta'  => $totalPeserta,
            'skorTertinggi' => $skorTertinggi,
            'skorRataRata'  => $skorRataRata,
            'userId'        => $userId,
            'menus'         => $this->getMenus(),
        ]);
    }
}
