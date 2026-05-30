<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKategoriFormasiMenu extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $menus = [
            [
                'role'       => 'admin',
                'menu_key'   => 'master_kategori_formasi',
                'label'      => 'Kategori Formasi',
                'icon'       => 'bi-briefcase',
                'url'        => '/admin/master/kategori-formasi',
                'parent_key' => 'master',
                'urutan'     => 8,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'super_admin',
                'menu_key'   => 'master_kategori_formasi',
                'label'      => 'Kategori Formasi',
                'icon'       => 'bi-briefcase',
                'url'        => '/admin/master/kategori-formasi',
                'parent_key' => 'master',
                'urutan'     => 8,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
        ];

        foreach ($menus as $menu) {
            // Hanya insert jika belum ada
            $exists = $this->db->table('menu_mapping')
                ->where('role', $menu['role'])
                ->where('menu_key', $menu['menu_key'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('menu_mapping')->insert($menu);
            }
        }
    }

    public function down(): void
    {
        $this->db->table('menu_mapping')
            ->whereIn('menu_key', ['master_kategori_formasi'])
            ->delete();
    }
}
