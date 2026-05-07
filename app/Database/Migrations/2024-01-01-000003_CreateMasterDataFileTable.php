<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterDataFileTable extends Migration
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
            'path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'tipe' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'ukuran' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
                'comment' => 'file size in bytes',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('master_data_file');
    }

    public function down(): void
    {
        $this->forge->dropTable('master_data_file', true);
    }
}
