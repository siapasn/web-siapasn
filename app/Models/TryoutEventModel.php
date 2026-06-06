<?php

namespace App\Models;

use CodeIgniter\Model;

class TryoutEventModel extends Model
{
    protected $table            = 'tryout_event';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'slug',
        'tryout_id',
        'deskripsi',
        'banner_url',
        'mulai_pendaftaran',
        'tutup_pendaftaran',
        'mulai_pelaksanaan',
        'tutup_pelaksanaan',
        'max_percobaan',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Cari event berdasarkan slug.
     */
    public function findBySlug(string $slug): ?array
    {
        $result = $this->where('slug', $slug)->first();
        return $result ?: null;
    }

    /**
     * Ambil semua event beserta nama tryout dan jumlah peserta.
     */
    public function getAllWithDetails(): array
    {
        return $this->db->table('tryout_event te')
            ->select('te.*, t.nama AS tryout_nama, t.durasi,
                      COUNT(tep.id) AS total_peserta')
            ->join('tryout t', 't.id = te.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.event_id = te.id', 'left')
            ->groupBy('te.id')
            ->orderBy('te.mulai_pelaksanaan', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Ambil event aktif yang sedang buka pendaftaran atau pelaksanaan.
     */
    public function getActiveForUser(): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->table('tryout_event te')
            ->select('te.*, t.nama AS tryout_nama, t.durasi,
                      COUNT(tep.id) AS total_peserta')
            ->join('tryout t', 't.id = te.tryout_id')
            ->join('tryout_event_peserta tep', 'tep.event_id = te.id', 'left')
            ->where('te.is_active', 1)
            ->where('te.tutup_pelaksanaan >=', $now)
            ->groupBy('te.id')
            ->orderBy('te.mulai_pelaksanaan', 'ASC')
            ->get()->getResultArray();
    }
}
