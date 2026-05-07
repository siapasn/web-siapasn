<?php

namespace App\Models;

use CodeIgniter\Model;

class PromosiModel extends Model
{
    protected $table            = 'promosi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'produk_id',
        'nama',
        'deskripsi',
        'jenis_diskon',
        'nilai_diskon',
        'mulai_at',
        'berakhir_at',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua promosi yang aktif dan belum kedaluwarsa.
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)
                    ->where('berakhir_at >', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Nonaktifkan semua promosi yang sudah kedaluwarsa.
     * Mengembalikan jumlah baris yang diperbarui.
     */
    public function deactivateExpired(): int
    {
        $this->db->table($this->table)
            ->where('berakhir_at <', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->update(['is_active' => 0]);

        return $this->db->affectedRows();
    }

    /**
     * Periksa apakah promosi sudah kedaluwarsa.
     */
    public function isExpired(array $promosi): bool
    {
        return strtotime($promosi['berakhir_at']) < time();
    }
}
