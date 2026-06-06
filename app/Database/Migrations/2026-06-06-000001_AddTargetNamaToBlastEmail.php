<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetNamaToBlastEmail extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('blast_email', [
            'target_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'target_email',
                'comment'    => 'Nama penerima email',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('blast_email', 'target_nama');
    }
}
