<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table            = 'transaksi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'produk_id',
        'voucher_id',
        'kode_transaksi',
        'harga_asli',
        'diskon',
        'harga_bayar',
        'status',
        'snap_token',
        'payment_method',
        'payment_channel',
        'midtrans_order_id',
        'expired_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua transaksi milik user tertentu, diurutkan terbaru.
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Cari transaksi berdasarkan kode transaksi.
     */
    public function getByKode(string $kode): ?array
    {
        return $this->where('kode_transaksi', $kode)->first();
    }

    /**
     * Generate kode transaksi unik.
     */
    public function generateKode(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }

    /**
     * Perbarui status transaksi berdasarkan ID.
     */
    public function updateStatus(int $id, string $status): void
    {
        $this->update($id, ['status' => $status]);
    }

    /**
     * Cari transaksi berdasarkan midtrans_order_id.
     */
    public function getByMidtransOrderId(string $orderId): ?array
    {
        return $this->where('midtrans_order_id', $orderId)->first();
    }
}
