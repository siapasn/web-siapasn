<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJawabanUserTable extends Migration
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
            'sesi_tryout_id' => [
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
            'jawaban' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => true,
                'default'    => null,
                'comment'    => 'null = tidak dijawab',
            ],
            'is_benar' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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
        $this->forge->addUniqueKey(['sesi_tryout_id', 'soal_id'], 'uq_sesi_soal');
        $this->forge->addForeignKey('sesi_tryout_id', 'sesi_tryout', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('soal_id', 'soal', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('jawaban_user');
    }

    public function down(): void
    {
        $this->forge->dropTable('jawaban_user', true);
    }
}
