<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\TryoutEventModel;
use App\Models\SesiTryoutModel;
use App\Models\HasilTryoutModel;
use App\Services\TryoutScoringService;

class TryoutEventController extends BaseController
{
    protected TryoutEventModel $eventModel;
    protected SesiTryoutModel  $sesiModel;

    public function __construct()
    {
        $this->eventModel = new TryoutEventModel();
        $this->sesiModel  = new SesiTryoutModel();
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

    private function getEventPhase(array $event, string $now): string
    {
        if ($now < $event['mulai_pelaksanaan']) {
            return 'menunggu';
        }

        if ($now <= $event['tutup_pelaksanaan']) {
            return 'pelaksanaan';
        }

        return 'selesai';
    }

    /**
     * Daftar event tryout yang tersedia untuk user.
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();
        $now    = date('Y-m-d H:i:s');

        // Event aktif (belum tutup pelaksanaan)
        $events = $this->eventModel->getActiveForUser();

        // Enrich: cek status partisipasi user per event
        foreach ($events as &$e) {
            $peserta = $db->table('tryout_event_peserta')
                ->where('event_id', $e['id'])
                ->where('user_id', $userId)
                ->get()->getRowArray();

            $e['user_registered'] = ! empty($peserta);
            $e['user_status']     = $peserta['status'] ?? null;
            $e['sesi_tryout_id']  = $peserta['sesi_tryout_id'] ?? null;

            $e['fase'] = $this->getEventPhase($e, $now);

            // Hitung percobaan user
            $e['user_percobaan'] = $db->table('sesi_tryout')
                ->where('user_id', $userId)
                ->where('tryout_id', $e['tryout_id'])
                ->whereIn('status', ['selesai', 'timeout', 'berlangsung'])
                ->countAllResults();
        }
        unset($e);

        // Event yang sudah selesai (untuk riwayat)
        $eventSelesai = $db->table('tryout_event te')
            ->select('te.*, t.nama AS tryout_nama, t.durasi,
                      COUNT(tep.id) AS total_peserta')
            ->join('tryout t', 't.id = te.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.event_id = te.id', 'left')
            ->where('te.is_active', 1)
            ->where('te.tutup_pelaksanaan <', $now)
            ->groupBy('te.id')
            ->orderBy('te.tutup_pelaksanaan', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // Enrich event selesai
        foreach ($eventSelesai as &$es) {
            $peserta = $db->table('tryout_event_peserta')
                ->where('event_id', $es['id'])
                ->where('user_id', $userId)
                ->get()->getRowArray();
            $es['user_registered'] = ! empty($peserta);
            $es['user_status']     = $peserta['status'] ?? null;
            $es['fase']            = 'selesai';
        }
        unset($es);

        return view('user/tryout-event/index', [
            'events'       => $events,
            'eventSelesai' => $eventSelesai,
            'menus'        => $this->getMenus(),
        ]);
    }

    /**
     * Detail event + daftar.
     */
    public function detail(string $slug)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();
        $now    = date('Y-m-d H:i:s');

        // Cari event by slug (backward-compat: juga coba by ID jika slug adalah angka)
        $event = ctype_digit($slug)
            ? $this->eventModel->find((int) $slug)
            : $this->eventModel->findBySlug($slug);

        if (! $event || ! $event['is_active']) {
            return redirect()->to(base_url('user/tryout-event'))
                ->with('error', 'Event tidak ditemukan.');
        }

        // Jika diakses via ID (angka), redirect ke URL slug
        if (ctype_digit($slug) && ! empty($event['slug'])) {
            return redirect()->to(base_url('user/tryout-event/' . $event['slug']), 301);
        }

        $eventId = (int) $event['id'];

        // Info tryout
        $tryout = $db->table('tryout')->where('id', $event['tryout_id'])->get()->getRowArray();
        $tryout['jumlah_soal'] = $db->table('mapping_soal')
            ->where('tryout_id', $event['tryout_id'])
            ->countAllResults();

        // Status peserta
        $peserta = $db->table('tryout_event_peserta')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        // Total peserta
        $totalPeserta = $db->table('tryout_event_peserta')
            ->where('event_id', $eventId)
            ->countAllResults();

        $fase = $this->getEventPhase($event, $now);

        // Hitung percobaan user
        $userPercobaan = $db->table('sesi_tryout')
            ->where('user_id', $userId)
            ->where('tryout_id', $event['tryout_id'])
            ->whereIn('status', ['selesai', 'timeout', 'berlangsung'])
            ->countAllResults();

        // Sesi aktif
        $sesiAktif = $this->sesiModel->getAktif($userId, (int) $event['tryout_id']);

        // Hasil terbaik user
        $hasilUser = null;
        $semuaHasil = [];
        if ($peserta && $peserta['status'] === 'completed') {
            $hasilUser = $db->table('hasil_tryout')
                ->where('user_id', $userId)
                ->where('tryout_id', $event['tryout_id'])
                ->orderBy('total_nilai', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            // Ambil semua hasil percobaan user (untuk riwayat)
            $semuaHasil = $db->table('hasil_tryout')
                ->where('user_id', $userId)
                ->where('tryout_id', $event['tryout_id'])
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return view('user/tryout-event/detail', [
            'event'         => $event,
            'tryout'        => $tryout,
            'peserta'       => $peserta,
            'totalPeserta'  => $totalPeserta,
            'fase'          => $fase,
            'userPercobaan' => $userPercobaan,
            'sesiAktif'     => $sesiAktif,
            'hasilUser'     => $hasilUser,
            'semuaHasil'    => $semuaHasil,
            'ulasanContext' => $this->getUlasanContext($userId, (int) $event['tryout_id']),
            'menus'         => $this->getMenus(),
        ]);
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
     * Backward-compatible route. Event tidak lagi memakai pendaftaran manual.
     */
    public function daftar(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (! $event || ! $event['is_active']) {
            return redirect()->to(base_url('user/tryout-event'))
                ->with('error', 'Event tidak ditemukan.');
        }

        $eventSlug = $event['slug'] ?: $eventId;

        return redirect()->to(base_url("user/tryout-event/{$eventSlug}"))
            ->with('success', 'Event tidak memerlukan pendaftaran. Anda bisa langsung mulai saat periode pelaksanaan berlangsung.');
    }

    /**
     * Mulai tryout event.
     */
    public function mulai(int $eventId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();
        $now    = date('Y-m-d H:i:s');

        $event = $this->eventModel->find($eventId);
        if (! $event || ! $event['is_active']) {
            return redirect()->to(base_url('user/tryout-event'))
                ->with('error', 'Event tidak ditemukan.');
        }

        $eventSlug = $event['slug'] ?: $eventId;

        // Cek periode pelaksanaan
        if ($now < $event['mulai_pelaksanaan'] || $now > $event['tutup_pelaksanaan']) {
            return redirect()->to(base_url("user/tryout-event/{$eventSlug}"))
                ->with('error', 'Periode pelaksanaan belum dimulai atau sudah berakhir.');
        }

        // Buat catatan peserta otomatis saat user pertama kali mulai event.
        $peserta = $db->table('tryout_event_peserta')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (! $peserta) {
            $db->table('tryout_event_peserta')->insert([
                'event_id'      => $eventId,
                'user_id'       => $userId,
                'registered_at' => $now,
                'status'        => 'registered',
            ]);
        }

        // Cek sesi aktif lebih dulu agar percobaan yang belum selesai bisa dilanjutkan.
        $sesiAktif = $this->sesiModel->getAktif($userId, (int) $event['tryout_id']);
        if ($sesiAktif) {
            $db->table('tryout_event_peserta')
                ->where('event_id', $eventId)
                ->where('user_id', $userId)
                ->update([
                    'status'         => 'started',
                    'sesi_tryout_id' => $sesiAktif['id'],
                ]);

            return redirect()->to(base_url('user/tryout/jawab/' . $sesiAktif['id'] . '?soal_index=1'));
        }

        // Cek max percobaan untuk sesi baru.
        $percobaan = $db->table('sesi_tryout')
            ->where('user_id', $userId)
            ->where('tryout_id', $event['tryout_id'])
            ->whereIn('status', ['selesai', 'timeout', 'berlangsung'])
            ->countAllResults();

        if ($percobaan >= (int) $event['max_percobaan']) {
            return redirect()->to(base_url("user/tryout-event/{$eventSlug}"))
                ->with('error', 'Anda sudah mencapai batas maksimal percobaan (' . $event['max_percobaan'] . 'x).');
        }

        // Buat sesi baru
        $sesiId = $this->sesiModel->mulai($userId, (int) $event['tryout_id']);

        // Update status peserta
        $db->table('tryout_event_peserta')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update([
                'status'         => 'started',
                'sesi_tryout_id' => $sesiId,
            ]);

        return redirect()->to(base_url('user/tryout/jawab/' . $sesiId . '?soal_index=1'));
    }

    /**
     * Kalender event tryout.
     */
    public function kalender()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Ambil semua event aktif (termasuk yang akan datang)
        $events = $db->table('tryout_event te')
            ->select('te.*, t.nama AS tryout_nama, COUNT(tep.id) AS total_peserta')
            ->join('tryout t', 't.id = te.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.event_id = te.id', 'left')
            ->where('te.is_active', 1)
            ->groupBy('te.id')
            ->orderBy('te.mulai_pelaksanaan', 'ASC')
            ->get()->getResultArray();

        // Build calendar events untuk FullCalendar
        $calendarEvents = [];
        foreach ($events as $ev) {
            $detailUrl = base_url('user/tryout-event/' . ($ev['slug'] ?: $ev['id']));

            // Event pelaksanaan (hijau)
            $calendarEvents[] = [
                'id'    => 'exec-' . $ev['id'],
                'title' => '🎯 ' . $ev['nama'],
                'start' => date('Y-m-d', strtotime($ev['mulai_pelaksanaan'])),
                'end'   => date('Y-m-d', strtotime($ev['tutup_pelaksanaan'] . ' +1 day')),
                'color' => '#198754',
                'textColor' => '#fff',
                'url'   => $detailUrl,
                'extendedProps' => [
                    'desc' => 'Periode Pelaksanaan',
                ],
            ];
        }

        return view('user/tryout-event/kalender', [
            'events'         => $events,
            'calendarEvents' => $calendarEvents,
            'menus'          => $this->getMenus(),
        ]);
    }

    /**
     * Leaderboard event.
     * Hanya bisa diakses jika user sudah mengerjakan tryout event ini.
     */
    public function leaderboard(string $slug)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        // Cari event by slug (backward-compat: juga coba by ID jika slug adalah angka)
        $event = ctype_digit($slug)
            ? $this->eventModel->find((int) $slug)
            : $this->eventModel->findBySlug($slug);

        if (! $event) {
            return redirect()->to(base_url('user/tryout-event'))
                ->with('error', 'Event tidak ditemukan.');
        }

        // Jika diakses via ID (angka), redirect ke URL slug
        if (ctype_digit($slug) && ! empty($event['slug'])) {
            return redirect()->to(base_url('user/tryout-event/' . $event['slug'] . '/leaderboard'), 301);
        }

        $eventId   = (int) $event['id'];
        $eventSlug = $event['slug'] ?: $eventId;

        // Validasi: user harus sudah pernah mengerjakan tryout ini
        $hasCompleted = $db->table('hasil_tryout')
            ->where('user_id', $userId)
            ->where('tryout_id', $event['tryout_id'])
            ->countAllResults();

        if ($hasCompleted === 0) {
            return redirect()->to(base_url('user/tryout-event/' . $eventSlug))
                ->with('error', 'Anda belum mengerjakan tryout ini. Selesaikan tryout terlebih dahulu untuk melihat leaderboard.');
        }

        // Ranking: skor terbaik per peserta event
        $rankings = $db->query("
            SELECT
                u.id AS user_id,
                u.nama,
                tep.status AS peserta_status,
                best_ht.total_nilai AS best_total_nilai,
                best_ht.skor_total AS best_skor_total,
                best_ht.jumlah_benar AS best_jumlah_benar,
                best_ht.status_lulus AS status_lulus,
                best_ht.detail_passing_grade,
                best_ht.created_at AS last_attempt
            FROM tryout_event_peserta tep
            JOIN users u ON u.id = tep.user_id
            LEFT JOIN (
                SELECT ht2.user_id, ht2.total_nilai, ht2.skor_total, ht2.jumlah_benar,
                       ht2.status_lulus, ht2.detail_passing_grade, ht2.created_at
                FROM hasil_tryout ht2
                WHERE ht2.tryout_id = ?
                AND ht2.id = (
                    SELECT ht3.id FROM hasil_tryout ht3
                    WHERE ht3.user_id = ht2.user_id AND ht3.tryout_id = ht2.tryout_id
                    ORDER BY ht3.total_nilai DESC, ht3.skor_total DESC
                    LIMIT 1
                )
            ) best_ht ON best_ht.user_id = tep.user_id
            WHERE tep.event_id = ?
            ORDER BY best_ht.total_nilai DESC, best_ht.skor_total DESC, best_ht.created_at ASC
        ", [$event['tryout_id'], $eventId])->getResultArray();

        // Filter hanya peserta yang sudah punya hasil
        $rankings = array_values(array_filter($rankings, fn($r) => $r['best_total_nilai'] !== null || $r['best_skor_total'] !== null));

        // Posisi user
        $myRank = null;
        $myData = null;
        foreach ($rankings as $i => $r) {
            if ((int) $r['user_id'] === $userId) {
                $myRank = $i + 1;
                $myData = $r;
                break;
            }
        }

        $totalPeserta = $db->table('tryout_event_peserta')
            ->where('event_id', $eventId)
            ->countAllResults();

        return view('user/tryout-event/leaderboard', [
            'event'        => $event,
            'rankings'     => $rankings,
            'myRank'       => $myRank,
            'myData'       => $myData,
            'totalPeserta' => $totalPeserta,
            'userId'       => $userId,
            'ulasanContext' => $this->getUlasanContext($userId, (int) $event['tryout_id']),
            'menus'        => $this->getMenus(),
        ]);
    }
}
