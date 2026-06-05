<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitorsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'comment'    => 'IPv4 atau IPv6 pengunjung',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'page_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Halaman pertama yang dikunjungi hari itu',
            ],
            'visited_at' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Tanggal kunjungan (1 record per IP per hari)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('visited_at', false, false, 'idx_visited_at');
        $this->forge->addUniqueKey(['ip_address', 'visited_at'], 'unique_visitor_per_day');
        $this->forge->createTable('visitors');
    }

    public function down(): void
    {
        $this->forge->dropTable('visitors', true);
    }
}
