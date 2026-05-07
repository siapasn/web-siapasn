<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuMappingTable extends Migration
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
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'admin', 'super_admin'],
                'null'       => false,
            ],
            'menu_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'default'    => null,
            ],
            'parent_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'urutan' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'is_visible' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('menu_mapping');
    }

    public function down(): void
    {
        $this->forge->dropTable('menu_mapping', true);
    }
}
