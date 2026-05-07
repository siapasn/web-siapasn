<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTryoutTable extends Migration
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
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'durasi' => [
                'type'    => 'INT',
                'null'    => false,
                'comment' => 'duration in minutes',
            ],
            'jumlah_soal' => [
                'type' => 'INT',
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

        $this->forge->createTable('tryout');
    }

    public function down(): void
    {
        $this->forge->dropTable('tryout', true);
    }
}
