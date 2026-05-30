<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'produk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'kategori_id',
        'formasi_id',
        'deskripsi',
        'thumbnail',
        'harga',
        'is_active',
        'is_highlight',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua produk yang aktif.
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Periksa apakah produk memiliki transaksi aktif (success atau pending).
     */
    public function hasActiveTransaksi(int $produkId): bool
    {
        $count = $this->db->table('transaksi')
            ->where('produk_id', $produkId)
            ->whereIn('status', ['success', 'pending'])
            ->countAllResults();

        return $count > 0;
    }
}
