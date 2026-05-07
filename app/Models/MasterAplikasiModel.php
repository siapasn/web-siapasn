<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MasterAplikasiModel
 *
 * Mengelola konfigurasi aplikasi yang disimpan di tabel master_aplikasi
 * sebagai pasangan key-value.
 *
 * Catatan: method tidak boleh bernama get() atau set() karena konflik
 * dengan CodeIgniter\Model::set() yang punya signature berbeda.
 */
class MasterAplikasiModel extends Model
{
    protected $table            = 'master_aplikasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'config_key',
        'config_value',
    ];

    // Tabel master_aplikasi hanya memiliki updated_at, tidak ada created_at
    protected $useTimestamps = false;
    protected $updatedField  = 'updated_at';

    /**
     * Ambil nilai konfigurasi berdasarkan key.
     * Kembalikan $default jika key tidak ditemukan.
     */
    public function getConfig(string $key, string $default = ''): string
    {
        $row = $this->where('config_key', $key)->first();

        return $row ? (string) $row['config_value'] : $default;
    }

    /**
     * Simpan atau perbarui nilai konfigurasi (upsert).
     */
    public function setConfig(string $key, string $value): void
    {
        $existing = $this->where('config_key', $key)->first();

        if ($existing) {
            $this->update($existing['id'], [
                'config_value' => $value,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->insert([
                'config_key'   => $key,
                'config_value' => $value,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Ambil semua konfigurasi sebagai array asosiatif key => value.
     */
    public function getAll(): array
    {
        $rows   = $this->findAll();
        $result = [];

        foreach ($rows as $row) {
            $result[$row['config_key']] = $row['config_value'];
        }

        return $result;
    }

    /**
     * Simpan beberapa pasangan key-value sekaligus.
     *
     * @param array<string, string> $data Associative array key => value
     */
    public function setMultiple(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setConfig((string) $key, (string) $value);
        }
    }
}
