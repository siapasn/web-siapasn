<?php

namespace App\Services;

class VoucherService
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Validate voucher and return voucher data or null if invalid.
     * Jika $userId diberikan, cek apakah user sudah pernah memakai voucher ini.
     */
    public function validate(string $kode, ?int $userId = null): ?array
    {
        // Auto-deactivate expired vouchers before validating
        $this->db->table('voucher')
            ->where('expired_at <', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->update(['is_active' => 0]);

        $voucher = $this->db->table('voucher')
            ->where('kode', $kode)
            ->where('is_active', 1)
            ->get()->getRowArray();

        if (!$voucher) return null;

        // Check expiry
        if ($voucher['expired_at'] && strtotime($voucher['expired_at']) < time()) {
            return null;
        }

        // Check global usage limit
        if ($voucher['batas_penggunaan'] !== null && $voucher['jumlah_digunakan'] >= $voucher['batas_penggunaan']) {
            return null;
        }

        // Check per-user usage: voucher hanya bisa dipakai 1x per user
        if ($userId !== null) {
            $sudahDipakai = $this->db->table('transaksi')
                ->where('user_id', $userId)
                ->where('voucher_id', $voucher['id'])
                ->whereIn('status', ['success', 'pending'])
                ->countAllResults();

            if ($sudahDipakai > 0) {
                return null; // user sudah pernah memakai voucher ini
            }
        }

        return $voucher;
    }

    /**
     * Calculate discounted price.
     */
    public function hitungDiskon(float $hargaAsli, array $voucher): float
    {
        if ($voucher['jenis_diskon'] === 'persentase') {
            return round($hargaAsli * ($voucher['nilai_diskon'] / 100), 2);
        }
        return min((float)$voucher['nilai_diskon'], $hargaAsli);
    }

    /**
     * Apply voucher usage (increment jumlah_digunakan, deactivate if limit reached).
     */
    public function apply(int $voucherId): void
    {
        $this->db->query(
            'UPDATE voucher SET jumlah_digunakan = jumlah_digunakan + 1 WHERE id = ?',
            [$voucherId]
        );

        // Auto-deactivate if limit reached
        $voucher = $this->db->table('voucher')->where('id', $voucherId)->get()->getRowArray();
        if ($voucher && $voucher['batas_penggunaan'] !== null && $voucher['jumlah_digunakan'] >= $voucher['batas_penggunaan']) {
            $this->db->table('voucher')->where('id', $voucherId)->update(['is_active' => 0]);
        }
    }
}
