<?php

namespace App\Models;

use CodeIgniter\Model;

class KatalogBukuModel extends Model
{
    protected $table            = 'katalog_buku';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode',
        'judul',
        'isbn',
        'pengarang',
        'penerbit',
        'harga',
        'url_thumbnail',
        'url_shopee',
        'is_active',
        'is_highlight',
        'urutan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua katalog buku aktif, urut berdasarkan urutan.
     * Buku highlight ditampilkan terlebih dahulu.
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('is_highlight', 'DESC')
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil buku yang di-highlight (aktif + highlight), urut berdasarkan urutan.
     */
    public function getHighlighted(): array
    {
        return $this->where('is_active', 1)
                    ->where('is_highlight', 1)
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
