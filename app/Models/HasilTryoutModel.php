<?php

namespace App\Models;

use CodeIgniter\Model;

class HasilTryoutModel extends Model
{
    protected $table            = 'hasil_tryout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'sesi_tryout_id',
        'user_id',
        'tryout_id',
        'skor_total',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_kosong',
        'detail_kategori',
        'peringkat',
        'status_lulus',
        'detail_passing_grade',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Ambil hasil tryout berdasarkan sesi_tryout_id.
     */
    public function getBySesi(int $sesiId): ?array
    {
        return $this->where('sesi_tryout_id', $sesiId)->first();
    }

    /**
     * Ambil semua hasil tryout milik user tertentu, diurutkan terbaru.
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Ambil semua hasil tryout untuk tryout tertentu, diurutkan skor tertinggi.
     */
    public function getByTryout(int $tryoutId): array
    {
        return $this->where('tryout_id', $tryoutId)
            ->orderBy('skor_total', 'DESC')
            ->findAll();
    }
}
