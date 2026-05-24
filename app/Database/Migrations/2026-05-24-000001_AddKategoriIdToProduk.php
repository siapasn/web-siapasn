<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKategoriIdToProduk extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('produk');

        if (! in_array('kategori_id', $fields)) {
            $this->forge->addColumn('produk', [
                'kategori_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'nama',
                    'comment'    => 'Kategori produk (CPNS, SEKDIN, PPPK, BUMN) — FK ke tabel kategori',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('produk');

        if (in_array('kategori_id', $fields)) {
            $this->forge->dropColumn('produk', 'kategori_id');
        }
    }
}
