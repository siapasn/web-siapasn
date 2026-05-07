<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Insert parent categories first
        $parents = [
            ['nama' => 'TWK (Tes Wawasan Kebangsaan)', 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'TIU (Tes Intelegensia Umum)',   'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'TKP (Tes Karakteristik Pribadi)', 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($parents as $parent) {
            $this->db->table('kategori')->insert($parent);
        }

        // Retrieve inserted parent IDs
        $twkId  = $this->db->table('kategori')->where('nama', 'TWK (Tes Wawasan Kebangsaan)')->get()->getRow()->id;
        $tiuId  = $this->db->table('kategori')->where('nama', 'TIU (Tes Intelegensia Umum)')->get()->getRow()->id;
        $tkpId  = $this->db->table('kategori')->where('nama', 'TKP (Tes Karakteristik Pribadi)')->get()->getRow()->id;

        // Sub-categories for TWK
        $twkChildren = [
            ['nama' => 'Pancasila',          'parent_id' => $twkId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'UUD 1945',           'parent_id' => $twkId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'NKRI',               'parent_id' => $twkId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Bhineka Tunggal Ika', 'parent_id' => $twkId, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Sub-categories for TIU
        $tiuChildren = [
            ['nama' => 'Verbal',   'parent_id' => $tiuId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Numerik',  'parent_id' => $tiuId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Figural',  'parent_id' => $tiuId, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Sub-categories for TKP
        $tkpChildren = [
            ['nama' => 'Pelayanan Publik',    'parent_id' => $tkpId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Sosial Budaya',       'parent_id' => $tkpId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Teknologi Informasi', 'parent_id' => $tkpId, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Profesionalisme',     'parent_id' => $tkpId, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('kategori')->insertBatch($twkChildren);
        $this->db->table('kategori')->insertBatch($tiuChildren);
        $this->db->table('kategori')->insertBatch($tkpChildren);
    }
}
