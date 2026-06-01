<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToBlastEmail extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('blast_email');

        if (! in_array('status', $fields)) {
            $this->forge->addColumn('blast_email', [
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'processing', 'done', 'failed'],
                    'default'    => 'pending',
                    'null'       => false,
                    'after'      => 'sent_by',
                    'comment'    => 'pending=antrian, processing=sedang kirim, done=selesai, failed=gagal',
                ],
            ]);
        }

        // Ubah ENUM tipe agar support subscribe dan subscribe_single
        $db->query("ALTER TABLE blast_email MODIFY COLUMN tipe ENUM('all','single','subscribe','subscribe_single') DEFAULT 'all'");
    }

    public function down(): void
    {
        $this->forge->dropColumn('blast_email', 'status');
    }
}
