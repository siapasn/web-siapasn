<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTryoutEventMenu extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $menus = [
            // Admin menu
            [
                'role'       => 'admin',
                'menu_key'   => 'tryout_event',
                'label'      => 'Tryout Event',
                'icon'       => 'bi-calendar-event',
                'url'        => '/admin/tryout-event',
                'parent_key' => null,
                'urutan'     => 10,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            // Super admin menu
            [
                'role'       => 'super_admin',
                'menu_key'   => 'tryout_event',
                'label'      => 'Tryout Event',
                'icon'       => 'bi-calendar-event',
                'url'        => '/admin/tryout-event',
                'parent_key' => null,
                'urutan'     => 10,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            // User menu
            [
                'role'       => 'user',
                'menu_key'   => 'tryout_event',
                'label'      => 'Tryout Event',
                'icon'       => 'bi-calendar-event',
                'url'        => '/user/tryout-event',
                'parent_key' => null,
                'urutan'     => 6,
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
            ->where('menu_key', 'tryout_event')
            ->delete();
    }
}
