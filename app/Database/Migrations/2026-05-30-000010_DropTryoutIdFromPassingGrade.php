<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropTryoutIdFromPassingGrade extends Migration
{
    public function up(): void
    {
        // Cari nama foreign key untuk tryout_id
        $fkName = null;
        $result = $this->db->query("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'passing_grade'
              AND COLUMN_NAME = 'tryout_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $row = $result->getRowArray();
        if ($row) {
            $fkName = $row['CONSTRAINT_NAME'];
            $this->db->query("ALTER TABLE passing_grade DROP FOREIGN KEY `{$fkName}`");
        }

        // Hapus kolom
        $this->forge->dropColumn('passing_grade', 'tryout_id');
    }

    public function down(): void
    {
        $this->forge->addColumn('passing_grade', [
            'tryout_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('ALTER TABLE passing_grade ADD CONSTRAINT passing_grade_tryout_id_foreign FOREIGN KEY (tryout_id) REFERENCES tryout(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }
}
