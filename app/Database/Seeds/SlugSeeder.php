<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;

/**
 * SlugSeeder
 * Script sekali pakai — tambah kolom slug dan isi dengan data dari nama.
 * Setelah migration AddSlugToTables berjalan, seeder ini tidak perlu lagi.
 */
class SlugSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->db;

        // ===== Tambah kolom slug jika belum ada =====
        foreach (['tryout_event', 'tryout', 'produk'] as $tbl) {
            $cols = $db->getFieldNames($tbl);
            if (! in_array('slug', $cols)) {
                $db->query("ALTER TABLE `{$tbl}` ADD COLUMN `slug` VARCHAR(255) NULL AFTER `nama`");
                CLI::write("✓ Kolom slug ditambahkan ke {$tbl}", 'green');
            } else {
                CLI::write("→ Kolom slug sudah ada di {$tbl}", 'yellow');
            }
        }

        // ===== Tambah UNIQUE INDEX jika belum ada =====
        foreach (['tryout_event', 'tryout', 'produk'] as $tbl) {
            // Cek apakah unique index sudah ada
            $indexes = $db->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = 'slug'")->getResultArray();
            if (empty($indexes)) {
                $db->query("ALTER TABLE `{$tbl}` ADD UNIQUE INDEX `{$tbl}_slug_unique` (`slug`)");
                CLI::write("✓ Unique index slug ditambahkan ke {$tbl}", 'green');
            }
        }

        // ===== Isi slug untuk data yang sudah ada =====
        foreach (['tryout_event', 'tryout', 'produk'] as $tbl) {
            $rows    = $db->table($tbl)->get()->getResultArray();
            $updated = 0;

            foreach ($rows as $row) {
                if (! empty($row['slug'])) {
                    continue;
                }

                $slug = $this->makeUniqueSlug($db, $tbl, $row['nama'], (int) $row['id']);
                $db->table($tbl)->where('id', $row['id'])->update(['slug' => $slug]);
                $updated++;
            }

            CLI::write("✓ {$tbl}: {$updated} baris diisi slug", 'green');
        }

        CLI::write("\nSelesai! Semua slug berhasil dibuat.", 'green');
    }

    private function makeSlug(string $text): string
    {
        $slug = strtolower($text);
        $map  = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c', '&' => 'dan',
        ];
        $slug = strtr($slug, $map);
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', trim($slug));

        return $slug ?: 'item';
    }

    private function makeUniqueSlug($db, string $table, string $nama, int $excludeId): string
    {
        $base  = $this->makeSlug($nama);
        $slug  = $base;
        $count = 1;

        while (true) {
            $n = $db->table($table)
                ->where('slug', $slug)
                ->where('id !=', $excludeId)
                ->countAllResults();

            if ($n === 0) {
                break;
            }

            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
