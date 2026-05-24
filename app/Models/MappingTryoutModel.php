<?php

namespace App\Models;

use CodeIgniter\Model;

class MappingTryoutModel extends Model
{
    protected $table            = 'mapping_tryout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'produk_id',
        'tryout_id',
        'urutan',
    ];

    // Tabel hanya memiliki created_at, tidak ada updated_at
    protected $useTimestamps = false;

    /**
     * Ambil semua tryout yang di-mapping ke produk tertentu,
     * diurutkan berdasarkan urutan, join tryout untuk nama.
     * Jumlah soal dihitung dari mapping_soal (bukan kolom jumlah_soal).
     */
    public function getByProduk(int $produkId): array
    {
        return $this->db->query('
            SELECT mt.id, mt.produk_id, mt.tryout_id, mt.urutan, mt.created_at,
                   t.nama AS nama_tryout, t.durasi,
                   (SELECT COUNT(*) FROM mapping_soal WHERE tryout_id = mt.tryout_id) AS jumlah_soal
            FROM mapping_tryout mt
            JOIN tryout t ON t.id = mt.tryout_id
            WHERE mt.produk_id = ?
            ORDER BY mt.urutan ASC
        ', [$produkId])->getResultArray();
    }

    /**
     * Periksa apakah pasangan (produk_id, tryout_id) sudah ada di tabel.
     */
    public function isDuplicate(int $produkId, int $tryoutId): bool
    {
        return $this->where('produk_id', $produkId)
            ->where('tryout_id', $tryoutId)
            ->countAllResults() > 0;
    }

    /**
     * Perbarui nilai urutan untuk satu baris mapping.
     */
    public function updateUrutan(int $id, int $urutan): void
    {
        $this->db->table('mapping_tryout')
            ->where('id', $id)
            ->update(['urutan' => $urutan]);
    }

    /**
     * Hitung total tryout yang di-mapping ke produk tertentu.
     */
    public function getTotalByProduk(int $produkId): int
    {
        return (int) $this->where('produk_id', $produkId)->countAllResults();
    }
}
