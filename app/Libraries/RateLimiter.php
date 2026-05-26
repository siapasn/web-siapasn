<?php

namespace App\Libraries;

/**
 * File-based Rate Limiter
 * 
 * Menggunakan file cache di writable/cache/ratelimit/ untuk tracking
 * jumlah request per IP address.
 */
class RateLimiter
{
    protected string $cachePath;

    public function __construct()
    {
        $this->cachePath = WRITEPATH . 'cache/ratelimit/';

        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Cek apakah request masih diperbolehkan
     *
     * @param string $key         Identifier unik (misal: login_md5ip)
     * @param int    $maxAttempts Jumlah maksimal percobaan
     * @param int    $decayMinutes Waktu reset dalam menit
     * @return bool true jika masih diperbolehkan, false jika sudah melebihi limit
     */
    public function check(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $data = $this->getData($key);

        if ($data === null) {
            return true;
        }

        // Cek apakah sudah expired
        if (time() > $data['expires_at']) {
            $this->reset($key);
            return true;
        }

        return $data['attempts'] < $maxAttempts;
    }

    /**
     * Tambah jumlah percobaan
     *
     * @param string $key          Identifier unik
     * @param int    $decayMinutes Waktu reset dalam menit
     */
    public function increment(string $key, int $decayMinutes): void
    {
        $data = $this->getData($key);

        if ($data === null || time() > $data['expires_at']) {
            // Buat entry baru
            $data = [
                'attempts'   => 1,
                'expires_at' => time() + ($decayMinutes * 60),
            ];
        } else {
            $data['attempts']++;
        }

        $this->saveData($key, $data);
    }

    /**
     * Reset counter untuk key tertentu
     */
    public function reset(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Dapatkan sisa waktu (dalam detik) sebelum rate limit expired
     */
    public function getRetryAfter(string $key): int
    {
        $data = $this->getData($key);

        if ($data === null) {
            return 0;
        }

        $remaining = $data['expires_at'] - time();
        return max(0, $remaining);
    }

    /**
     * Baca data dari file cache
     */
    protected function getData(string $key): ?array
    {
        $file = $this->getFilePath($key);

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $data    = json_decode($content, true);

        if (! is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Simpan data ke file cache
     */
    protected function saveData(string $key, array $data): void
    {
        $file = $this->getFilePath($key);
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Dapatkan path file berdasarkan key
     */
    protected function getFilePath(string $key): string
    {
        return $this->cachePath . md5($key) . '.json';
    }
}
