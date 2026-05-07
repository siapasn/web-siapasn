<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuMappingSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // -------------------------------------------------------
        // Role: user
        // -------------------------------------------------------
        $userMenus = [
            [
                'role'       => 'user',
                'menu_key'   => 'dashboard',
                'label'      => 'Dashboard',
                'icon'       => 'bi-house',
                'url'        => '/user/dashboard',
                'parent_key' => null,
                'urutan'     => 1,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'user',
                'menu_key'   => 'produk',
                'label'      => 'Paket Tryout',
                'icon'       => 'bi-box',
                'url'        => '/user/produk',
                'parent_key' => null,
                'urutan'     => 2,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'user',
                'menu_key'   => 'transaksi',
                'label'      => 'Transaksi',
                'icon'       => 'bi-receipt',
                'url'        => '/user/transaksi',
                'parent_key' => null,
                'urutan'     => 3,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'user',
                'menu_key'   => 'tryout',
                'label'      => 'Tryout Saya',
                'icon'       => 'bi-pencil-square',
                'url'        => '/user/tryout',
                'parent_key' => null,
                'urutan'     => 4,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
        ];

        // -------------------------------------------------------
        // Role: admin (base menus shared with super_admin)
        // -------------------------------------------------------
        $adminMenus = [
            [
                'role'       => 'admin',
                'menu_key'   => 'dashboard',
                'label'      => 'Dashboard',
                'icon'       => 'bi-speedometer2',
                'url'        => '/admin/dashboard',
                'parent_key' => null,
                'urutan'     => 1,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master',
                'label'      => 'Master Data',
                'icon'       => 'bi-database',
                'url'        => '#',
                'parent_key' => null,
                'urutan'     => 2,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_user',
                'label'      => 'Master User',
                'icon'       => 'bi-people',
                'url'        => '/admin/master/user',
                'parent_key' => 'master',
                'urutan'     => 1,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_kategori',
                'label'      => 'Kategori',
                'icon'       => 'bi-tags',
                'url'        => '/admin/master/kategori',
                'parent_key' => 'master',
                'urutan'     => 2,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_soal',
                'label'      => 'Soal',
                'icon'       => 'bi-question-circle',
                'url'        => '/admin/master/soal',
                'parent_key' => 'master',
                'urutan'     => 3,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_tryout',
                'label'      => 'Tryout',
                'icon'       => 'bi-journal-text',
                'url'        => '/admin/master/tryout',
                'parent_key' => 'master',
                'urutan'     => 4,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_produk',
                'label'      => 'Produk',
                'icon'       => 'bi-bag',
                'url'        => '/admin/master/produk',
                'parent_key' => 'master',
                'urutan'     => 5,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_passing_grade',
                'label'      => 'Passing Grade',
                'icon'       => 'bi-bar-chart',
                'url'        => '/admin/master/passing-grade',
                'parent_key' => 'master',
                'urutan'     => 6,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'master_datafile',
                'label'      => 'Data File',
                'icon'       => 'bi-file-earmark',
                'url'        => '/admin/master/datafile',
                'parent_key' => 'master',
                'urutan'     => 7,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'mapping',
                'label'      => 'Mapping',
                'icon'       => 'bi-diagram-3',
                'url'        => '#',
                'parent_key' => null,
                'urutan'     => 3,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'mapping_soal',
                'label'      => 'Mapping Soal',
                'icon'       => 'bi-link',
                'url'        => '/admin/mapping/soal',
                'parent_key' => 'mapping',
                'urutan'     => 1,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'mapping_tryout',
                'label'      => 'Mapping Tryout',
                'icon'       => 'bi-link-45deg',
                'url'        => '/admin/mapping/tryout',
                'parent_key' => 'mapping',
                'urutan'     => 2,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'promosi',
                'label'      => 'Promosi',
                'icon'       => 'bi-megaphone',
                'url'        => '/admin/promosi',
                'parent_key' => null,
                'urutan'     => 4,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'voucher',
                'label'      => 'Voucher',
                'icon'       => 'bi-ticket-perforated',
                'url'        => '/admin/voucher',
                'parent_key' => null,
                'urutan'     => 5,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'laporan',
                'label'      => 'Laporan',
                'icon'       => 'bi-file-earmark-bar-graph',
                'url'        => '/admin/laporan',
                'parent_key' => null,
                'urutan'     => 6,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
            [
                'role'       => 'admin',
                'menu_key'   => 'menu_mapping',
                'label'      => 'Menu Mapping',
                'icon'       => 'bi-layout-sidebar',
                'url'        => '/admin/menu-mapping',
                'parent_key' => null,
                'urutan'     => 7,
                'is_visible' => 1,
                'updated_at' => $now,
            ],
        ];

        // -------------------------------------------------------
        // Role: super_admin — same as admin + extra items
        // -------------------------------------------------------
        $superAdminMenus = [];

        // Clone admin menus for super_admin role
        foreach ($adminMenus as $menu) {
            $superAdminMenus[] = array_merge($menu, ['role' => 'super_admin']);
        }

        // Additional super_admin-only items
        $superAdminMenus[] = [
            'role'       => 'super_admin',
            'menu_key'   => 'master_aplikasi',
            'label'      => 'Master Aplikasi',
            'icon'       => 'bi-gear',
            'url'        => '/superadmin/master-aplikasi',
            'parent_key' => 'master',
            'urutan'     => 8,
            'is_visible' => 1,
            'updated_at' => $now,
        ];

        $superAdminMenus[] = [
            'role'       => 'super_admin',
            'menu_key'   => 'audit_log',
            'label'      => 'Audit Log',
            'icon'       => 'bi-shield-check',
            'url'        => '/superadmin/audit-log',
            'parent_key' => null,
            'urutan'     => 8,
            'is_visible' => 1,
            'updated_at' => $now,
        ];

        // Insert all menus
        $this->db->table('menu_mapping')->insertBatch($userMenus);
        $this->db->table('menu_mapping')->insertBatch($adminMenus);
        $this->db->table('menu_mapping')->insertBatch($superAdminMenus);
    }
}
