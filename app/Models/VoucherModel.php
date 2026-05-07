<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table            = 'voucher';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode',
        'jenis_diskon',
        'nilai_diskon',
        'batas_penggunaan',
        'jumlah_digunakan',
        'expired_at',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Cari voucher aktif dan belum kedaluwarsa berdasarkan kode.
     */
    public function findByKode(string $kode): ?array
    {
        $voucher = $this->where('kode', $kode)
                        ->where('is_active', 1)
                        ->first();

        if (! $voucher) {
            return null;
        }

        if (! $this->isValid($voucher)) {
            return null;
        }

        return $voucher;
    }

    /**
     * Periksa apakah voucher masih valid:
     * - is_active = 1
     * - expired_at IS NULL atau expired_at > sekarang
     * - batas_penggunaan IS NULL atau jumlah_digunakan < batas_penggunaan
     */
    public function isValid(array $voucher): bool
    {
        if (! $voucher['is_active']) {
            return false;
        }

        if ($voucher['expired_at'] !== null && strtotime($voucher['expired_at']) <= time()) {
            return false;
        }

        if ($voucher['batas_penggunaan'] !== null
            && (int) $voucher['jumlah_digunakan'] >= (int) $voucher['batas_penggunaan']) {
            return false;
        }

        return true;
    }

    /**
     * Tambah jumlah_digunakan sebesar 1.
     * Jika batas_penggunaan tercapai, nonaktifkan voucher.
     */
    public function incrementUsage(int $id): void
    {
        $this->db->query(
            'UPDATE voucher SET jumlah_digunakan = jumlah_digunakan + 1 WHERE id = ?',
            [$id]
        );

        // Re-fetch to check if limit reached
        $voucher = $this->find($id);
        if ($voucher
            && $voucher['batas_penggunaan'] !== null
            && (int) $voucher['jumlah_digunakan'] >= (int) $voucher['batas_penggunaan']) {
            $this->db->table($this->table)
                ->where('id', $id)
                ->update(['is_active' => 0]);
        }
    }

    /**
     * Nonaktifkan semua voucher yang sudah kedaluwarsa.
     * Mengembalikan jumlah baris yang diperbarui.
     */
    public function deactivateExpired(): int
    {
        $this->db->table($this->table)
            ->where('expired_at <', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->update(['is_active' => 0]);

        return $this->db->affectedRows();
    }
}
