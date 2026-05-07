<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSoalTable extends Migration
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
            'kategori_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'pertanyaan' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pilihan_a' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pilihan_b' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pilihan_c' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pilihan_d' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pilihan_e' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
            'kunci_jawaban' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => false,
                'comment'    => 'a/b/c/d/e',
            ],
            'pembahasan' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
            'file_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
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
        $this->forge->addForeignKey('kategori_id', 'kategori', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('file_id', 'master_data_file', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('soal');
    }

    public function down(): void
    {
        $this->forge->dropTable('soal', true);
    }
}
