<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMappingSoalTable extends Migration
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
            'tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'soal_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'urutan' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tryout_id', 'soal_id'], 'uq_tryout_soal');
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('soal_id', 'soal', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('mapping_soal');
    }

    public function down(): void
    {
        $this->forge->dropTable('mapping_soal', true);
    }
}
