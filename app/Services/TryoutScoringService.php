<?php

namespace App\Services;

use App\Models\JawabanUserModel;
use App\Models\HasilTryoutModel;
use App\Models\SesiTryoutModel;

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

    /**
     * Hitung dan simpan hasil tryout untuk sesi tertentu.
     * Termasuk passing grade per sub kategori.
     */
    public function hitung(int $sesiId): array
    {
        $sesi = $this->sesiModel->find($sesiId);
        if (! $sesi) {
            throw new \RuntimeException("Sesi tryout tidak ditemukan: {$sesiId}");
        }

        $db = \Config\Database::connect();

        // Ambil semua jawaban beserta kunci jawaban, nilai, dan kategori soal
        $jawaban = $db->table('jawaban_user ju')
            ->select('ju.soal_id, ju.jawaban,
                      s.kunci_jawaban, s.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e,
                      s.kategori_id, s.sub_kategori_id,
                      k.nama as kategori_nama, k.tipe_soal as kategori_tipe,
                      sk.nama as sub_kategori_nama, sk.tipe_soal as sub_tipe_soal')
            ->join('soal s', 's.id = ju.soal_id')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->join('kategori sk', 'sk.id = s.sub_kategori_id', 'left')
            ->where('ju.sesi_tryout_id', $sesiId)
            ->get()->getResultArray();

        $jumlahBenar  = 0;
        $jumlahSalah  = 0;
        $jumlahKosong = 0;
        $detailKategori = [];

        foreach ($jawaban as $j) {
            $tipeSoal = $j['sub_tipe_soal'] ?? $j['kategori_tipe'] ?? 'POINT';
            $isKosong = ($j['jawaban'] === null || $j['jawaban'] === '');

            // Hitung skor berdasarkan tipe soal
            if ($tipeSoal === 'SCORE') {
                // TKP: nilai berdasarkan pilihan yang dipilih
                $nilaiSoal = 0;
                if (! $isKosong) {
                    $pilihanMap = ['a' => 'nilai_a', 'b' => 'nilai_b', 'c' => 'nilai_c', 'd' => 'nilai_d', 'e' => 'nilai_e'];
                    $kolomNilai = $pilihanMap[$j['jawaban']] ?? null;
                    $nilaiSoal  = $kolomNilai ? (int) ($j[$kolomNilai] ?? 0) : 0;
                }
                $isBenar = ! $isKosong && $nilaiSoal > 0;
            } else {
                // POINT: benar/salah berdasarkan kunci jawaban
                $isBenar   = (! $isKosong && $j['jawaban'] === $j['kunci_jawaban']);
                $nilaiSoal = $isBenar ? 1 : 0;
            }

            // Update is_benar di jawaban_user
            $db->table('jawaban_user')
               ->where('sesi_tryout_id', $sesiId)
               ->where('soal_id', $j['soal_id'])
               ->update(['is_benar' => ($isBenar ? 1 : 0)]);

            if ($isKosong) {
                $jumlahKosong++;
            } elseif ($isBenar) {
                $jumlahBenar++;
            } else {
                $jumlahSalah++;
            }

            // Akumulasi per sub kategori (atau kategori jika tidak ada sub)
            $groupId   = $j['sub_kategori_id'] ?? $j['kategori_id'];
            $groupNama = $j['sub_kategori_id']
                ? ($j['sub_kategori_nama'] ?? $j['kategori_nama'])
                : $j['kategori_nama'];
            $groupTipe = $j['sub_tipe_soal'] ?? $j['kategori_tipe'] ?? 'POINT';

            if (! isset($detailKategori[$groupId])) {
                $detailKategori[$groupId] = [
                    'kategori_id'      => $j['kategori_id'],
                    'kategori_nama'    => $j['kategori_nama'],
                    'sub_kategori_id'  => $j['sub_kategori_id'],
                    'sub_kategori_nama'=> $groupNama,
                    'tipe_soal'        => $groupTipe,
                    'benar'            => 0,
                    'salah'            => 0,
                    'kosong'           => 0,
                    'total'            => 0,
                    'total_nilai'      => 0, // untuk SCORE (TKP)
                    'max_nilai'        => 0, // untuk SCORE (TKP)
                ];
            }

            $detailKategori[$groupId]['total']++;
            if ($isKosong) {
                $detailKategori[$groupId]['kosong']++;
            } elseif ($isBenar) {
                $detailKategori[$groupId]['benar']++;
            } else {
                $detailKategori[$groupId]['salah']++;
            }

            // Akumulasi nilai untuk SCORE
            if ($groupTipe === 'SCORE') {
                $detailKategori[$groupId]['total_nilai'] += $nilaiSoal;
                $detailKategori[$groupId]['max_nilai']   += 5; // nilai max per soal TKP = 5
            }
        }

        $totalSoal = count($jawaban);
        $skorTotal = $totalSoal > 0 ? round(($jumlahBenar / $totalSoal) * 100, 2) : 0;

        // Hitung skor per sub kategori
        foreach ($detailKategori as &$kat) {
            if ($kat['tipe_soal'] === 'SCORE') {
                // TKP: skor = (total_nilai / max_nilai) * 100
                $kat['skor'] = $kat['max_nilai'] > 0
                    ? round(($kat['total_nilai'] / $kat['max_nilai']) * 100, 2)
                    : 0;
            } else {
                // POINT: skor = (benar / total) * 100
                $kat['skor'] = $kat['total'] > 0
                    ? round(($kat['benar'] / $kat['total']) * 100, 2)
                    : 0;
            }
        }
        unset($kat);

        // ── Passing Grade ────────────────────────────────────────────────────
        $passingGradeData = $this->hitungPassingGrade(
            (int) $sesi['tryout_id'],
            $skorTotal,
            $detailKategori
        );

        // Hitung peringkat
        $peringkat = $this->getRanking((int) $sesi['tryout_id'], $skorTotal);

        // Simpan atau update hasil
        $existing = $this->hasilModel->where('sesi_tryout_id', $sesiId)->first();

        $hasilData = [
            'sesi_tryout_id'      => $sesiId,
            'user_id'             => $sesi['user_id'],
            'tryout_id'           => $sesi['tryout_id'],
            'skor_total'          => $skorTotal,
            'jumlah_benar'        => $jumlahBenar,
            'jumlah_salah'        => $jumlahSalah,
            'jumlah_kosong'       => $jumlahKosong,
            'detail_kategori'     => json_encode(array_values($detailKategori)),
            'peringkat'           => $peringkat,
            'status_lulus'        => $passingGradeData['status_lulus'],
            'detail_passing_grade'=> json_encode($passingGradeData['detail']),
        ];

        if ($existing) {
            $this->hasilModel->update($existing['id'], $hasilData);
            $hasilData['id'] = $existing['id'];
        } else {
            $hasilData['created_at'] = date('Y-m-d H:i:s');
            $id = $this->hasilModel->insert($hasilData);
            $hasilData['id'] = $id;
        }

        return $hasilData;
    }

    /**
     * Hitung passing grade berdasarkan konfigurasi di tabel passing_grade.
     * Cek per sub kategori, lalu overall.
     *
     * @return array ['status_lulus' => 'lulus'|'tidak_lulus'|null, 'detail' => [...]]
     */
    private function hitungPassingGrade(int $tryoutId, float $skorTotal, array $detailKategori): array
    {
        $db = \Config\Database::connect();

        // Ambil semua passing grade yang relevan (global + per kategori)
        $pgRows = $db->table('passing_grade pg')
            ->select('pg.*, k.nama as nama_kategori, sk.nama as nama_sub_kategori')
            ->join('kategori k', 'k.id = pg.kategori_id', 'left')
            ->join('kategori sk', 'sk.id = pg.sub_kategori_id', 'left')
            ->where('pg.tryout_id IS NULL', null, false) // global
            ->orWhere('pg.tryout_id', $tryoutId)         // spesifik tryout
            ->get()->getResultArray();

        if (empty($pgRows)) {
            // Tidak ada konfigurasi passing grade
            return ['status_lulus' => null, 'detail' => []];
        }

        $detail    = [];
        $semuaLulus = true;

        foreach ($pgRows as $pg) {
            $nilaiMinimum = (float) $pg['nilai_minimum'];
            $katId        = $pg['kategori_id'];
            $subKatId     = $pg['sub_kategori_id'];

            if ($katId === null) {
                // Passing grade overall (skor total)
                $skorAktual = $skorTotal;
                $label      = 'Keseluruhan';
            } elseif ($subKatId !== null) {
                // Passing grade per sub kategori
                $skorAktual = null;
                foreach ($detailKategori as $kat) {
                    if ((int) ($kat['sub_kategori_id'] ?? 0) === (int) $subKatId) {
                        $skorAktual = $kat['skor'];
                        break;
                    }
                }
                $label = ($pg['nama_sub_kategori'] ?? '') ?: ($pg['nama_kategori'] ?? 'Sub Kategori');
            } else {
                // Passing grade per kategori (tanpa sub)
                $skorAktual = null;
                foreach ($detailKategori as $kat) {
                    if ((int) $kat['kategori_id'] === (int) $katId && empty($kat['sub_kategori_id'])) {
                        $skorAktual = $kat['skor'];
                        break;
                    }
                }
                $label = $pg['nama_kategori'] ?? 'Kategori';
            }

            if ($skorAktual === null) continue;

            $lulus = $skorAktual >= $nilaiMinimum;
            if (! $lulus) $semuaLulus = false;

            $detail[] = [
                'label'         => $label,
                'kategori_id'   => $katId,
                'sub_kategori_id' => $subKatId,
                'nilai_minimum' => $nilaiMinimum,
                'skor_aktual'   => round($skorAktual, 2),
                'lulus'         => $lulus,
            ];
        }

        return [
            'status_lulus' => empty($detail) ? null : ($semuaLulus ? 'lulus' : 'tidak_lulus'),
            'detail'       => $detail,
        ];
    }

    /**
     * Hitung peringkat user berdasarkan skor untuk tryout tertentu.
     */
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
