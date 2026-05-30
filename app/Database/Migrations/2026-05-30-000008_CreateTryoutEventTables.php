<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTryoutEventTables extends Migration
{
    public function up(): void
    {
        // Tabel tryout_event — event tryout nasional/gratis
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK ke tryout yang digunakan',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'banner_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
            ],
            'mulai_pendaftaran' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'tutup_pendaftaran' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'mulai_pelaksanaan' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'tutup_pelaksanaan' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'max_percobaan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 1,
                'comment'    => 'Jumlah maksimal percobaan per peserta',
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
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tryout_event');

        // Tabel tryout_event_peserta — peserta yang mendaftar event
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'event_id' => [
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
            'registered_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['registered', 'started', 'completed'],
                'default'    => 'registered',
            ],
            'sesi_tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'FK ke sesi_tryout jika sudah mulai',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['event_id', 'user_id'], 'uq_event_user');
        $this->forge->addForeignKey('event_id', 'tryout_event', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tryout_event_peserta');
    }

    public function down(): void
    {
        $this->forge->dropTable('tryout_event_peserta', true);
        $this->forge->dropTable('tryout_event', true);
    }
}
