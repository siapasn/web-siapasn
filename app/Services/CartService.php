<?php

namespace App\Services;

/**
 * CartService
 *
 * Mengelola keranjang belanja user di Redis.
 * Key format: cart:{user_id}
 * Value: JSON array of produk_id
 * TTL: 7 hari
 */
class CartService
{
    protected RedisService $redis;
    protected int          $ttl = 604800; // 7 hari

    public function __construct()
    {
        $this->redis = new RedisService();
    }

    protected function key(int $userId): string
    {
        return "cart:{$userId}";
    }

    /**
     * Ambil semua item di keranjang user.
     * @return int[]
     */
    public function getItems(int $userId): array
    {
        $raw = $this->redis->get($this->key($userId));
        if ($raw === null) return [];
        $items = json_decode($raw, true);
        return is_array($items) ? array_values(array_unique($items)) : [];
    }

    /**
     * Tambah produk ke keranjang. Return false jika sudah ada.
     */
    public function addItem(int $userId, int $produkId): bool
    {
        $items = $this->getItems($userId);
        if (in_array($produkId, $items, true)) {
            return false; // sudah ada
        }
        $items[] = $produkId;
        $this->redis->set($this->key($userId), json_encode($items), $this->ttl);
        return true;
    }

    /**
     * Hapus satu produk dari keranjang.
     */
    public function removeItem(int $userId, int $produkId): void
    {
        $items = array_filter($this->getItems($userId), fn($id) => $id !== $produkId);
        $items = array_values($items);
        if (empty($items)) {
            $this->redis->delete($this->key($userId));
        } else {
            $this->redis->set($this->key($userId), json_encode($items), $this->ttl);
        }
    }

    /**
     * Kosongkan seluruh keranjang user.
     */
    public function clear(int $userId): void
    {
        $this->redis->delete($this->key($userId));
    }

    /**
     * Jumlah item di keranjang.
     */
    public function count(int $userId): int
    {
        return count($this->getItems($userId));
    }
}
