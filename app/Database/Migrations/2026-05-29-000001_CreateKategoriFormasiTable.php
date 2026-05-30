<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKategoriFormasiTable extends Migration
{
    public function up(): void
    {
        // Tabel kategori_formasi — kategori utama formasi CPNS
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
                'comment'    => 'Nama kategori formasi, misal: Teknologi Informasi',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'comment' => 'Deskripsi singkat kategori formasi',
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Bootstrap Icon class, misal: bi-laptop',
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Urutan tampil',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'comment'    => '1=aktif, 0=nonaktif',
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
        $this->forge->createTable('kategori_formasi');

        // Tabel formasi — formasi spesifik di bawah kategori
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori_formasi_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK ke kategori_formasi',
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'Nama formasi, misal: Pranata Komputer',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
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
        $this->forge->addForeignKey('kategori_formasi_id', 'kategori_formasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('formasi');
    }

    public function down(): void
    {
        $this->forge->dropTable('formasi', true);
        $this->forge->dropTable('kategori_formasi', true);
    }
}
