<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriFormasiModel extends Model
{
    protected $table            = 'kategori_formasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'deskripsi',
        'icon',
        'urutan',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua kategori formasi beserta jumlah formasi di dalamnya.
     */
    public function getAllWithCount(): array
    {
        return $this->db->table('kategori_formasi kf')
            ->select('kf.*, COUNT(f.id) AS jumlah_formasi')
            ->join('formasi f', 'f.kategori_formasi_id = kf.id', 'left')
            ->groupBy('kf.id')
            ->orderBy('kf.urutan', 'ASC')
            ->orderBy('kf.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil semua kategori formasi yang aktif.
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('nama', 'ASC')
            ->findAll();
    }
}
