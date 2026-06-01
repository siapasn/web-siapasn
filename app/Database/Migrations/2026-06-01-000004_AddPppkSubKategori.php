<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPppkSubKategori extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // Sub kategori untuk TRYOUT PPPK (parent_id = 3)
        $subKategoris = [
            [
                'nama'      => 'Kompetensi Teknis',
                'parent_id' => 3,
                'tipe_soal' => 'SCORE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'      => 'Kompetensi Manajerial',
                'parent_id' => 3,
                'tipe_soal' => 'POINT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'      => 'Kompetensi Sosial Kultural',
                'parent_id' => 3,
                'tipe_soal' => 'POINT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($subKategoris as $sub) {
            // Hanya insert jika belum ada
            $exists = $this->db->table('kategori')
                ->where('nama', $sub['nama'])
                ->where('parent_id', $sub['parent_id'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('kategori')->insert($sub);
            }
        }

        // Set passing grade untuk sub kategori PPPK
        // Ambil ID sub kategori yang baru dibuat
        $subIds = $this->db->table('kategori')
            ->select('id, nama')
            ->where('parent_id', 3)
            ->get()->getResultArray();

        foreach ($subIds as $sub) {
            // Cek apakah passing grade sudah ada
            $pgExists = $this->db->table('passing_grade')
                ->where('kategori_id', 3)
                ->where('sub_kategori_id', $sub['id'])
                ->countAllResults();

            if ($pgExists === 0) {
                $nilaiMin = 0;
                switch ($sub['nama']) {
                    case 'Kompetensi Teknis':
                        $nilaiMin = 130; // 26 soal benar x 5 poin
                        break;
                    case 'Kompetensi Manajerial':
                        $nilaiMin = 130; // target minimal
                        break;
                    case 'Kompetensi Sosial Kultural':
                        $nilaiMin = 100; // target minimal
                        break;
                }

                $this->db->table('passing_grade')->insert([
                    'kategori_id'     => 3,
                    'sub_kategori_id' => $sub['id'],
                    'nilai_minimum'   => $nilaiMin,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Hapus passing grade PPPK
        $subIds = $this->db->table('kategori')
            ->select('id')
            ->where('parent_id', 3)
            ->get()->getResultArray();

        foreach ($subIds as $sub) {
            $this->db->table('passing_grade')
                ->where('kategori_id', 3)
                ->where('sub_kategori_id', $sub['id'])
                ->delete();
        }

        // Hapus sub kategori PPPK
        $this->db->table('kategori')
            ->where('parent_id', 3)
            ->whereIn('nama', ['Kompetensi Teknis', 'Kompetensi Manajerial', 'Kompetensi Sosial Kultural'])
            ->delete();
    }
}
