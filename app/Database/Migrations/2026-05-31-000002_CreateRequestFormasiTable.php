<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRequestFormasiTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'formasi_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'pesan' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'comment' => 'Pesan/catatan dari user',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
            ],
            'admin_note' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'handled_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'handled_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'notified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'comment' => 'Waktu email notifikasi dikirim',
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('formasi_id', 'formasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('request_formasi');
    }

    public function down(): void
    {
        $this->forge->dropTable('request_formasi', true);
    }
}
