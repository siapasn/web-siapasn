<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePassingGradeTable extends Migration
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
                'null'       => true,
                'default'    => null,
            ],
            'kategori_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'nilai_minimum' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => false,
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
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('kategori_id', 'kategori', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('passing_grade');
    }

    public function down(): void
    {
        $this->forge->dropTable('passing_grade', true);
    }
}
