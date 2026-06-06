<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;

/**
 * Tandai migration AddSlugToTables sebagai sudah dijalankan,
 * karena slug sudah dibuat via SlugSeeder.
 */
class MarkSlugMigration extends Seeder
{
    public function run(): void
    {
        $db = $this->db;

        // Cek batch terakhir
        $lastBatch = $db->table('migrations')
            ->selectMax('batch')
            ->get()->getRowArray();
        $batch = ($lastBatch['MAX(batch)'] ?? 0) + 1;

        // Cek apakah sudah ada entri untuk migration ini
        $exists = $db->table('migrations')
            ->where('class', 'AddSlugToTables')
            ->countAllResults();

        if ($exists === 0) {
            $db->table('migrations')->insert([
                'version'   => '2026-06-06-000002',
                'class'     => 'AddSlugToTables',
                'group'     => 'default',
                'namespace' => 'App',
                'time'      => time(),
                'batch'     => $batch,
            ]);
            CLI::write('✓ Migration AddSlugToTables ditandai sebagai sudah dijalankan (batch ' . $batch . ')', 'green');
        } else {
            CLI::write('→ Migration AddSlugToTables sudah terdaftar sebelumnya', 'yellow');
        }

        CLI::write('Selesai.', 'green');
    }
}
