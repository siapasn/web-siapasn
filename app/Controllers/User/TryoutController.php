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
            // Ambil semua produk yang dimiliki user (aktif)
            $produkList = $db->table('user_produk up')
                ->select('p.id, p.nama, p.thumbnail, p.deskripsi, up.expired_at')
                ->join('produk p', 'p.id = up.produk_id')
                ->where('up.user_id', $userId)
                ->groupStart()
                    ->where('up.expired_at IS NULL', null, false)
                    ->orWhere('up.expired_at >', date('Y-m-d H:i:s'))
                ->groupEnd()
                ->groupBy('p.id')
                ->orderBy('p.nama', 'ASC')
                ->get()->getResultArray();

            foreach ($produkList as $produk) {
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
                }
                unset($t);

                // Tandai status sesi setiap tryout
                foreach ($tryouts as &$t) {
                    $t['sudah_selesai'] = $this->sesiModel->sudahSelesai($userId, (int) $t['id']);
                    $sesiAktif          = $this->sesiModel->getAktif($userId, (int) $t['id']);
                    $t['sesi_aktif_id'] = $sesiAktif ? $sesiAktif['id'] : null;
                }
                unset($t);

                $selesai = count(array_filter($tryouts, fn($t) => $t['sudah_selesai']));

                $paketList[] = [
                    'produk'          => $produk,
                    'tryouts'         => $tryouts,
                    'jumlah_tryout'   => count($tryouts),
                    'jumlah_selesai'  => $selesai,
                    'total_durasi'    => array_sum(array_column($tryouts, 'durasi')),
                    'total_soal'      => array_sum(array_column($tryouts, 'jumlah_soal')),
                ];            }
        }

        return view('user/tryout/index', [
            'paketList' => $paketList,
            'menus'     => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // mulai(int $tryoutId) — GET: halaman konfirmasi sebelum mulai
    // -------------------------------------------------------------------------

    public function mulai(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();

        // Cek akses user ke tryout ini
        if (!$this->userHasAccessToTryout($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda tidak memiliki akses ke tryout ini.');
        }

        // Cek apakah sesi sudah selesai
        if ($this->sesiModel->sudahSelesai($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda sudah menyelesaikan tryout ini dan tidak dapat mengulanginya.');
        }

        // Ambil data tryout
        $tryout = $db->table('tryout')->where('id', $tryoutId)->get()->getRowArray();
        if (!$tryout) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Tryout tidak ditemukan.');
        }

        // Hitung jumlah soal dari mapping_soal
        $tryout['jumlah_soal'] = $db->table('mapping_soal')
            ->where('tryout_id', $tryoutId)
            ->countAllResults();

        // Cek apakah ada sesi yang sedang berlangsung
        $sesiAktif = $this->sesiModel->getAktif($userId, $tryoutId);

        return view('user/tryout/mulai', [
            'tryout'    => $tryout,
            'sesiAktif' => $sesiAktif,
            'menus'     => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // start(int $tryoutId) — POST: mulai sesi tryout
    // -------------------------------------------------------------------------

    public function start(int $tryoutId)
    {
        $userId = (int) session()->get('user_id');

        // Re-check akses
        if (!$this->userHasAccessToTryout($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda tidak memiliki akses ke tryout ini.');
        }

        // Re-check sesi duplikat
        if ($this->sesiModel->sudahSelesai($userId, $tryoutId)) {
            return redirect()->to(base_url('user/tryout'))
                ->with('error', 'Anda sudah menyelesaikan tryout ini dan tidak dapat mengulanginya.');
        }

        // Jika ada sesi berlangsung, lanjutkan sesi tersebut
        $sesiAktif = $this->sesiModel->getAktif($userId, $tryoutId);
        if ($sesiAktif) {
            return redirect()->to(base_url('user/tryout/jawab/' . $sesiAktif['id'] . '?soal_index=1'));
        }

        // Buat sesi baru
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

        // Cek apakah hasil sudah ada; jika belum, hitung sekarang
        $hasil = $this->hasilModel->getBySesi($sesiId);
        if (!$hasil) {
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

        // Ambil semua soal beserta jawaban user dan kunci jawaban
        $soalList = $db->table('mapping_soal ms')
            ->select('s.id as soal_id, s.pertanyaan, s.pilihan_a, s.pilihan_b, s.pilihan_c, s.pilihan_d, s.pilihan_e, s.kunci_jawaban, s.pembahasan, ju.jawaban as jawaban_user, ju.is_benar')
            ->join('soal s', 's.id = ms.soal_id')
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
    // soal() — alias lama untuk backward compatibility
    // -------------------------------------------------------------------------

    public function soal(int $sesiId, int $nomorSoal = 1)
    {
        return redirect()->to(base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . $nomorSoal));
    }
}
