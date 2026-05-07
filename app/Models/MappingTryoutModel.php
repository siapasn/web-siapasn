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
     */
    public function getByProduk(int $produkId): array
    {
        return $this->db->table('mapping_tryout mt')
            ->select('mt.id, mt.produk_id, mt.tryout_id, mt.urutan, mt.created_at, t.nama AS nama_tryout, t.durasi, t.jumlah_soal')
            ->join('tryout t', 't.id = mt.tryout_id')
            ->where('mt.produk_id', $produkId)
            ->orderBy('mt.urutan', 'ASC')
            ->get()
            ->getResultArray();
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
