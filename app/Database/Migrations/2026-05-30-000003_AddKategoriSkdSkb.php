<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKategoriSkdSkb extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('kategori')->insert([
            'nama'       => 'TRYOUT SKD + SKB CPNS',
            'parent_id'  => null,
            'tipe_soal'  => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $this->db->table('kategori')
            ->where('nama', 'TRYOUT SKD + SKB CPNS')
            ->where('parent_id IS NULL', null, false)
            ->delete();
    }
}
