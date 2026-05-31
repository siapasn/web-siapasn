<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShareTokenToHasilTryout extends Migration
{
    public function up(): void
    {
        $fields = $this->db->getFieldNames('hasil_tryout');

        if (! in_array('share_token', $fields)) {
            $this->forge->addColumn('hasil_tryout', [
                'share_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'detail_passing_grade',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $fields = $this->db->getFieldNames('hasil_tryout');
        if (in_array('share_token', $fields)) {
            $this->forge->dropColumn('hasil_tryout', 'share_token');
        }
    }
}
