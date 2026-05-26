<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriBukuModel extends Model
{
    protected $table            = 'kategori_buku';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'urutan',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua kategori buku yang aktif, urut berdasarkan urutan.
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }
}
