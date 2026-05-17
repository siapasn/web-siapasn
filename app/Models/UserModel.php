<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'email',
        'telepon',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'login_attempts',
        'locked_until',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Cari user berdasarkan email.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Tambah login_attempts sebesar 1 menggunakan raw SQL.
     */
    public function incrementLoginAttempts(int $userId): void
    {
        $this->db->query(
            'UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Reset login_attempts ke 0 dan hapus locked_until.
     */
    public function resetLoginAttempts(int $userId): void
    {
        $this->update($userId, [
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);
    }

    /**
     * Kunci akun selama $minutes menit dari sekarang.
     */
    public function lockAccount(int $userId, int $minutes = 15): void
    {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

        $this->update($userId, [
            'locked_until' => $lockedUntil,
        ]);
    }

    /**
     * Periksa apakah akun sedang terkunci.
     */
    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    /**
     * Tandai email sebagai terverifikasi.
     */
    public function verifyEmail(int $userId): void
    {
        $this->update($userId, [
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil semua user berdasarkan role.
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }
}
