<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHighlightToKatalogBuku extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('katalog_buku', [
            'is_highlight' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_active',
                'comment'    => 'Jika 1, buku ditampilkan di dashboard user',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('katalog_buku', 'is_highlight');
    }
}
