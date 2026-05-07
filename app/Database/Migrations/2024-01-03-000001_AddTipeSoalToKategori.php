<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipeSoalToKategori extends Migration
{
    public function up(): void
    {
        // Tambah kolom tipe_soal ke tabel kategori (jika belum ada)
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('kategori');

        if (! in_array('tipe_soal', $fields)) {
            $this->forge->addColumn('kategori', [
                'tipe_soal' => [
                    'type'       => 'ENUM',
                    'constraint' => ['SCORE', 'POINT'],
                    'null'       => true,
                    'default'    => null,
                    'comment'    => 'SCORE = nilai 1-5 per pilihan (TKP), POINT = kunci jawaban A-E (TWK, TIU)',
                    'after'      => 'parent_id',
                ],
            ]);
        }

        // Set tipe_soal = SCORE untuk sub-kategori TKP (nilai 1-5 per pilihan)
        $db->query("UPDATE kategori SET tipe_soal = 'SCORE' WHERE nama = 'TKP' AND parent_id IS NOT NULL");

        // Set tipe_soal = POINT untuk sub-kategori TWK dan TIU (kunci jawaban A-E)
        $db->query("UPDATE kategori SET tipe_soal = 'POINT' WHERE nama IN ('TWK', 'TIU') AND parent_id IS NOT NULL AND tipe_soal IS NULL");
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('kategori');

        if (in_array('tipe_soal', $fields)) {
            $this->forge->dropColumn('kategori', 'tipe_soal');
        }
    }
}
