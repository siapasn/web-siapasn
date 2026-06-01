<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionTokenToUsers extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('users');

        if (! in_array('session_token', $fields)) {
            $this->forge->addColumn('users', [
                'session_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'locked_until',
                    'comment'    => 'Token sesi aktif — dipakai untuk single session enforcement',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'session_token');
    }
}
