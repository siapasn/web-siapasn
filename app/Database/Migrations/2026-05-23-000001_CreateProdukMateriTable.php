<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProdukMateriTable extends Migration
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
            'produk_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'tipe_file' => [
                'type'       => 'ENUM',
                'constraint' => ['Gambar', 'Video', 'Dokumen'],
                'null'       => false,
            ],
            'url_file' => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('produk_id');
        $this->forge->addForeignKey('produk_id', 'produk', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('produk_materi', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('produk_materi', true);
    }
}
