<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterAplikasiTable extends Migration
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
            'config_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'config_value' => [
                'type'    => 'TEXT',
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
        $this->forge->addUniqueKey('config_key');

        $this->forge->createTable('master_aplikasi');
    }

    public function down(): void
    {
        $this->forge->dropTable('master_aplikasi', true);
    }
}
