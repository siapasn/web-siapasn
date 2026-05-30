<?php

namespace App\Models;

use CodeIgniter\Model;

class UserProdukModel extends Model
{
    protected $table            = 'user_produk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'produk_id',
        'transaksi_id',
        'expired_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Periksa apakah user memiliki akses aktif ke produk tertentu.
     * Akses aktif: expired_at IS NULL atau expired_at > sekarang.
     */
    public function hasAccess(int $userId, int $produkId): bool
    {
        $count = $this->db->table('user_produk')
            ->where('user_id', $userId)
            ->where('produk_id', $produkId)
            ->groupStart()
                ->where('expired_at IS NULL', null, false)
                ->orWhere('expired_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Aktifkan atau perbarui akses user ke produk.
     * Jika sudah ada, update; jika belum, insert.
     * expired_at dihitung otomatis dari config 'produk_expired_days' di master_aplikasi.
     */
    public function aktivasiAkses(int $userId, int $produkId, int $transaksiId, ?string $expiredAt = null): void
    {
        // Jika expiredAt tidak diberikan, hitung dari config
        if ($expiredAt === null) {
            $expiredAt = $this->hitungExpiredAt();
        }

        $existing = $this->where('user_id', $userId)
            ->where('produk_id', $produkId)
            ->first();

        $data = [
            'user_id'      => $userId,
            'produk_id'    => $produkId,
            'transaksi_id' => $transaksiId,
            'expired_at'   => $expiredAt,
        ];

        if ($existing) {
            $this->update($existing['id'], [
                'transaksi_id' => $transaksiId,
                'expired_at'   => $expiredAt,
            ]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('user_produk')->insert($data);
        }
    }

    /**
     * Hitung expired_at berdasarkan config 'produk_expired_days' di master_aplikasi.
     * Default: 365 hari dari sekarang.
     */
    private function hitungExpiredAt(): string
    {
        $days = 365; // default 1 tahun

        try {
            $row = $this->db->table('master_aplikasi')
                ->where('config_key', 'produk_expired_days')
                ->get()->getRowArray();

            if ($row && is_numeric($row['config_value']) && (int) $row['config_value'] > 0) {
                $days = (int) $row['config_value'];
            }
        } catch (\Throwable $e) {
            // Gunakan default jika gagal
        }

        return date('Y-m-d H:i:s', strtotime("+{$days} days"));
    }

    /**
     * Ambil semua produk yang dimiliki user beserta data produk.
     */
    public function getByUser(int $userId): array
    {
        return $this->db->table('user_produk up')
            ->select('up.*, p.nama as produk_nama, p.deskripsi, p.harga, up.expired_at, up.created_at')
            ->join('produk p', 'p.id = up.produk_id')
            ->where('up.user_id', $userId)
            ->get()
            ->getResultArray();
    }
}
