-- ============================================================
-- Reset dan isi ulang tabel menu_mapping
-- Jalankan di phpMyAdmin: pilih database cpns_tryout, tab SQL
-- ============================================================

USE `cpns_tryout`;

TRUNCATE TABLE `menu_mapping`;

-- ============================================================
-- ROLE: user
-- ============================================================
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
('user', 'dashboard', 'Dashboard',    'bi-house',         'user/dashboard', NULL, 1, 1, NOW()),
('user', 'produk',    'Paket Tryout', 'bi-box',           'user/produk',    NULL, 2, 1, NOW()),
('user', 'transaksi', 'Transaksi',    'bi-receipt',       'user/transaksi', NULL, 3, 1, NOW()),
('user', 'tryout',    'Tryout Saya',  'bi-pencil-square', 'user/tryout',    NULL, 4, 1, NOW());

-- ============================================================
-- ROLE: admin
-- ============================================================
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
-- Top level
('admin', 'dashboard',   'Dashboard',   'bi-speedometer2', 'admin/dashboard', NULL, 1, 1, NOW()),
('admin', 'master',      'Master Data', 'bi-database',     '#',               NULL, 2, 1, NOW()),
('admin', 'mapping',     'Mapping',     'bi-diagram-3',    '#',               NULL, 3, 1, NOW()),
('admin', 'promosi',     'Promosi',     'bi-megaphone',    'admin/promosi',   NULL, 4, 1, NOW()),
('admin', 'voucher',     'Voucher',     'bi-ticket-perforated', 'admin/voucher', NULL, 5, 1, NOW()),
('admin', 'laporan',     'Laporan',     'bi-file-earmark-bar-graph', 'admin/laporan/transaksi', NULL, 6, 1, NOW()),
('admin', 'menu_mapping','Menu Mapping','bi-layout-sidebar','admin/menu-mapping', NULL, 7, 1, NOW()),

-- Sub-menu Master Data
('admin', 'master_user',         'Master User',   'bi-people',          'admin/master/user',          'master', 1, 1, NOW()),
('admin', 'master_kategori',     'Kategori',      'bi-tags',            'admin/master/kategori',      'master', 2, 1, NOW()),
('admin', 'master_soal',         'Soal',          'bi-question-circle', 'admin/master/soal',          'master', 3, 1, NOW()),
('admin', 'master_tryout',       'Tryout',        'bi-journal-text',    'admin/master/tryout',        'master', 4, 1, NOW()),
('admin', 'master_produk',       'Produk',        'bi-bag',             'admin/master/produk',        'master', 5, 1, NOW()),
('admin', 'master_passing_grade','Passing Grade', 'bi-bar-chart',       'admin/master/passing-grade', 'master', 6, 1, NOW()),
('admin', 'master_datafile',     'Data File',     'bi-file-earmark',    'admin/master/datafile',      'master', 7, 1, NOW()),

-- Sub-menu Mapping
('admin', 'mapping_soal',   'Mapping Soal',   'bi-link',       'admin/mapping/soal',   'mapping', 1, 1, NOW()),
('admin', 'mapping_tryout', 'Mapping Tryout', 'bi-link-45deg', 'admin/mapping/tryout', 'mapping', 2, 1, NOW());

-- ============================================================
-- ROLE: super_admin (sama dengan admin + tambahan)
-- ============================================================
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
-- Top level
('super_admin', 'dashboard',   'Dashboard',   'bi-speedometer2', 'admin/dashboard', NULL, 1, 1, NOW()),
('super_admin', 'master',      'Master Data', 'bi-database',     '#',               NULL, 2, 1, NOW()),
('super_admin', 'mapping',     'Mapping',     'bi-diagram-3',    '#',               NULL, 3, 1, NOW()),
('super_admin', 'promosi',     'Promosi',     'bi-megaphone',    'admin/promosi',   NULL, 4, 1, NOW()),
('super_admin', 'voucher',     'Voucher',     'bi-ticket-perforated', 'admin/voucher', NULL, 5, 1, NOW()),
('super_admin', 'laporan',     'Laporan',     'bi-file-earmark-bar-graph', 'admin/laporan/transaksi', NULL, 6, 1, NOW()),
('super_admin', 'menu_mapping','Menu Mapping','bi-layout-sidebar','admin/menu-mapping', NULL, 7, 1, NOW()),
('super_admin', 'audit_log',   'Audit Log',   'bi-shield-check', 'superadmin/audit-log', NULL, 8, 1, NOW()),
('super_admin', 'akun',        'Manajemen Akun','bi-person-gear','superadmin/akun',  NULL, 9, 1, NOW()),
('super_admin', 'backup',      'Backup & Restore','bi-cloud-download','superadmin/backup', NULL, 10, 1, NOW()),

-- Sub-menu Master Data
('super_admin', 'master_user',         'Master User',    'bi-people',          'admin/master/user',           'master', 1, 1, NOW()),
('super_admin', 'master_kategori',     'Kategori',       'bi-tags',            'admin/master/kategori',       'master', 2, 1, NOW()),
('super_admin', 'master_soal',         'Soal',           'bi-question-circle', 'admin/master/soal',           'master', 3, 1, NOW()),
('super_admin', 'master_tryout',       'Tryout',         'bi-journal-text',    'admin/master/tryout',         'master', 4, 1, NOW()),
('super_admin', 'master_produk',       'Produk',         'bi-bag',             'admin/master/produk',         'master', 5, 1, NOW()),
('super_admin', 'master_passing_grade','Passing Grade',  'bi-bar-chart',       'admin/master/passing-grade',  'master', 6, 1, NOW()),
('super_admin', 'master_datafile',     'Data File',      'bi-file-earmark',    'admin/master/datafile',       'master', 7, 1, NOW()),
('super_admin', 'master_aplikasi',     'Master Aplikasi','bi-gear',            'superadmin/master-aplikasi',  'master', 8, 1, NOW()),

-- Sub-menu Mapping
('super_admin', 'mapping_soal',   'Mapping Soal',   'bi-link',       'admin/mapping/soal',   'mapping', 1, 1, NOW()),
('super_admin', 'mapping_tryout', 'Mapping Tryout', 'bi-link-45deg', 'admin/mapping/tryout', 'mapping', 2, 1, NOW());
