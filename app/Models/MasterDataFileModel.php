<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterDataFileModel extends Model
{
    protected $table            = 'master_data_file';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama',
        'path',
        'tipe',
        'ukuran',
    ];

    // Tabel hanya memiliki created_at, tidak ada updated_at
    protected $useTimestamps = false;

    /**
     * Saat insert, isi created_at secara manual.
     */
    public function insertFile(array $data): int|string
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data, true);
    }
}
