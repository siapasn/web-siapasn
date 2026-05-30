<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\SesiTryoutModel;
use App\Models\JawabanUserModel;
use App\Models\TryoutModel;
use App\Models\UserProdukModel;
use App\Models\HasilTryoutModel;
use App\Services\TryoutScoringService;

class TryoutController extends BaseController
{
    protected SesiTryoutModel     $sesiModel;
    protected JawabanUserModel    $jawabanModel;
    protected TryoutModel         $tryoutModel;
    protected UserProdukModel     $userProdukModel;
    protected HasilTryoutModel    $hasilModel;
    protected TryoutScoringService $scoringService;

    public function __construct()
    {
        $this->sesiModel      = new SesiTryoutModel();
        $this->jawabanModel   = new JawabanUserModel();
        $this->tryoutModel    = new TryoutModel();
        $this->userProdukModel = new UserProdukModel();
        $this->hasilModel     = new HasilTryoutModel();
        $this->scoringService = new TryoutScoringService();
    }

    // -------------------------------------------------------------------------
    // Helper: ambil menu sidebar
    // -------------------------------------------------------------------------

    private function getMenus(): array
    {
        $db = \Config\Database::connect();

        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    // -------------------------------------------------------------------------
    // Helper: periksa apakah user memiliki akses ke tryout tertentu
    // Akses: user_produk → mapping_tryout → tryout
    // -------------------------------------------------------------------------

    private function userHasAccessToTryout(int $userId, int $tryoutId): bool
    {
        $db = \Config\Database::connect();

        $count = $db->table('user_produk up')
            ->join('mapping_tryout mt', 'mt.produk_id = up.produk_id')
            ->where('up.user_id', $userId)
            ->where('mt.tryout_id', $tryoutId)
            ->groupStart()
                ->where('up.expired_at IS NULL', null, false)
                ->orWhere('up.expired_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    // -------------------------------------------------------------------------
    // index() — daftar paket produk yang dimiliki user, beserta tryout di dalamnya
    // -------------------------------------------------------------------------

    public function index()
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        $paketList = [];

        if ($db->tableExists('mapping_tryout') && $db->tableExists('user_produk')) {
            // Ambil semua produk yang pernah dibeli user (termasuk yang sudah expired)
            $produkList = $db->table('user_produk up')
                ->select('p.id, p.nama, p.thumbnail, p.deskripsi, p.kategori_id, k.nama AS kategori_nama, up.expired_at')
                ->join('produk p', 'p.id = up.produk_id')
                ->join('kategori k', 'k.id = p.kategori_id', 'left')
                ->where('up.user_id', $userId)
                ->groupBy('p.id')
                ->orderBy('k.id', 'ASC')
                ->orderBy('p.nama', 'ASC')
                ->get()->getResultArray();

            foreach ($produkList as $produk) {
                // Tentukan apakah produk sudah expired
                $isExpired = false;
                if (! empty($produk['expired_at']) && strtotime($produk['expired_at']) <= time()) {
                    $isExpired = true;
                }
                $produk['is_expired'] = $isExpired;

                // Ambil semua tryout dalam produk ini
                $tryouts = $db->table('mapping_tryout mt')
                    ->select('t.id, t.nama, t.durasi, t.is_active, mt.urutan')
                    ->join('tryout t', 't.id = mt.tryout_id')
                    ->where('mt.produk_id', $produk['id'])
                    ->where('t.is_active', 1)
                    ->orderBy('mt.urutan', 'ASC')
                    ->get()->getResultArray();

                // Hitung jumlah soal dari mapping_soal per tryout
                foreach ($tryouts as &$t) {
                    $t['jumlah_soal'] = $db->table('mapping_soal')
                        ->where('tryout_id', $t['id'])
                        ->countAllResults();

                    // Status sesi aktif (berlangsung)
                    $sesiAktif          = $this->sesiModel->getAktif($userId, (int) $t['id']);
                    $t['sesi_aktif_id'] = $sesiAktif ? $sesiAktif['id'] : null;
                    $t['sudah_selesai'] = false; // tidak diblokir lagi, bisa berulang

                    // Riwayat sesi selesai untuk tryout ini
                    $t['riwayat'] = $db->table('sesi_tryout st')
                        ->select('st.id as sesi_id, st.mulai_at, st.selesai_at, st.status,
                                  ht.skor_total, ht.jumlah_benar, ht.jumlah_salah, ht.status_lulus')
                        ->join('hasil_tryout ht', 'ht.sesi_tryout_id = st.id', 'left')
                        ->where('st.user_id', $userId)
                        ->where('st.tryout_id', $t['id'])
                        ->whereIn('st.status', ['selesai', 'timeout'])
                        ->orderBy('st.mulai_at', 'DESC')
                        ->get()->getResultArray();
                }
                unset($t);

                $selesai = count(array_filter($tryouts, fn($t) => ! empty($t['riwayat'])));

                $paketList[] = [
                    'produk'          => $produk,
                    'tryouts'         => $tryouts,
                    'jumlah_tryout'   => count($tryouts),
                    'jumlah_selesai'  => $selesai,
                    'total_durasi'    => array_sum(array_column($tryouts, 'durasi')),
                    'total_soal'      => array_sum(array_column($tryouts, 'jumlah_soal')),
                ];            }
        }

        // Ambil semua kategori level 1 untuk tab
        $semuaKategori = $db->table('kategori')
            ->where('parent_id IS NULL', null, false)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        // Group paket by kategori
        $paketByKategori = [];
        foreach ($semuaKategori as $kat) {
            $paketKat = array_values(array_filter($paketList, fn($p) => (int)($p['produk']['kategori_id'] ?? 0) === (int)$kat['id']));
            $paketByKategori[] = [
                'kat_id'   => $kat['id'],
                'kat_nama' => $kat['nama'],
                'paket'    => $paketKat,
            ];
        }
        // Paket tanpa kategori
        $paketTanpaKat = array_values(array_filter($paketList, fn($p) => empty($p['produk']['kategori_id'])));
        if (! empty($paketTanpaKat)) {
            $paketByKategori[] = ['kat_id' => 0, 'kat_nama' => 'Lainnya', 'paket' => $paketTanpaKat];
        }

        return view('user/tryout/index', [
            'paketList'       => $paketList,
            'paketByKategori' => $paketByKategori,
            'menus'           => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // mulai(int $tryoutId) — GET: halaman konfirmasi sebelum mulai
    // -------------------------------------------------------------------------

    public function mulai(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        if (!$this->userHasAccessToTryout($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda tidak memiliki akses ke tryout ini.');
        }

        $tryout = $db->table('tryout')->where('id', $tryoutId)->get()->getRowArray();
        if (!$tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Tryout tidak ditemukan.');
        }

        $tryout['jumlah_soal'] = $db->table('mapping_soal')
            ->where('tryout_id', $tryoutId)
            ->countAllResults();

        $sesiAktif = $this->sesiModel->getAktif($userId, $tryoutId);

        return view('user/tryout/mulai', [
            'tryout'    => $tryout,
            'sesiAktif' => $sesiAktif,
            'menus'     => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // start(int $tryoutId) — POST: mulai sesi tryout baru (bisa berulang)
    // -------------------------------------------------------------------------

    public function start(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');

        if (!$this->userHasAccessToTryout($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda tidak memiliki akses ke tryout ini.');
        }

        // Jika ada sesi berlangsung, lanjutkan sesi tersebut
        $sesiAktif = $this->sesiModel->getAktif($userId, $tryoutId);
        if ($sesiAktif) {
            return redirect()->to(base_url('user/tryout/jawab/' . $sesiAktif['id'] . '?soal_index=1'));
        }

        // Buat sesi baru (boleh berulang)
        $sesiId = $this->sesiModel->mulai($userId, $tryoutId);

        return redirect()->to(base_url('user/tryout/jawab/' . $sesiId . '?soal_index=1'));
    }

    // -------------------------------------------------------------------------
    // jawab(int $sesiId) — GET: tampilkan soal
    // -------------------------------------------------------------------------

    public function jawab(int $sesiId)
    {
        $userId     = (int) session()->get('user_id');
        $soalIndex  = max(1, (int) ($this->request->getGet('soal_index') ?? 1));
        $db         = \Config\Database::connect();

        // Verifikasi sesi milik user dan masih berlangsung
        $sesi = $this->sesiModel->find($sesiId);
        if (!$sesi || (int) $sesi['user_id'] !== $userId || $sesi['status'] !== 'berlangsung') {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Sesi tryout tidak valid atau sudah berakhir.');
        }

        // Ambil data tryout
        $tryout = $db->table('tryout')->where('id', $sesi['tryout_id'])->get()->getRowArray();
        if (!$tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Data tryout tidak ditemukan.');
        }

        // Hitung sisa waktu
        $mulaiTimestamp = strtotime($sesi['mulai_at']);
        $durasiDetik    = (int) $tryout['durasi'] * 60;
        $selesaiAt      = $mulaiTimestamp + $durasiDetik;
        $sisaDetik      = $selesaiAt - time();

        // Jika waktu habis, selesaikan sesi otomatis
        if ($sisaDetik <= 0) {
            $this->sesiModel->selesaikan($sesiId, 'timeout');

            return redirect()->to(base_url('user/tryout/hasil/' . $sesiId))
                ->with('info', 'Waktu tryout telah habis. Sesi diselesaikan secara otomatis.');
        }

        // Ambil daftar soal dalam tryout (urut berdasarkan mapping)
        $soalList = $this->tryoutModel->getSoal((int) $sesi['tryout_id']);

        if (empty($soalList)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Tryout ini belum memiliki soal.');
        }

        $totalSoal = count($soalList);

        // Validasi nomor soal
        if ($soalIndex < 1) {
            $soalIndex = 1;
        }
        if ($soalIndex > $totalSoal) {
            $soalIndex = $totalSoal;
        }

        // Soal saat ini (index 0-based)
        $soalSaatIni = $soalList[$soalIndex - 1];

        // Ambil jawaban user untuk soal ini
        $jawabanUser = $this->jawabanModel
            ->where('sesi_tryout_id', $sesiId)
            ->where('soal_id', $soalSaatIni['soal_id'])
            ->first();

        // Ambil semua jawaban user untuk navigasi (soal mana yang sudah dijawab)
        $semuaJawaban = $this->jawabanModel->getJawabanSesi($sesiId);
        $soalDijawab  = array_column($semuaJawaban, 'jawaban', 'soal_id');

        return view('user/tryout/jawab', [
            'sesi'        => $sesi,
            'tryout'      => $tryout,
            'soalList'    => $soalList,
            'soalSaatIni' => $soalSaatIni,
            'soalIndex'   => $soalIndex,
            'totalSoal'   => $totalSoal,
            'jawabanUser' => $jawabanUser,
            'soalDijawab' => $soalDijawab,
            'sisaDetik'   => $sisaDetik,
            'selesaiAt'   => $selesaiAt,
            'menus'       => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // simpanJawaban() — POST AJAX: auto-save jawaban
    // -------------------------------------------------------------------------

    public function simpanJawaban()
    {
        $sesiId  = (int) $this->request->getPost('sesi_id');
        $soalId  = (int) $this->request->getPost('soal_id');
        $jawaban = $this->request->getPost('jawaban'); // bisa null/kosong

        // Validasi input dasar
        if (!$sesiId || !$soalId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak lengkap.'])
                ->setStatusCode(400);
        }

        $userId = (int) session()->get('user_id');

        // Verifikasi sesi milik user dan masih berlangsung
        $sesi = $this->sesiModel->find($sesiId);
        if (!$sesi || (int) $sesi['user_id'] !== $userId || $sesi['status'] !== 'berlangsung') {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi tidak valid.'])
                ->setStatusCode(403);
        }

        // Normalisasi jawaban: string kosong → null
        if ($jawaban === '' || $jawaban === null) {
            $jawaban = null;
        }

        $this->jawabanModel->simpanJawaban($sesiId, $soalId, $jawaban);

        return $this->response->setJSON(['status' => true]);
    }

    // -------------------------------------------------------------------------
    // selesai(int $sesiId) — POST: selesaikan tryout
    // -------------------------------------------------------------------------

    public function selesai(int $sesiId)
    {
        $userId = (int) session()->get('user_id');

        // Verifikasi sesi milik user
        $sesi = $this->sesiModel->find($sesiId);
        if (!$sesi || (int) $sesi['user_id'] !== $userId) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Sesi tidak valid.');
        }

        // Hanya selesaikan jika masih berlangsung
        if ($sesi['status'] === 'berlangsung') {
            $this->sesiModel->selesaikan($sesiId, 'selesai');
        }

        return redirect()->to(base_url('user/tryout/hasil/' . $sesiId));
    }

    // -------------------------------------------------------------------------
    // hasil(int $sesiId) — GET: tampilkan hasil tryout
    // -------------------------------------------------------------------------

    public function hasil(int $sesiId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        // Verifikasi sesi milik user
        $sesi = $this->sesiModel->find($sesiId);
        if (!$sesi || (int) $sesi['user_id'] !== $userId) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Sesi tidak ditemukan.');
        }

        // Ambil data tryout
        $tryout = $db->table('tryout')->where('id', $sesi['tryout_id'])->get()->getRowArray();
        if (!$tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Data tryout tidak ditemukan.');
        }

        // Cek apakah hasil sudah ada; hitung ulang jika belum ada atau masih 0
        // (bisa terjadi jika scoring sebelumnya gagal / race condition)
        $hasil = $this->hasilModel->getBySesi($sesiId);
        $perluHitung = ! $hasil
            || ((int)($hasil['jumlah_benar'] ?? 0) === 0
                && (int)($hasil['jumlah_salah'] ?? 0) === 0
                && (int)($hasil['jumlah_kosong'] ?? 0) === 0
                && (float)($hasil['total_nilai'] ?? 0) == 0);

        if ($perluHitung) {
            try {
                $hasil = $this->scoringService->hitung($sesiId);
            } catch (\RuntimeException $e) {
                return redirect()->to(base_url('user/tryout'))
                    ->with('error', 'Gagal menghitung hasil: ' . $e->getMessage());
            }
        }

        // Decode detail_kategori dari JSON
        $detailKategori = [];
        if (!empty($hasil['detail_kategori'])) {
            $decoded = json_decode($hasil['detail_kategori'], true);
            if (is_array($decoded)) {
                $detailKategori = $decoded;
            }
        }

        return view('user/tryout/hasil', [
            'sesi'           => $sesi,
            'tryout'         => $tryout,
            'hasil'          => $hasil,
            'detailKategori' => $detailKategori,
            'menus'          => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // pembahasan(int $sesiId) — GET: tampilkan pembahasan soal
    // -------------------------------------------------------------------------

    public function pembahasan(int $sesiId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        // Verifikasi sesi milik user dan sudah selesai
        $sesi = $this->sesiModel->find($sesiId);
        if (!$sesi || (int) $sesi['user_id'] !== $userId) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Sesi tidak ditemukan.');
        }

        if (!in_array($sesi['status'], ['selesai', 'timeout'], true)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Pembahasan hanya tersedia setelah tryout selesai.');
        }

        // Ambil data tryout
        $tryout = $db->table('tryout')->where('id', $sesi['tryout_id'])->get()->getRowArray();
        if (!$tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Data tryout tidak ditemukan.');
        }

        // Pastikan scoring sudah dijalankan agar is_benar di jawaban_user terisi benar.
        // Jika hasil belum ada atau semua 0, hitung sekarang.
        $hasil = $this->hasilModel->getBySesi($sesiId);
        $perluHitung = ! $hasil
            || ((int)($hasil['jumlah_benar'] ?? 0) === 0
                && (int)($hasil['jumlah_salah'] ?? 0) === 0
                && (int)($hasil['jumlah_kosong'] ?? 0) === 0
                && (float)($hasil['total_nilai'] ?? 0) == 0);

        if ($perluHitung) {
            try {
                $this->scoringService->hitung($sesiId);
            } catch (\RuntimeException $e) {
                // Scoring gagal — lanjutkan tampil pembahasan, is_benar mungkin NULL
            }
        }

        // Ambil semua soal beserta jawaban user, kunci jawaban, nilai, dan tipe soal
        $soalList = $db->table('mapping_soal ms')
            ->select('s.id as soal_id, s.pertanyaan,
                      s.pilihan_a, s.pilihan_b, s.pilihan_c, s.pilihan_d, s.pilihan_e,
                      s.kunci_jawaban, s.pembahasan,
                      s.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e,
                      s.kategori_id,
                      k.tipe_soal as tipe_soal,
                      ju.jawaban as jawaban_user, ju.is_benar')
            ->join('soal s', 's.id = ms.soal_id')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->join('jawaban_user ju', 'ju.soal_id = ms.soal_id AND ju.sesi_tryout_id = ' . (int) $sesiId, 'left')
            ->where('ms.tryout_id', $sesi['tryout_id'])
            ->orderBy('ms.urutan', 'ASC')
            ->get()->getResultArray();

        return view('user/tryout/pembahasan', [
            'sesi'     => $sesi,
            'tryout'   => $tryout,
            'soalList' => $soalList,
            'menus'    => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // detailSesi(int $tryoutId) — GET: halaman detail sesi + riwayat + chart
    // -------------------------------------------------------------------------

    public function detailSesi(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        if (! $this->userHasAccessToTryout($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda tidak memiliki akses ke tryout ini.');
        }

        $tryout = $db->table('tryout')->where('id', $tryoutId)->get()->getRowArray();
        if (! $tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Tryout tidak ditemukan.');
        }

        // Ambil semua riwayat sesi selesai user untuk tryout ini
        $riwayat = $db->table('sesi_tryout st')
            ->select('st.id as sesi_id, st.mulai_at, st.selesai_at, st.status,
                      ht.id as hasil_id, ht.skor_total, ht.total_nilai, ht.max_nilai,
                      ht.jumlah_benar, ht.jumlah_salah, ht.jumlah_kosong,
                      ht.status_lulus, ht.peringkat, ht.detail_kategori')
            ->join('hasil_tryout ht', 'ht.sesi_tryout_id = st.id', 'left')
            ->where('st.user_id', $userId)
            ->where('st.tryout_id', $tryoutId)
            ->whereIn('st.status', ['selesai', 'timeout'])
            ->orderBy('st.mulai_at', 'ASC')
            ->get()->getResultArray();

        // Cek sesi aktif
        $sesiAktif = $this->sesiModel->getAktif($userId, $tryoutId);

        // Statistik ringkasan — gunakan total_nilai (poin) bukan persentase
        $totalSesi     = count($riwayat);
        $allTotalNilai = array_filter(array_column($riwayat, 'total_nilai'), fn($v) => $v > 0);

        // Fallback ke skor_total jika total_nilai belum ada (data lama)
        if (empty($allTotalNilai)) {
            $allTotalNilai = array_column($riwayat, 'skor_total');
        }

        $skorTerbaik  = $totalSesi > 0 && ! empty($allTotalNilai) ? max($allTotalNilai) : 0;
        $skorRataRata = $totalSesi > 0 && ! empty($allTotalNilai)
            ? round(array_sum($allTotalNilai) / count($allTotalNilai), 0)
            : 0;
        $jumlahLulus  = count(array_filter($riwayat, fn($r) => $r['status_lulus'] === 'lulus'));

        // Chart data — gunakan total_nilai
        $chartLabels = [];
        $chartData   = [];
        foreach ($riwayat as $i => $r) {
            $chartLabels[] = 'Sesi ' . ($i + 1) . ' (' . date('d/m', strtotime($r['mulai_at'])) . ')';
            $nilaiChart    = (int)($r['total_nilai'] ?? 0) ?: (float)($r['skor_total'] ?? 0);
            $chartData[]   = $nilaiChart;
        }

        return view('user/tryout/detail-sesi', [
            'tryout'       => $tryout,
            'riwayat'      => $riwayat,
            'sesiAktif'    => $sesiAktif,
            'chartLabels'  => $chartLabels,
            'chartData'    => $chartData,
            'totalSesi'    => $totalSesi,
            'skorTerbaik'  => $skorTerbaik,
            'skorRataRata' => $skorRataRata,
            'jumlahLulus'  => $jumlahLulus,
            'menus'        => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // soal() — alias lama untuk backward compatibility
    // -------------------------------------------------------------------------

    public function soal(int $sesiId, int $nomorSoal = 1)
    {
        return redirect()->to(base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . $nomorSoal));
    }
}
