<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHasilTryoutTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'skor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => false,
            ],
            'jumlah_benar' => [
                'type' => 'INT',
                'null' => false,
            ],
            'jumlah_salah' => [
                'type' => 'INT',
                'null' => false,
            ],
            'jumlah_kosong' => [
                'type' => 'INT',
                'null' => false,
            ],
            'detail_kategori' => [
                'type'    => 'JSON',
                'null'    => true,
                'default' => null,
                'comment' => 'skor per kategori',
            ],
            'peringkat' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sesi_tryout_id');
        $this->forge->addForeignKey('sesi_tryout_id', 'sesi_tryout', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('hasil_tryout');
    }

    public function down(): void
    {
        $this->forge->dropTable('hasil_tryout', true);
    }
}
