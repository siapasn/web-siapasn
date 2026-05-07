<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubKategoriToPassingGrade extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('passing_grade');

        if (! in_array('sub_kategori_id', $fields)) {
            $this->forge->addColumn('passing_grade', [
                'sub_kategori_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'kategori_id',
                ],
            ]);
            $this->db->query('ALTER TABLE passing_grade ADD CONSTRAINT fk_pg_sub_kategori FOREIGN KEY (sub_kategori_id) REFERENCES kategori(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    public function down(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('passing_grade');

        if (in_array('sub_kategori_id', $fields)) {
            $this->db->query('ALTER TABLE passing_grade DROP FOREIGN KEY fk_pg_sub_kategori');
            $this->forge->dropColumn('passing_grade', 'sub_kategori_id');
        }
    }
}
