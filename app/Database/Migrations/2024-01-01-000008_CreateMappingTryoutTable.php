<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMappingTryoutTable extends Migration
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
            'produk_id' => [
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
            'urutan' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['produk_id', 'tryout_id'], 'uq_produk_tryout');
        $this->forge->addForeignKey('produk_id', 'produk', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('mapping_tryout');
    }

    public function down(): void
    {
        $this->forge->dropTable('mapping_tryout', true);
    }
}
