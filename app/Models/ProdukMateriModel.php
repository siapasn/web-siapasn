<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukMateriModel extends Model
{
    protected $table            = 'produk_materi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'produk_id',
        'judul',
        'tipe_file',
        'url_file',
        'urutan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua materi untuk produk tertentu, diurutkan berdasarkan urutan.
     */
    public function getByProduk(int $produkId): array
    {
        return $this->where('produk_id', $produkId)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Hapus semua materi milik produk tertentu.
     */
    public function deleteByProduk(int $produkId): void
    {
        $this->where('produk_id', $produkId)->delete();
    }
}
