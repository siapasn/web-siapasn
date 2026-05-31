<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequestFormasiMenu extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $menus = [
            [
                'role'       => 'admin',
                'menu_key'   => 'request_formasi',
                'label'      => 'Request Formasi',
                'icon'       => 'bi-inbox',
                'url'        => '/admin/request-formasi',
                'parent_key' => null,
                'urutan'     => 11,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'super_admin',
                'menu_key'   => 'request_formasi',
                'label'      => 'Request Formasi',
                'icon'       => 'bi-inbox',
                'url'        => '/admin/request-formasi',
                'parent_key' => null,
                'urutan'     => 11,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
        ];

        foreach ($menus as $menu) {
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
            ->where('menu_key', 'request_formasi')
            ->delete();
    }
}
