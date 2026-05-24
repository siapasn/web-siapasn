<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table            = 'kategori';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'parent_id',
        'tipe_soal',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua kategori beserta nama parent-nya (self-join).
     */
    public function getWithParent(): array
    {
        return $this->db->table('kategori k')
            ->select('k.*, p.nama AS parent_nama')
            ->join('kategori p', 'p.id = k.parent_id', 'left')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil semua kategori induk (parent_id IS NULL).
     */
    public function getParents(): array
    {
        return $this->where('parent_id IS NULL', null, false)->findAll();
    }

    /**
     * Ambil semua kategori (induk maupun sub-kategori) beserta nama parent-nya.
     * Digunakan untuk dropdown yang menampilkan semua pilihan.
     */
    public function getAll(): array
    {
        return $this->db->query('
            SELECT k.id, k.nama, k.parent_id, k.tipe_soal, p.nama AS parent_nama
            FROM kategori k
            LEFT JOIN kategori p ON p.id = k.parent_id
            ORDER BY COALESCE(k.parent_id, k.id) ASC, k.parent_id IS NULL DESC, k.nama ASC
        ')->getResultArray();
    }

    /**
     * Ambil semua sub-kategori dari parent tertentu.
     */
    public function getChildren(int $parentId): array
    {
        return $this->where('parent_id', $parentId)->findAll();
    }
}
