<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetTipeSoalForSkb extends Migration
{
    public function up(): void
    {
        // Set tipe_soal SCORE untuk kategori yang belum punya tipe_soal
        // SKB CPNS (id=2), TRYOUT PPPK (id=3), TRYOUT SKD + SKB (id=12)
        $this->db->table('kategori')
            ->where('tipe_soal IS NULL', null, false)
            ->whereIn('id', [2, 3, 12])
            ->update(['tipe_soal' => 'SCORE']);
    }

    public function down(): void
    {
        $this->db->table('kategori')
            ->whereIn('id', [2, 3, 12])
            ->update(['tipe_soal' => null]);
    }
}
