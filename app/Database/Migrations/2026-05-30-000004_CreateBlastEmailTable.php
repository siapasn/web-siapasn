<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlastEmailTable extends Migration
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
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'body' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['all', 'single'],
                'default'    => 'all',
                'comment'    => 'all=semua user, single=1 user tertentu',
            ],
            'target_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'ID user target jika tipe=single',
            ],
            'target_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Email target (untuk referensi)',
            ],
            'total_sent' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'total_failed' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'sent_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID admin yang mengirim',
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
        $this->forge->createTable('blast_email');
    }

    public function down(): void
    {
        $this->forge->dropTable('blast_email', true);
    }
}
