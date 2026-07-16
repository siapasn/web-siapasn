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
     * Hanya tampilkan tryout yang user punya akses (beli produk atau daftar event).
     * Dikelompokkan per kategori.
     */
    public function index()
    {
        $db     = \Config\Database::connect();
        $userId = (int) session()->get('user_id');
        $activeSesi = $this->getActiveSesi($userId);

        // Ambil semua kategori level 1
        $kategoris = $db->table('kategori')
            ->where('parent_id IS NULL', null, false)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        // Ambil produk_id yang user miliki aksesnya (sudah dibeli & belum expired)
        $ownedProdukIds = $db->table('user_produk')
            ->select('produk_id')
            ->where('user_id', $userId)
            ->groupStart()
                ->where('expired_at IS NULL', null, false)
                ->orWhere('expired_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->get()->getResultArray();
        $ownedProdukIds = array_column($ownedProdukIds, 'produk_id');

        // Ambil tryout_id yang user ikuti lewat event (terdaftar sebagai peserta)
        $eventTryoutIds = $db->table('tryout_event_peserta tep')
            ->select('te.tryout_id')
            ->join('tryout_event te', 'te.id = tep.event_id')
            ->where('tep.user_id', $userId)
            ->get()->getResultArray();
        $eventTryoutIds = array_column($eventTryoutIds, 'tryout_id');

        // Ambil tryout_id yang terhubung ke produk yang dimiliki user
        $produkTryoutIds = [];
        if (! empty($ownedProdukIds)) {
            $produkTryoutRows = $db->table('mapping_tryout')
                ->select('tryout_id')
                ->whereIn('produk_id', $ownedProdukIds)
                ->get()->getResultArray();
            $produkTryoutIds = array_column($produkTryoutRows, 'tryout_id');
        }

        // Gabungkan: tryout yang bisa diakses user
        $accessibleTryoutIds = array_unique(array_merge($produkTryoutIds, $eventTryoutIds));

        if (empty($accessibleTryoutIds)) {
            return view('user/ranking/index', [
                'tryoutByKategori' => [],
                'activeSesi'       => $activeSesi,
                'menus'            => $this->getMenus(),
            ]);
        }

        // Filter lagi: hanya tryout yang SUDAH PERNAH dikerjakan user (ada di hasil_tryout)
        $completedTryoutIds = $db->table('hasil_tryout')
            ->select('tryout_id')
            ->where('user_id', $userId)
            ->whereIn('tryout_id', $accessibleTryoutIds)
            ->groupBy('tryout_id')
            ->get()->getResultArray();
        $completedTryoutIds = array_column($completedTryoutIds, 'tryout_id');

        if (empty($completedTryoutIds)) {
            return view('user/ranking/index', [
                'tryoutByKategori' => [],
                'activeSesi'       => $activeSesi,
                'menus'            => $this->getMenus(),
            ]);
        }

        // Ambil tryout yang user sudah kerjakan. LEFT JOIN menjaga event gratis
        // yang tidak terhubung ke produk agar tetap muncul di perangkingan.
        $tryouts = $db->table('tryout t')
            ->select('t.id, t.nama, t.slug, t.durasi, COALESCE(MIN(p.kategori_id), 0) AS kategori_id,
                      COUNT(DISTINCT ht.user_id) AS total_peserta,
                      MAX(ht.total_nilai) AS skor_tertinggi')
            ->join('mapping_tryout mt', 'mt.tryout_id = t.id', 'left')
            ->join('produk p', 'p.id = mt.produk_id', 'left')
            ->join('hasil_tryout ht', 'ht.tryout_id = t.id', 'left')
            ->where('t.is_active', 1)
            ->whereIn('t.id', $completedTryoutIds)
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

        $eventItems = array_values(array_filter($tryouts, fn($t) => (int)($t['kategori_id'] ?? 0) === 0));
        if (! empty($eventItems)) {
            $tryoutByKategori[] = [
                'kat_id'   => 0,
                'kat_nama' => 'Event Tryout',
                'tryouts'  => $eventItems,
            ];
        }

        return view('user/ranking/index', [
            'tryoutByKategori' => $tryoutByKategori,
            'activeSesi'       => $activeSesi,
            'menus'            => $this->getMenus(),
        ]);
    }

    private function getActiveSesi(int $userId): ?array
    {
        $db = \Config\Database::connect();

        return $db->table('sesi_tryout st')
            ->select('st.id AS sesi_id, st.mulai_at, t.id AS tryout_id, t.nama AS tryout_nama,
                      te.id AS event_id, te.slug AS event_slug, te.nama AS event_nama')
            ->join('tryout t', 't.id = st.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.sesi_tryout_id = st.id AND tep.user_id = st.user_id', 'left')
            ->join('tryout_event te', 'te.id = tep.event_id', 'left')
            ->where('st.user_id', $userId)
            ->where('st.status', 'berlangsung')
            ->orderBy('st.mulai_at', 'DESC')
            ->get()
            ->getRowArray();
    }

    private function getUlasanContext(int $userId, int $tryoutId): array
    {
        $db = \Config\Database::connect();

        $produk = $db->table('mapping_tryout mt')
            ->select('p.id, p.nama, p.slug')
            ->join('produk p', 'p.id = mt.produk_id')
            ->where('mt.tryout_id', $tryoutId)
            ->where('p.is_active', 1)
            ->orderBy('p.id', 'ASC')
            ->get()
            ->getRowArray();

        if (! $produk) {
            return [
                'produk'       => null,
                'can_review'   => false,
                'has_reviewed' => false,
                'has_attempt'  => false,
            ];
        }

        $hasAttempt = $db->table('sesi_tryout')
            ->where('user_id', $userId)
            ->where('tryout_id', $tryoutId)
            ->whereIn('status', ['selesai', 'timeout'])
            ->countAllResults() > 0;

        $hasReviewed = $db->table('ulasan')
            ->where('user_id', $userId)
            ->where('produk_id', $produk['id'])
            ->countAllResults() > 0;

        return [
            'produk'       => $produk,
            'can_review'   => $hasAttempt && ! $hasReviewed,
            'has_reviewed' => $hasReviewed,
            'has_attempt'  => $hasAttempt,
        ];
    }

    /**
     * Leaderboard per tryout — ranking semua peserta.
     * Hanya bisa diakses jika user telah membeli produk terkait atau terdaftar di event.
     */
    public function leaderboard(string $slug)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        // Cari tryout by slug (backward-compat: juga coba by ID jika slug adalah angka)
        if (ctype_digit($slug)) {
            $tryout = $db->table('tryout')->where('id', (int) $slug)->get()->getRowArray();
        } else {
            $tryout = $db->table('tryout')->where('slug', $slug)->get()->getRowArray();
        }

        if (! $tryout) {
            return redirect()->to(base_url('user/ranking'))
                ->with('error', 'Tryout tidak ditemukan.');
        }

        // Jika diakses via ID (angka), redirect ke URL slug
        if (ctype_digit($slug) && ! empty($tryout['slug'])) {
            return redirect()->to(base_url('user/ranking/' . $tryout['slug']), 301);
        }

        $tryoutId = (int) $tryout['id'];

        // --- Validasi Akses ---
        // Cek 1: apakah user punya produk yang mengandung tryout ini?
        $hasProdukAccess = $db->table('mapping_tryout mt')
            ->join('user_produk up', 'up.produk_id = mt.produk_id')
            ->where('mt.tryout_id', $tryoutId)
            ->where('up.user_id', $userId)
            ->groupStart()
                ->where('up.expired_at IS NULL', null, false)
                ->orWhere('up.expired_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->countAllResults();

        // Cek 2: apakah user terdaftar di event yang menggunakan tryout ini?
        $hasEventAccess = $db->table('tryout_event_peserta tep')
            ->join('tryout_event te', 'te.id = tep.event_id')
            ->where('te.tryout_id', $tryoutId)
            ->where('tep.user_id', $userId)
            ->countAllResults();

        if ($hasProdukAccess === 0 && $hasEventAccess === 0) {
            return redirect()->to(base_url('user/ranking'))
                ->with('error', 'Anda belum memiliki akses ke perangkingan tryout ini. Silakan beli paket atau daftar event terkait.');
        }

        // Cek 3: apakah user sudah pernah mengerjakan tryout ini?
        $hasCompleted = $db->table('hasil_tryout')
            ->where('user_id', $userId)
            ->where('tryout_id', $tryoutId)
            ->countAllResults();

        if ($hasCompleted === 0) {
            return redirect()->to(base_url('user/ranking'))
                ->with('error', 'Anda belum mengerjakan tryout ini. Selesaikan tryout terlebih dahulu untuk melihat perangkingan.');
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
            'ulasanContext' => $this->getUlasanContext($userId, $tryoutId),
            'menus'         => $this->getMenus(),
        ]);
    }
}
