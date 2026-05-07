<?php

namespace App\Models;

use CodeIgniter\Model;

class JawabanUserModel extends Model
{
    protected $table            = 'jawaban_user';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'sesi_tryout_id',
        'soal_id',
        'jawaban',
        'is_benar',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Simpan atau perbarui jawaban user (upsert berdasarkan sesi_tryout_id + soal_id).
     * Jawaban null berarti soal tidak dijawab.
     */
    public function simpanJawaban(int $sesiId, int $soalId, ?string $jawaban): void
    {
        $existing = $this->where('sesi_tryout_id', $sesiId)
            ->where('soal_id', $soalId)
            ->first();

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $this->update($existing['id'], [
                'jawaban'    => $jawaban,
                'updated_at' => $now,
            ]);
        } else {
            $this->db->table('jawaban_user')->insert([
                'sesi_tryout_id' => $sesiId,
                'soal_id'        => $soalId,
                'jawaban'        => $jawaban,
                'is_benar'       => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    /**
     * Ambil semua jawaban untuk sesi tertentu.
     */
    public function getBySesi(int $sesiId): array
    {
        return $this->where('sesi_tryout_id', $sesiId)->findAll();
    }

    /**
     * Alias untuk getBySesi() — digunakan di TryoutController.
     */
    public function getJawabanSesi(int $sesiId): array
    {
        return $this->getBySesi($sesiId);
    }

    /**
     * Hitung jumlah jawaban benar untuk sesi tertentu.
     */
    public function hitungBenar(int $sesiId): int
    {
        return (int) $this->where('sesi_tryout_id', $sesiId)
            ->where('is_benar', 1)
            ->countAllResults();
    }

    /**
     * Hitung jumlah jawaban benar, salah, dan kosong untuk sesi tertentu.
     *
     * @return array{benar: int, salah: int, kosong: int}
     */
    public function countBySesi(int $sesiId): array
    {
        $jawaban = $this->where('sesi_tryout_id', $sesiId)->findAll();

        $benar  = 0;
        $salah  = 0;
        $kosong = 0;

        foreach ($jawaban as $j) {
            if ($j['jawaban'] === null) {
                $kosong++;
            } elseif ($j['is_benar'] == 1) {
                $benar++;
            } else {
                $salah++;
            }
        }

        return [
            'benar'  => $benar,
            'salah'  => $salah,
            'kosong' => $kosong,
        ];
    }
}
