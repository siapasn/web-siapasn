<?php

namespace App\Models;

use CodeIgniter\Model;

class SesiTryoutModel extends Model
{
    protected $table            = 'sesi_tryout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'tryout_id',
        'mulai_at',
        'selesai_at',
        'status',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Cari sesi yang sedang berlangsung untuk user dan tryout tertentu.
     */
    public function getAktif(int $userId, int $tryoutId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('tryout_id', $tryoutId)
            ->where('status', 'berlangsung')
            ->first();
    }

    /**
     * Cari sesi yang sudah selesai atau timeout untuk user dan tryout tertentu.
     */
    public function getSelesai(int $userId, int $tryoutId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('tryout_id', $tryoutId)
            ->whereIn('status', ['selesai', 'timeout'])
            ->first();
    }

    /**
     * Periksa apakah user sudah menyelesaikan tryout (status selesai atau timeout).
     * Alias: sudahSelesai()
     */
    public function isSelesai(int $userId, int $tryoutId): bool
    {
        $count = $this->where('user_id', $userId)
            ->where('tryout_id', $tryoutId)
            ->whereIn('status', ['selesai', 'timeout'])
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Alias untuk isSelesai() — digunakan di TryoutController.
     */
    public function sudahSelesai(int $userId, int $tryoutId): bool
    {
        return $this->isSelesai($userId, $tryoutId);
    }

    /**
     * Mulai sesi tryout baru dan kembalikan ID sesi yang dibuat.
     */
    public function mulai(int $userId, int $tryoutId): int
    {
        $data = [
            'user_id'    => $userId,
            'tryout_id'  => $tryoutId,
            'mulai_at'   => date('Y-m-d H:i:s'),
            'selesai_at' => null,
            'status'     => 'berlangsung',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('sesi_tryout')->insert($data);

        return (int) $this->db->insertID();
    }

    /**
     * Tandai sesi sebagai selesai dengan mencatat waktu selesai dan status.
     */
    public function selesai(int $sesiId, string $status = 'selesai'): void
    {
        $this->update($sesiId, [
            'selesai_at' => date('Y-m-d H:i:s'),
            'status'     => $status,
        ]);

        // Update status peserta event jika sesi ini terkait event
        $this->db->table('tryout_event_peserta')
            ->where('sesi_tryout_id', $sesiId)
            ->update(['status' => 'completed']);
    }

    /**
     * Alias untuk selesai() — digunakan di TryoutController.
     */
    public function selesaikan(int $sesiId, string $status = 'selesai'): void
    {
        $this->selesai($sesiId, $status);
    }
}

