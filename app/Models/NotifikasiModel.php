<?php

namespace App\Models;

use CodeIgniter\Model;

class NotifikasiModel extends Model
{
    protected $table            = 'notifikasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'tipe',
        'judul',
        'pesan',
        'url',
        'is_read',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Buat notifikasi baru.
     */
    public static function kirim(int $userId, string $tipe, string $judul, ?string $pesan = null, ?string $url = null): void
    {
        $db = \Config\Database::connect();
        $db->table('notifikasi')->insert([
            'user_id'    => $userId,
            'tipe'       => $tipe,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'url'        => $url,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu.
     */
    public static function kirimKeRole(string $role, string $tipe, string $judul, ?string $pesan = null, ?string $url = null): void
    {
        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('id')
            ->where('role', $role)
            ->where('is_active', 1)
            ->get()->getResultArray();

        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($users as $u) {
            $batch[] = [
                'user_id'    => $u['id'],
                'tipe'       => $tipe,
                'judul'      => $judul,
                'pesan'      => $pesan,
                'url'        => $url,
                'is_read'    => 0,
                'created_at' => $now,
            ];
        }

        if (! empty($batch)) {
            $db->table('notifikasi')->insertBatch($batch);
        }
    }

    /**
     * Ambil notifikasi terbaru untuk user (max 20).
     */
    public function getForUser(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Hitung notifikasi belum dibaca.
     */
    public function countUnread(int $userId): int
    {
        return (int) $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Tandai semua sebagai dibaca.
     */
    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->set('is_read', 1)
            ->update();
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function markRead(int $id, int $userId): void
    {
        $this->where('id', $id)
            ->where('user_id', $userId)
            ->set('is_read', 1)
            ->update();
    }
}
