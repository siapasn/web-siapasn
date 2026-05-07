<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AuditLogModel
 *
 * Menyimpan log aktivitas penting yang dilakukan oleh pengguna (terutama Super Admin).
 * Tabel: audit_log
 */
class AuditLogModel extends Model
{
    protected $table            = 'audit_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'aksi',
        'detail',
        'ip_address',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Catat satu entri log audit.
     *
     * @param int    $userId    ID pengguna yang melakukan aksi
     * @param string $aksi      Nama aksi singkat (mis. "Tambah Akun")
     * @param string $detail    Deskripsi detail aksi
     * @param string $ipAddress Alamat IP pengguna
     */
    public function catat(int $userId, string $aksi, string $detail = '', string $ipAddress = ''): void
    {
        $this->insert([
            'user_id'    => $userId,
            'aksi'       => $aksi,
            'detail'     => $detail,
            'ip_address' => $ipAddress,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
