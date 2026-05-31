<?php

namespace App\Models;

use CodeIgniter\Model;

class UlasanModel extends Model
{
    protected $table            = 'ulasan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'produk_id',
        'rating',
        'komentar',
        'is_visible',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil ulasan visible untuk produk tertentu.
     */
    public function getByProduk(int $produkId): array
    {
        return $this->db->table('ulasan u')
            ->select('u.*, us.nama AS user_nama')
            ->join('users us', 'us.id = u.user_id')
            ->where('u.produk_id', $produkId)
            ->where('u.is_visible', 1)
            ->orderBy('u.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Hitung rata-rata rating produk.
     */
    public function getAvgRating(int $produkId): float
    {
        $row = $this->db->table('ulasan')
            ->selectAvg('rating')
            ->where('produk_id', $produkId)
            ->where('is_visible', 1)
            ->get()->getRowArray();

        return round((float) ($row['rating'] ?? 0), 1);
    }

    /**
     * Cek apakah user sudah pernah memberi ulasan untuk produk ini.
     */
    public function hasReviewed(int $userId, int $produkId): bool
    {
        return $this->where('user_id', $userId)
            ->where('produk_id', $produkId)
            ->countAllResults() > 0;
    }

    /**
     * Ambil semua ulasan (untuk admin).
     */
    public function getAllWithDetails(): array
    {
        return $this->db->table('ulasan u')
            ->select('u.*, us.nama AS user_nama, us.email AS user_email, p.nama AS produk_nama')
            ->join('users us', 'us.id = u.user_id')
            ->join('produk p', 'p.id = u.produk_id')
            ->orderBy('u.created_at', 'DESC')
            ->get()->getResultArray();
    }
}
