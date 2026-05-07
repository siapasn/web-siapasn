<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThumbnailToProduk extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('produk');

        if (! in_array('thumbnail', $fields)) {
            $this->forge->addColumn('produk', [
                'thumbnail' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'deskripsi',
                    'comment'    => 'Nama file thumbnail, disimpan di public/uploads/produk/',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('produk');

        if (in_array('thumbnail', $fields)) {
            $this->forge->dropColumn('produk', 'thumbnail');
        }
    }
}
