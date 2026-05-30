<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRankingMenu extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $exists = $this->db->table('menu_mapping')
            ->where('role', 'user')
            ->where('menu_key', 'ranking')
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('menu_mapping')->insert([
                'role'       => 'user',
                'menu_key'   => 'ranking',
                'label'      => 'Perangkingan',
                'icon'       => 'bi-trophy',
                'url'        => '/user/ranking',
                'parent_key' => null,
                'urutan'     => 5,
                'is_visible' => 1,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('menu_mapping')
            ->where('role', 'user')
            ->where('menu_key', 'ranking')
            ->delete();
    }
}
