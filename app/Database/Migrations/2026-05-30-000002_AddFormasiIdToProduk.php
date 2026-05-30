<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFormasiIdToProduk extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('produk', [
            'formasi_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'kategori_id',
                'comment'    => 'FK ke tabel formasi',
            ],
        ]);

        // Tambah foreign key
        $this->db->query('ALTER TABLE produk ADD CONSTRAINT fk_produk_formasi FOREIGN KEY (formasi_id) REFERENCES formasi(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE produk DROP FOREIGN KEY fk_produk_formasi');
        $this->forge->dropColumn('produk', 'formasi_id');
    }
}
