<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWebContentMenu extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $menus = [
            [
                'role'       => 'admin',
                'menu_key'   => 'web_content',
                'label'      => 'Web Content',
                'icon'       => 'bi-file-richtext',
                'url'        => '/admin/konten',
                'parent_key' => null,
                'urutan'     => 8,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'super_admin',
                'menu_key'   => 'web_content',
                'label'      => 'Web Content',
                'icon'       => 'bi-file-richtext',
                'url'        => '/admin/konten',
                'parent_key' => null,
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
            ->whereIn('menu_key', ['web_content'])
            ->delete();
    }
}
