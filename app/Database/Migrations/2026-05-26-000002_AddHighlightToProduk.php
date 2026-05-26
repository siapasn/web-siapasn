<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHighlightToProduk extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('produk');

        if (! in_array('is_highlight', $fields)) {
            $this->forge->addColumn('produk', [
                'is_highlight' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'is_active',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('produk', 'is_highlight');
    }
}
