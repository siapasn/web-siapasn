<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;

/**
 * Tambahkan menu "Daftar Formasi" untuk role user.
 */
class AddFormasiMenu extends Seeder
{
    public function run(): void
    {
        $db = $this->db;

        // Ambil urutan maksimum saat ini untuk role user
        $maxUrutan = $db->table('menu_mapping')
            ->where('role', 'user')
            ->selectMax('urutan')
            ->get()->getRowArray();
        $nextUrutan = ($maxUrutan['MAX(urutan)'] ?? 0) + 1;

        // Cek apakah menu sudah ada
        $exists = $db->table('menu_mapping')
            ->where('role', 'user')
            ->where('menu_key', 'formasi')
            ->countAllResults();

        if ($exists > 0) {
            CLI::write('→ Menu formasi untuk role user sudah ada, dilewati.', 'yellow');
            return;
        }

        $db->table('menu_mapping')->insert([
            'role'       => 'user',
            'menu_key'   => 'formasi',
            'label'      => 'Daftar Formasi',
            'icon'       => 'bi-briefcase',
            'url'        => 'user/formasi',
            'parent_key' => null,
            'urutan'     => $nextUrutan,
            'is_visible' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CLI::write("✓ Menu 'Daftar Formasi' ditambahkan (urutan {$nextUrutan}) untuk role user.", 'green');
    }
}
