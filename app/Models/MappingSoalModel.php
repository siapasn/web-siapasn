<?php

namespace App\Models;

use CodeIgniter\Model;

class MappingSoalModel extends Model
{
    protected $table            = 'mapping_soal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'tryout_id',
        'soal_id',
        'urutan',
    ];

    // Tabel hanya memiliki created_at, tidak ada updated_at
    protected $useTimestamps = false;

    /**
     * Ambil semua soal yang di-mapping ke tryout tertentu,
     * diurutkan berdasarkan urutan, join soal untuk pertanyaan dan kategori.
     */
    public function getByTryout(int $tryoutId): array
    {
        return $this->db->table('mapping_soal ms')
            ->select('ms.id, ms.tryout_id, ms.soal_id, ms.urutan, ms.created_at, s.pertanyaan, k.nama AS nama_kategori')
            ->join('soal s', 's.id = ms.soal_id')
            ->join('kategori k', 'k.id = s.kategori_id', 'left')
            ->where('ms.tryout_id', $tryoutId)
            ->orderBy('ms.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Periksa apakah pasangan (tryout_id, soal_id) sudah ada di tabel.
     */
    public function isDuplicate(int $tryoutId, int $soalId): bool
    {
        return $this->where('tryout_id', $tryoutId)
            ->where('soal_id', $soalId)
            ->countAllResults() > 0;
    }

    /**
     * Perbarui nilai urutan untuk satu baris mapping.
     */
    public function updateUrutan(int $id, int $urutan): void
    {
        $this->db->table('mapping_soal')
            ->where('id', $id)
            ->update(['urutan' => $urutan]);
    }

    /**
     * Hitung total soal yang di-mapping ke tryout tertentu.
     */
    public function getTotalByTryout(int $tryoutId): int
    {
        return (int) $this->where('tryout_id', $tryoutId)->countAllResults();
    }
}
