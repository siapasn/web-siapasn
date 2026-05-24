<?php

namespace App\Services;

use App\Models\JawabanUserModel;
use App\Models\HasilTryoutModel;
use App\Models\SesiTryoutModel;

/**
 * TryoutScoringService
 *
 * Aturan Scoring:
 * - POINT (TWK, TIU): jawaban benar = 5 poin, salah/kosong = 0 poin
 * - SCORE (TKP)     : nilai diambil dari field nilai_a–nilai_e sesuai pilihan user
 *
 * Passing Grade:
 * - Dibandingkan dengan total_nilai (bukan persentase)
 * - Lulus jika total_nilai >= nilai_minimum untuk SETIAP sub kategori yang dikonfigurasi
 * - Status akhir 'lulus' hanya jika SEMUA sub kategori lulus
 */
class TryoutScoringService
{
    protected JawabanUserModel $jawabanModel;
    protected HasilTryoutModel $hasilModel;
    protected SesiTryoutModel  $sesiModel;

    public function __construct()
    {
        $this->jawabanModel = new JawabanUserModel();
        $this->hasilModel   = new HasilTryoutModel();
        $this->sesiModel    = new SesiTryoutModel();
    }

    public function hitung(int $sesiId): array
    {
        $sesi = $this->sesiModel->find($sesiId);
        if (! $sesi) {
            throw new \RuntimeException("Sesi tryout tidak ditemukan: {$sesiId}");
        }

        $db = \Config\Database::connect();

        // Ambil semua jawaban beserta data soal dan kategori.
        // Gunakan raw query untuk menghindari kolom collision saat JOIN dua kali ke tabel kategori.
        $sql = "
            SELECT
                ju.soal_id,
                ju.jawaban,
                s.kunci_jawaban,
                s.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e,
                s.kategori_id,
                k.nama        AS kategori_nama,
                k.tipe_soal   AS kategori_tipe
            FROM jawaban_user ju
            INNER JOIN soal s  ON s.id  = ju.soal_id
            LEFT  JOIN kategori k  ON k.id  = s.kategori_id
            WHERE ju.sesi_tryout_id = ?
        ";
        $jawaban = $db->query($sql, [$sesiId])->getResultArray();

        // Jika tidak ada jawaban sama sekali, lempar exception agar tidak menyimpan hasil 0
        if (empty($jawaban)) {
            throw new \RuntimeException("Tidak ada jawaban ditemukan untuk sesi: {$sesiId}");
        }

        $jumlahBenar    = 0;
        $jumlahSalah    = 0;
        $jumlahKosong   = 0;
        $detailKategori = [];

        foreach ($jawaban as $j) {
            $tipeSoal = $j['kategori_tipe'] ?? 'POINT';
            $isKosong = ($j['jawaban'] === null || $j['jawaban'] === '');

            // ── Deteksi apakah soal SCORE benar-benar punya nilai per pilihan ──
            // Jika tipe SCORE tapi semua nilai_a–e NULL/0, fallback ke mode POINT
            $adaNilaiScore = false;
            if ($tipeSoal === 'SCORE') {
                foreach (['nilai_a','nilai_b','nilai_c','nilai_d','nilai_e'] as $kol) {
                    if (isset($j[$kol]) && $j[$kol] !== null && (int)$j[$kol] > 0) {
                        $adaNilaiScore = true;
                        break;
                    }
                }
                if (! $adaNilaiScore) {
                    $tipeSoal = 'POINT'; // fallback: tidak ada nilai per pilihan, pakai POINT
                }
            }

            // ── Hitung nilai per soal ────────────────────────────────────────
            if ($tipeSoal === 'SCORE') {
                // TKP: ambil nilai dari field nilai_a–nilai_e
                $nilaiSoal = 0;
                if (! $isKosong) {
                    $map        = ['a'=>'nilai_a','b'=>'nilai_b','c'=>'nilai_c','d'=>'nilai_d','e'=>'nilai_e'];
                    $kolom      = $map[$j['jawaban']] ?? null;
                    $nilaiSoal  = $kolom ? (int)($j[$kolom] ?? 0) : 0;
                }
                $isBenar = false; // SCORE tidak punya konsep benar/salah biner
            } else {
                // POINT: benar = 5, salah/kosong = 0
                $isBenar   = (! $isKosong && $j['jawaban'] === $j['kunci_jawaban']);
                $nilaiSoal = $isBenar ? 5 : 0;
            }

            // Update is_benar di jawaban_user
            $db->table('jawaban_user')
               ->where('sesi_tryout_id', $sesiId)
               ->where('soal_id', $j['soal_id'])
               ->update(['is_benar' => ($isBenar ? 1 : 0)]);

            if ($isKosong) {
                $jumlahKosong++;
            } elseif ($tipeSoal === 'POINT' && $isBenar) {
                $jumlahBenar++;
            } elseif ($tipeSoal === 'POINT') {
                $jumlahSalah++;
            }

            // ── Akumulasi per kategori ───────────────────────────────────────
            $groupId   = $j['kategori_id'];
            $groupNama = $j['kategori_nama'];
            // Gunakan $tipeSoal yang sudah di-resolve (termasuk fallback SCORE→POINT)
            $groupTipe = $tipeSoal;

            if (! isset($detailKategori[$groupId])) {
                $detailKategori[$groupId] = [
                    'kategori_id'       => $j['kategori_id'],
                    'kategori_nama'     => $j['kategori_nama'],
                    'sub_kategori_id'   => null,
                    'sub_kategori_nama' => $groupNama,
                    'tipe_soal'         => $groupTipe,
                    'benar'             => 0,
                    'salah'             => 0,
                    'kosong'            => 0,
                    'total'             => 0,
                    'total_nilai'       => 0, // akumulasi nilai aktual
                    'max_nilai'         => 0, // nilai maksimum possible
                ];
            }

            $detailKategori[$groupId]['total']++;
            $detailKategori[$groupId]['total_nilai'] += $nilaiSoal;

            if ($groupTipe === 'POINT') {
                $detailKategori[$groupId]['max_nilai'] += 5; // max per soal POINT = 5
                if ($isKosong)       $detailKategori[$groupId]['kosong']++;
                elseif ($isBenar)    $detailKategori[$groupId]['benar']++;
                else                 $detailKategori[$groupId]['salah']++;
            } else {
                // SCORE: max nilai per soal = 5 (nilai tertinggi TKP)
                $detailKategori[$groupId]['max_nilai'] += 5;
                if ($isKosong) $detailKategori[$groupId]['kosong']++;
                else           $detailKategori[$groupId]['benar']++; // "benar" = ada jawaban
            }
        }

        // ── Skor per sub kategori (persentase untuk tampilan) ────────────────
        foreach ($detailKategori as &$kat) {
            $kat['skor'] = $kat['max_nilai'] > 0
                ? round(($kat['total_nilai'] / $kat['max_nilai']) * 100, 2)
                : 0;
        }
        unset($kat);

        // ── Skor total keseluruhan ───────────────────────────────────────────
        $totalNilaiAll = array_sum(array_column($detailKategori, 'total_nilai'));
        $maxNilaiAll   = array_sum(array_column($detailKategori, 'max_nilai'));
        $skorTotal     = $maxNilaiAll > 0 ? round(($totalNilaiAll / $maxNilaiAll) * 100, 2) : 0;

        // ── Passing Grade ────────────────────────────────────────────────────
        $passingGradeData = $this->hitungPassingGrade(
            (int) $sesi['tryout_id'],
            $detailKategori
        );

        // ── Peringkat ────────────────────────────────────────────────────────
        $peringkat = $this->getRanking((int) $sesi['tryout_id'], $skorTotal);

        // ── Simpan hasil ─────────────────────────────────────────────────────
        $existing = $this->hasilModel->where('sesi_tryout_id', $sesiId)->first();

        $hasilData = [
            'sesi_tryout_id'       => $sesiId,
            'user_id'              => $sesi['user_id'],
            'tryout_id'            => $sesi['tryout_id'],
            'skor_total'           => $skorTotal,
            'total_nilai'          => $totalNilaiAll,
            'max_nilai'            => $maxNilaiAll,
            'jumlah_benar'         => $jumlahBenar,
            'jumlah_salah'         => $jumlahSalah,
            'jumlah_kosong'        => $jumlahKosong,
            'detail_kategori'      => json_encode(array_values($detailKategori)),
            'peringkat'            => $peringkat,
            'status_lulus'         => $passingGradeData['status_lulus'],
            'detail_passing_grade' => json_encode($passingGradeData['detail']),
        ];

        if ($existing) {
            $this->hasilModel->update($existing['id'], $hasilData);
            $hasilData['id'] = $existing['id'];
        } else {
            $hasilData['created_at'] = date('Y-m-d H:i:s');
            $hasilData['id']         = $this->hasilModel->insert($hasilData);
        }

        return $hasilData;
    }

    /**
     * Hitung passing grade per sub kategori.
     * Bandingkan total_nilai (bukan persentase) dengan nilai_minimum.
     * Lulus = total_nilai >= nilai_minimum untuk SEMUA sub kategori yang dikonfigurasi.
     */
    private function hitungPassingGrade(int $tryoutId, array $detailKategori): array
    {
        $db = \Config\Database::connect();

        $pgRows = $db->table('passing_grade pg')
            ->select('pg.*, k.nama AS nama_kategori, sk.nama AS nama_sub_kategori')
            ->join('kategori k',  'k.id  = pg.kategori_id',    'left')
            ->join('kategori sk', 'sk.id = pg.sub_kategori_id','left')
            ->groupStart()
                ->where('pg.tryout_id IS NULL', null, false)
                ->orWhere('pg.tryout_id', $tryoutId)
            ->groupEnd()
            ->get()->getResultArray();

        if (empty($pgRows)) {
            return ['status_lulus' => null, 'detail' => []];
        }

        $detail     = [];
        $semuaLulus = true;

        foreach ($pgRows as $pg) {
            $nilaiMinimum = (float) $pg['nilai_minimum'];
            $katId        = $pg['kategori_id'];
            $subKatId     = $pg['sub_kategori_id'];

            // Cari total_nilai aktual dari detailKategori
            $totalNilaiAktual = null;
            $tipeSoal         = 'POINT';

            if ($subKatId !== null) {
                foreach ($detailKategori as $kat) {
                    if ((int)($kat['sub_kategori_id'] ?? 0) === (int)$subKatId) {
                        $totalNilaiAktual = $kat['total_nilai'];
                        $tipeSoal         = $kat['tipe_soal'];
                        break;
                    }
                }
                $label = ($pg['nama_sub_kategori'] ?? '') ?: ($pg['nama_kategori'] ?? 'Sub Kategori');
            } elseif ($katId !== null) {
                foreach ($detailKategori as $kat) {
                    if ((int)$kat['kategori_id'] === (int)$katId) {
                        $totalNilaiAktual = ($totalNilaiAktual ?? 0) + $kat['total_nilai'];
                        $tipeSoal         = $kat['tipe_soal'];
                    }
                }
                $label = $pg['nama_kategori'] ?? 'Kategori';
            } else {
                // Overall: jumlah semua total_nilai
                $totalNilaiAktual = array_sum(array_column($detailKategori, 'total_nilai'));
                $label            = 'Keseluruhan';
            }

            if ($totalNilaiAktual === null) continue;

            $lulus = $totalNilaiAktual >= $nilaiMinimum;
            if (! $lulus) $semuaLulus = false;

            $detail[] = [
                'label'            => $label,
                'kategori_id'      => $katId,
                'sub_kategori_id'  => $subKatId,
                'tipe_soal'        => $tipeSoal,
                'nilai_minimum'    => $nilaiMinimum,
                'total_nilai'      => $totalNilaiAktual,
                'lulus'            => $lulus,
            ];
        }

        return [
            'status_lulus' => empty($detail) ? null : ($semuaLulus ? 'lulus' : 'tidak_lulus'),
            'detail'       => $detail,
        ];
    }

    public function getRanking(int $tryoutId, float $skorUser): int
    {
        $db    = \Config\Database::connect();
        $count = $db->table('hasil_tryout')
            ->where('tryout_id', $tryoutId)
            ->where('skor_total >', $skorUser)
            ->countAllResults();

        return $count + 1;
    }
}
