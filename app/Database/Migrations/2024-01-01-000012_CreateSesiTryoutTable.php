<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSesiTryoutTable extends Migration
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
            'tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'mulai_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'selesai_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['berlangsung', 'selesai', 'timeout'],
                'default'    => 'berlangsung',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('tryout_id', 'tryout', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('sesi_tryout');
    }

    public function down(): void
    {
        $this->forge->dropTable('sesi_tryout', true);
    }
}
