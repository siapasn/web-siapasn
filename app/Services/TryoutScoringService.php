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

        // Ambil semua soal pada tryout, lalu LEFT JOIN jawaban user.
        // Soal yang belum dijawab tetap dihitung sebagai kosong agar passing grade akurat.
        $sql = "
            SELECT
                s.id AS soal_id,
                ju.jawaban,
                s.kunci_jawaban,
                s.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e,
                s.kategori_id,
                k.nama        AS kategori_nama,
                k.tipe_soal   AS kategori_tipe
            FROM mapping_soal ms
            INNER JOIN soal s ON s.id = ms.soal_id
            LEFT JOIN jawaban_user ju
                ON ju.soal_id = ms.soal_id
                AND ju.sesi_tryout_id = ?
            LEFT JOIN kategori k ON k.id = s.kategori_id
            WHERE ms.tryout_id = ?
            ORDER BY ms.urutan ASC
        ";
        $jawaban = $db->query($sql, [$sesiId, $sesi['tryout_id']])->getResultArray();

        if (empty($jawaban)) {
            throw new \RuntimeException("Tryout ini belum memiliki soal: {$sesi['tryout_id']}");
        }

        $jumlahBenar    = 0;
        $jumlahSalah    = 0;
        $jumlahKosong   = 0;
        $detailKategori = [];

        foreach ($jawaban as $j) {
            $tipeSoal = $j['kategori_tipe'] ?? 'SCORE';
            $isKosong = ($j['jawaban'] === null || $j['jawaban'] === '');

            // ── Deteksi tipe soal berdasarkan data aktual ──
            // POINT = setiap pilihan punya nilai 1-5 (TKP), tidak ada benar/salah
            // SCORE = ada kunci jawaban benar/salah (TWK, TIU, SKB), benar = 5 poin
            //
            // Fallback: jika tipe SCORE tapi tidak ada kunci_jawaban dan ada nilai_a–e → treat as POINT
            //           jika tipe POINT tapi tidak ada nilai_a–e dan ada kunci → treat as SCORE
            $adaNilai = false;
            foreach (['nilai_a','nilai_b','nilai_c','nilai_d','nilai_e'] as $kol) {
                if (isset($j[$kol]) && $j[$kol] !== null && (int)$j[$kol] > 0) {
                    $adaNilai = true;
                    break;
                }
            }
            $adaKunci = ! empty($j['kunci_jawaban']);

            // Resolve tipe berdasarkan data aktual
            if ($tipeSoal === 'POINT' && $adaNilai) {
                // Benar: POINT dengan nilai per pilihan
                $tipeSoal = 'POINT';
            } elseif ($tipeSoal === 'SCORE' && $adaKunci) {
                // Benar: SCORE dengan kunci jawaban
                $tipeSoal = 'SCORE';
            } elseif ($adaNilai && ! $adaKunci) {
                // Data punya nilai tapi tidak ada kunci → POINT
                $tipeSoal = 'POINT';
            } elseif ($adaKunci && ! $adaNilai) {
                // Data punya kunci tapi tidak ada nilai → SCORE
                $tipeSoal = 'SCORE';
            }
            // Jika keduanya ada atau keduanya tidak ada, pakai tipe dari kategori

            // ── Hitung nilai per soal ────────────────────────────────────────
            if ($tipeSoal === 'POINT') {
                // POINT (TKP): ambil nilai dari field nilai_a–nilai_e
                $nilaiSoal = 0;
                if (! $isKosong) {
                    $map        = ['a'=>'nilai_a','b'=>'nilai_b','c'=>'nilai_c','d'=>'nilai_d','e'=>'nilai_e'];
                    $kolom      = $map[$j['jawaban']] ?? null;
                    $nilaiSoal  = $kolom ? (int)($j[$kolom] ?? 0) : 0;
                }
                $isBenar = false; // POINT tidak punya konsep benar/salah
            } else {
                // SCORE (TWK/TIU/SKB): benar = 5, salah/kosong = 0
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
            } elseif ($tipeSoal === 'SCORE' && $isBenar) {
                $jumlahBenar++;
            } elseif ($tipeSoal === 'SCORE') {
                $jumlahSalah++;
            }
            // POINT: tidak menambah jumlahBenar/jumlahSalah

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
                // POINT (TKP): max per soal = 5, tidak ada benar/salah
                $detailKategori[$groupId]['max_nilai'] += 5;
                if ($isKosong) $detailKategori[$groupId]['kosong']++;
                else           $detailKategori[$groupId]['benar']++; // "benar" = ada jawaban (untuk counter)
            } else {
                // SCORE (TWK/TIU/SKB): max per soal = 5, ada benar/salah
                $detailKategori[$groupId]['max_nilai'] += 5;
                if ($isKosong)       $detailKategori[$groupId]['kosong']++;
                elseif ($isBenar)    $detailKategori[$groupId]['benar']++;
                else                 $detailKategori[$groupId]['salah']++;
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
                    // Match: sub_kategori_id di PG cocok dengan sub_kategori_id di detail
                    // ATAU: sub_kategori_id di PG cocok dengan kategori_id di detail
                    //       (karena soal bisa langsung terhubung ke sub-kategori sebagai kategori_id)
                    $matchBySub = (int)($kat['sub_kategori_id'] ?? 0) === (int)$subKatId;
                    $matchByKat = (int)($kat['kategori_id'] ?? 0) === (int)$subKatId;

                    if ($matchBySub || $matchByKat) {
                        $totalNilaiAktual = ($totalNilaiAktual ?? 0) + (int) $kat['total_nilai'];
                        $tipeSoal         = $kat['tipe_soal'];
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
