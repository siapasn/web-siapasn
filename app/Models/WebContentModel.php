<?php

namespace App\Models;

use CodeIgniter\Model;

class WebContentModel extends Model
{
    protected $table            = 'web_content';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'slug',
        'judul',
        'konten',
        'tipe',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'slug'  => 'required|max_length[100]',
        'judul' => 'required|max_length[200]',
        'tipe'  => 'required|in_list[halaman,teks,angka]',
    ];

    /**
     * Ambil konten berdasarkan slug. Return null jika tidak ditemukan.
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->where('is_active', 1)->first();
    }

    /**
     * Ambil banyak slug sekaligus, return associative array [slug => konten].
     */
    public function getMultiple(array $slugs): array
    {
        $rows = $this->whereIn('slug', $slugs)->where('is_active', 1)->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['slug']] = $row['konten'];
        }
        return $result;
    }
}
