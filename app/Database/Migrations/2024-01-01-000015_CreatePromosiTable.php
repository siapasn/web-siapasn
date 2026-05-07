<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromosiTable extends Migration
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
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'jenis_diskon' => [
                'type'       => 'ENUM',
                'constraint' => ['persentase', 'nominal'],
                'null'       => false,
            ],
            'nilai_diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'mulai_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'berakhir_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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
        $this->forge->addForeignKey('produk_id', 'produk', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('promosi');
    }

    public function down(): void
    {
        $this->forge->dropTable('promosi', true);
    }
}
