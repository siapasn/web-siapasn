<?php

namespace App\Models;

use CodeIgniter\Model;

class FormasiModel extends Model
{
    protected $table            = 'formasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kategori_formasi_id',
        'nama',
        'deskripsi',
        'referensi',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua formasi beserta nama kategori-nya.
     */
    public function getAllWithKategori(): array
    {
        return $this->db->table('formasi f')
            ->select('f.*, kf.nama AS kategori_nama')
            ->join('kategori_formasi kf', 'kf.id = f.kategori_formasi_id', 'left')
            ->orderBy('kf.urutan', 'ASC')
            ->orderBy('f.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil formasi berdasarkan kategori tertentu.
     */
    public function getByKategori(int $kategoriFormasiId): array
    {
        return $this->where('kategori_formasi_id', $kategoriFormasiId)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }
}
