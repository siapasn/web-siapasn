<?php

namespace App\Models;

use CodeIgniter\Model;

class PassingGradeModel extends Model
{
    protected $table            = 'passing_grade';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kategori_id',
        'sub_kategori_id',
        'nilai_minimum',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua passing grade beserta nama kategori dan sub kategori.
     */
    public function getWithRelations(): array
    {
        return $this->db->table('passing_grade pg')
            ->select('pg.*, k.nama AS nama_kategori, sk.nama AS nama_sub_kategori')
            ->join('kategori k', 'k.id = pg.kategori_id', 'left')
            ->join('kategori sk', 'sk.id = pg.sub_kategori_id', 'left')
            ->orderBy('pg.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Cari passing grade berdasarkan kombinasi kategori_id + sub_kategori_id.
     * sub_kategori_id NULL dianggap "global untuk kategori ini".
     */
    public function findByKategoriAndSub(int $kategoriId, ?int $subKategoriId, ?int $excludeId = null): ?array
    {
        $builder = $this->where('kategori_id', $kategoriId);

        if ($subKategoriId === null) {
            $builder->where('sub_kategori_id IS NULL', null, false);
        } else {
            $builder->where('sub_kategori_id', $subKategoriId);
        }

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        $result = $builder->first();
        return $result ?: null;
    }
}
