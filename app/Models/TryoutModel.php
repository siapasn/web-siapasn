<?php

namespace App\Models;

use CodeIgniter\Model;

class TryoutModel extends Model
{
    protected $table            = 'tryout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'durasi',
        'jumlah_soal',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua tryout yang aktif (is_active = 1).
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Ambil daftar soal untuk tryout tertentu melalui mapping_soal,
     * diurutkan berdasarkan kolom urutan.
     */
    public function getSoal(int $tryoutId): array
    {
        return $this->db->table('mapping_soal ms')
            ->select(
                'ms.urutan, ms.soal_id, s.pertanyaan, s.pilihan_a, s.pilihan_b, ' .
                's.pilihan_c, s.pilihan_d, s.pilihan_e, ' .
                's.nilai_a, s.nilai_b, s.nilai_c, s.nilai_d, s.nilai_e, ' .
                's.kunci_jawaban, s.pembahasan, s.kategori_id'
            )
            ->join('soal s', 's.id = ms.soal_id')
            ->where('ms.tryout_id', $tryoutId)
            ->orderBy('ms.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }
}
