<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKatalogBukuTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori_buku_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'url_thumbnail' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'url_shopee' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'urutan' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kategori_buku_id', 'kategori_buku', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('katalog_buku');
    }

    public function down(): void
    {
        $this->forge->dropTable('katalog_buku', true);
    }
}
