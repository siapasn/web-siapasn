<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubKategoriToSoal extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('soal', [
            'sub_kategori_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'kategori_id',
            ],
        ]);
        // Add FK
        $this->db->query('ALTER TABLE soal ADD CONSTRAINT fk_soal_sub_kategori FOREIGN KEY (sub_kategori_id) REFERENCES kategori(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE soal DROP FOREIGN KEY fk_soal_sub_kategori');
        $this->forge->dropColumn('soal', 'sub_kategori_id');
    }
}
