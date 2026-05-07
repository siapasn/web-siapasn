<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuMappingModel extends Model
{
    protected $table            = 'menu_mapping';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'role',
        'menu_key',
        'label',
        'icon',
        'url',
        'parent_key',
        'urutan',
        'is_visible',
    ];

    // Tabel menu_mapping hanya memiliki updated_at, tidak ada created_at
    protected $useTimestamps = false;
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua menu untuk role tertentu, diurutkan berdasarkan urutan.
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil menu top-level (parent_key IS NULL) untuk role tertentu.
     */
    public function getTopLevel(string $role): array
    {
        return $this->where('role', $role)
                    ->where('parent_key IS NULL', null, false)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil sub-menu (children) berdasarkan parent_key untuk role tertentu.
     */
    public function getChildren(string $role, string $parentKey): array
    {
        return $this->where('role', $role)
                    ->where('parent_key', $parentKey)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }
}
