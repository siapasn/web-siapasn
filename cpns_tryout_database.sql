-- ============================================================
-- CPNS Tryout Online — Database Setup
-- Database: cpns_tryout
-- Generated: 2026-05-03
--
-- Cara import:
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Klik "New" untuk buat database baru bernama: cpns_tryout
-- 3. Pilih database cpns_tryout, klik tab "Import"
-- 4. Pilih file ini, klik "Go"
-- ============================================================

CREATE DATABASE IF NOT EXISTS `cpns_tryout`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `cpns_tryout`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                 INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`               VARCHAR(100)     NOT NULL,
    `email`              VARCHAR(100)     NOT NULL,
    `telepon`            VARCHAR(20)      DEFAULT NULL,
    `password`           VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash',
    `role`               ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
    `is_active`          TINYINT(1)       NOT NULL DEFAULT 1,
    `email_verified_at`  DATETIME         DEFAULT NULL,
    `login_attempts`     INT              NOT NULL DEFAULT 0,
    `locked_until`       DATETIME         DEFAULT NULL,
    `created_at`         DATETIME         DEFAULT NULL,
    `updated_at`         DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. kategori
-- ============================================================
CREATE TABLE IF NOT EXISTS `kategori` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(100)     NOT NULL,
    `parent_id`  INT(10) UNSIGNED DEFAULT NULL COMMENT 'self-referencing FK',
    `created_at` DATETIME         DEFAULT NULL,
    `updated_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_kategori_parent` (`parent_id`),
    CONSTRAINT `fk_kategori_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. master_data_file
-- ============================================================
CREATE TABLE IF NOT EXISTS `master_data_file` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(200)     NOT NULL,
    `path`       VARCHAR(500)     NOT NULL,
    `tipe`       VARCHAR(50)      DEFAULT NULL,
    `ukuran`     INT              DEFAULT NULL COMMENT 'file size in bytes',
    `created_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. soal
-- ============================================================
CREATE TABLE IF NOT EXISTS `soal` (
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `kategori_id`   INT(10) UNSIGNED NOT NULL,
    `pertanyaan`    TEXT             NOT NULL,
    `pilihan_a`     TEXT             NOT NULL,
    `pilihan_b`     TEXT             NOT NULL,
    `pilihan_c`     TEXT             NOT NULL,
    `pilihan_d`     TEXT             NOT NULL,
    `pilihan_e`     TEXT             DEFAULT NULL,
    `kunci_jawaban` CHAR(1)          NOT NULL COMMENT 'a/b/c/d/e',
    `pembahasan`    TEXT             DEFAULT NULL,
    `file_id`       INT(10) UNSIGNED DEFAULT NULL,
    `created_at`    DATETIME         DEFAULT NULL,
    `updated_at`    DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_soal_kategori` (`kategori_id`),
    KEY `idx_soal_file` (`file_id`),
    CONSTRAINT `fk_soal_kategori` FOREIGN KEY (`kategori_id`)
        REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_soal_file` FOREIGN KEY (`file_id`)
        REFERENCES `master_data_file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. tryout
-- ============================================================
CREATE TABLE IF NOT EXISTS `tryout` (
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`        VARCHAR(200)     NOT NULL,
    `durasi`      INT              NOT NULL COMMENT 'duration in minutes',
    `jumlah_soal` INT              NOT NULL,
    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`  DATETIME         DEFAULT NULL,
    `updated_at`  DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. mapping_soal (Soal -> Tryout)
-- ============================================================
CREATE TABLE IF NOT EXISTS `mapping_soal` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tryout_id`  INT(10) UNSIGNED NOT NULL,
    `soal_id`    INT(10) UNSIGNED NOT NULL,
    `urutan`     INT              NOT NULL DEFAULT 0,
    `created_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tryout_soal` (`tryout_id`, `soal_id`),
    KEY `idx_mapping_soal_tryout` (`tryout_id`),
    KEY `idx_mapping_soal_soal` (`soal_id`),
    CONSTRAINT `fk_mapping_soal_tryout` FOREIGN KEY (`tryout_id`)
        REFERENCES `tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mapping_soal_soal` FOREIGN KEY (`soal_id`)
        REFERENCES `soal` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. produk
-- ============================================================
CREATE TABLE IF NOT EXISTS `produk` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(200)     NOT NULL,
    `deskripsi`  TEXT             DEFAULT NULL,
    `harga`      DECIMAL(12,2)    NOT NULL,
    `is_active`  TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at` DATETIME         DEFAULT NULL,
    `updated_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. mapping_tryout (Tryout -> Produk)
-- ============================================================
CREATE TABLE IF NOT EXISTS `mapping_tryout` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `produk_id`  INT(10) UNSIGNED NOT NULL,
    `tryout_id`  INT(10) UNSIGNED NOT NULL,
    `urutan`     INT              NOT NULL DEFAULT 0,
    `created_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_produk_tryout` (`produk_id`, `tryout_id`),
    KEY `idx_mapping_tryout_produk` (`produk_id`),
    KEY `idx_mapping_tryout_tryout` (`tryout_id`),
    CONSTRAINT `fk_mapping_tryout_produk` FOREIGN KEY (`produk_id`)
        REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mapping_tryout_tryout` FOREIGN KEY (`tryout_id`)
        REFERENCES `tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. voucher
-- ============================================================
CREATE TABLE IF NOT EXISTS `voucher` (
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode`             VARCHAR(50)      NOT NULL,
    `jenis_diskon`     ENUM('persentase','nominal') NOT NULL,
    `nilai_diskon`     DECIMAL(12,2)    NOT NULL,
    `batas_penggunaan` INT              DEFAULT NULL,
    `jumlah_digunakan` INT              NOT NULL DEFAULT 0,
    `expired_at`       DATETIME         DEFAULT NULL,
    `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`       DATETIME         DEFAULT NULL,
    `updated_at`       DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_voucher_kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. transaksi
-- ============================================================
CREATE TABLE IF NOT EXISTS `transaksi` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT(10) UNSIGNED NOT NULL,
    `produk_id`         INT(10) UNSIGNED NOT NULL,
    `voucher_id`        INT(10) UNSIGNED DEFAULT NULL,
    `kode_transaksi`    VARCHAR(50)      NOT NULL,
    `harga_asli`        DECIMAL(12,2)    NOT NULL,
    `diskon`            DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
    `harga_bayar`       DECIMAL(12,2)    NOT NULL,
    `status`            ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
    `snap_token`        VARCHAR(255)     DEFAULT NULL,
    `midtrans_order_id` VARCHAR(100)     DEFAULT NULL,
    `expired_at`        DATETIME         DEFAULT NULL,
    `created_at`        DATETIME         DEFAULT NULL,
    `updated_at`        DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_transaksi_kode` (`kode_transaksi`),
    KEY `idx_transaksi_user` (`user_id`),
    KEY `idx_transaksi_produk` (`produk_id`),
    KEY `idx_transaksi_voucher` (`voucher_id`),
    CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_transaksi_produk` FOREIGN KEY (`produk_id`)
        REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_transaksi_voucher` FOREIGN KEY (`voucher_id`)
        REFERENCES `voucher` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. user_produk (akses user ke produk setelah bayar)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_produk` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT(10) UNSIGNED NOT NULL,
    `produk_id`    INT(10) UNSIGNED NOT NULL,
    `transaksi_id` INT(10) UNSIGNED NOT NULL,
    `expired_at`   DATETIME         DEFAULT NULL,
    `created_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_produk` (`user_id`, `produk_id`),
    KEY `idx_user_produk_user` (`user_id`),
    KEY `idx_user_produk_produk` (`produk_id`),
    KEY `idx_user_produk_transaksi` (`transaksi_id`),
    CONSTRAINT `fk_user_produk_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_user_produk_produk` FOREIGN KEY (`produk_id`)
        REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_user_produk_transaksi` FOREIGN KEY (`transaksi_id`)
        REFERENCES `transaksi` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. sesi_tryout
-- ============================================================
CREATE TABLE IF NOT EXISTS `sesi_tryout` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL,
    `tryout_id`  INT(10) UNSIGNED NOT NULL,
    `mulai_at`   DATETIME         NOT NULL,
    `selesai_at` DATETIME         DEFAULT NULL,
    `status`     ENUM('berlangsung','selesai','timeout') NOT NULL DEFAULT 'berlangsung',
    `created_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sesi_tryout_user` (`user_id`),
    KEY `idx_sesi_tryout_tryout` (`tryout_id`),
    CONSTRAINT `fk_sesi_tryout_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_sesi_tryout_tryout` FOREIGN KEY (`tryout_id`)
        REFERENCES `tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. jawaban_user
-- ============================================================
CREATE TABLE IF NOT EXISTS `jawaban_user` (
    `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sesi_tryout_id` INT(10) UNSIGNED NOT NULL,
    `soal_id`        INT(10) UNSIGNED NOT NULL,
    `jawaban`        CHAR(1)          DEFAULT NULL COMMENT 'null = tidak dijawab',
    `is_benar`       TINYINT(1)       DEFAULT NULL,
    `created_at`     DATETIME         DEFAULT NULL,
    `updated_at`     DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sesi_soal` (`sesi_tryout_id`, `soal_id`),
    KEY `idx_jawaban_sesi` (`sesi_tryout_id`),
    KEY `idx_jawaban_soal` (`soal_id`),
    CONSTRAINT `fk_jawaban_sesi` FOREIGN KEY (`sesi_tryout_id`)
        REFERENCES `sesi_tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_jawaban_soal` FOREIGN KEY (`soal_id`)
        REFERENCES `soal` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. hasil_tryout
-- ============================================================
CREATE TABLE IF NOT EXISTS `hasil_tryout` (
    `id`              INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sesi_tryout_id`  INT(10) UNSIGNED NOT NULL,
    `user_id`         INT(10) UNSIGNED NOT NULL,
    `tryout_id`       INT(10) UNSIGNED NOT NULL,
    `skor_total`      DECIMAL(8,2)     NOT NULL,
    `jumlah_benar`    INT              NOT NULL,
    `jumlah_salah`    INT              NOT NULL,
    `jumlah_kosong`   INT              NOT NULL,
    `detail_kategori` JSON             DEFAULT NULL COMMENT 'skor per kategori',
    `peringkat`       INT              DEFAULT NULL,
    `created_at`      DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_hasil_sesi` (`sesi_tryout_id`),
    KEY `idx_hasil_user` (`user_id`),
    KEY `idx_hasil_tryout` (`tryout_id`),
    CONSTRAINT `fk_hasil_sesi` FOREIGN KEY (`sesi_tryout_id`)
        REFERENCES `sesi_tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_hasil_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_hasil_tryout` FOREIGN KEY (`tryout_id`)
        REFERENCES `tryout` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. promosi
-- ============================================================
CREATE TABLE IF NOT EXISTS `promosi` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `produk_id`    INT(10) UNSIGNED NOT NULL,
    `nama`         VARCHAR(200)     NOT NULL,
    `deskripsi`    TEXT             DEFAULT NULL,
    `jenis_diskon` ENUM('persentase','nominal') NOT NULL,
    `nilai_diskon` DECIMAL(12,2)    NOT NULL,
    `mulai_at`     DATETIME         NOT NULL,
    `berakhir_at`  DATETIME         NOT NULL,
    `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`   DATETIME         DEFAULT NULL,
    `updated_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_promosi_produk` (`produk_id`),
    CONSTRAINT `fk_promosi_produk` FOREIGN KEY (`produk_id`)
        REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. passing_grade
-- ============================================================
CREATE TABLE IF NOT EXISTS `passing_grade` (
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tryout_id`     INT(10) UNSIGNED DEFAULT NULL,
    `kategori_id`   INT(10) UNSIGNED DEFAULT NULL,
    `nilai_minimum` DECIMAL(8,2)     NOT NULL,
    `created_at`    DATETIME         DEFAULT NULL,
    `updated_at`    DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pg_tryout` (`tryout_id`),
    KEY `idx_pg_kategori` (`kategori_id`),
    CONSTRAINT `fk_pg_tryout` FOREIGN KEY (`tryout_id`)
        REFERENCES `tryout` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pg_kategori` FOREIGN KEY (`kategori_id`)
        REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. menu_mapping
-- ============================================================
CREATE TABLE IF NOT EXISTS `menu_mapping` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `role`       ENUM('user','admin','super_admin') NOT NULL,
    `menu_key`   VARCHAR(100)     NOT NULL,
    `label`      VARCHAR(100)     NOT NULL,
    `icon`       VARCHAR(100)     DEFAULT NULL,
    `url`        VARCHAR(200)     DEFAULT NULL,
    `parent_key` VARCHAR(100)     DEFAULT NULL,
    `urutan`     INT              NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1)       NOT NULL DEFAULT 1,
    `updated_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. master_aplikasi
-- ============================================================
CREATE TABLE IF NOT EXISTS `master_aplikasi` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `config_key`   VARCHAR(100)     NOT NULL,
    `config_value` TEXT             DEFAULT NULL,
    `updated_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. audit_log
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id`         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED    NOT NULL,
    `aksi`       VARCHAR(200)        NOT NULL,
    `detail`     TEXT                DEFAULT NULL,
    `ip_address` VARCHAR(45)         DEFAULT NULL,
    `created_at` DATETIME            DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user` (`user_id`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 20. password_resets
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(100)     NOT NULL,
    `token`      VARCHAR(255)     NOT NULL,
    `created_at` DATETIME         DEFAULT NULL COMMENT 'used to enforce 60-minute expiry',
    PRIMARY KEY (`id`),
    KEY `idx_pr_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ci4_migrations (tabel tracking migrasi CI4)
-- ============================================================
CREATE TABLE IF NOT EXISTS `migrations` (
    `id`        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `version`   VARCHAR(255)        NOT NULL,
    `class`     TEXT                NOT NULL,
    `group`     VARCHAR(255)        NOT NULL,
    `namespace` VARCHAR(255)        NOT NULL,
    `time`      INT(11)             NOT NULL,
    `batch`     INT(11) UNSIGNED    NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATA AWAL (Seeder)
-- ============================================================

-- Super Admin (password: Admin@1234)
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_active`, `email_verified_at`, `created_at`, `updated_at`)
VALUES (
    'Super Administrator',
    'superadmin@cpns.test',
    '$2y$10$0r5JYOTUh0PT980a8JV3FO6irX3BPc6auFRPhjY3oOd4skjrn43lO', -- password: Admin@1234
    'super_admin',
    1,
    NOW(),
    NOW(),
    NOW()
);

-- Kategori CPNS
INSERT INTO `kategori` (`nama`, `parent_id`, `created_at`, `updated_at`) VALUES
('TWK (Tes Wawasan Kebangsaan)', NULL, NOW(), NOW()),
('TIU (Tes Intelegensia Umum)',  NULL, NOW(), NOW()),
('TKP (Tes Karakteristik Pribadi)', NULL, NOW(), NOW());

-- Sub-kategori TWK (parent_id = 1)
INSERT INTO `kategori` (`nama`, `parent_id`, `created_at`, `updated_at`) VALUES
('Pancasila',          1, NOW(), NOW()),
('UUD 1945',           1, NOW(), NOW()),
('NKRI',               1, NOW(), NOW()),
('Bhineka Tunggal Ika',1, NOW(), NOW());

-- Sub-kategori TIU (parent_id = 2)
INSERT INTO `kategori` (`nama`, `parent_id`, `created_at`, `updated_at`) VALUES
('Verbal',  2, NOW(), NOW()),
('Numerik', 2, NOW(), NOW()),
('Figural', 2, NOW(), NOW());

-- Sub-kategori TKP (parent_id = 3)
INSERT INTO `kategori` (`nama`, `parent_id`, `created_at`, `updated_at`) VALUES
('Pelayanan Publik',    3, NOW(), NOW()),
('Sosial Budaya',       3, NOW(), NOW()),
('Teknologi Informasi', 3, NOW(), NOW()),
('Profesionalisme',     3, NOW(), NOW());

-- Menu Mapping: role user
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
('user', 'dashboard', 'Dashboard',    'bi-house',         '/user/dashboard', NULL, 1, 1, NOW()),
('user', 'produk',    'Paket Tryout', 'bi-box',           '/user/produk',    NULL, 2, 1, NOW()),
('user', 'transaksi', 'Transaksi',    'bi-receipt',       '/user/transaksi', NULL, 3, 1, NOW()),
('user', 'tryout',    'Tryout Saya',  'bi-pencil-square', '/user/tryout',    NULL, 4, 1, NOW());

-- Menu Mapping: role admin
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
('admin', 'dashboard',          'Dashboard',     'bi-speedometer2',          '/admin/dashboard',          NULL,     1, 1, NOW()),
('admin', 'master',             'Master Data',   'bi-database',              '#',                         NULL,     2, 1, NOW()),
('admin', 'master_user',        'Master User',   'bi-people',                '/admin/master/user',        'master', 1, 1, NOW()),
('admin', 'master_kategori',    'Kategori',      'bi-tags',                  '/admin/master/kategori',    'master', 2, 1, NOW()),
('admin', 'master_soal',        'Soal',          'bi-question-circle',       '/admin/master/soal',        'master', 3, 1, NOW()),
('admin', 'master_tryout',      'Tryout',        'bi-journal-text',          '/admin/master/tryout',      'master', 4, 1, NOW()),
('admin', 'master_produk',      'Produk',        'bi-bag',                   '/admin/master/produk',      'master', 5, 1, NOW()),
('admin', 'master_passing_grade','Passing Grade','bi-bar-chart',             '/admin/master/passing-grade','master',6, 1, NOW()),
('admin', 'master_datafile',    'Data File',     'bi-file-earmark',          '/admin/master/datafile',    'master', 7, 1, NOW()),
('admin', 'mapping',            'Mapping',       'bi-diagram-3',             '#',                         NULL,     3, 1, NOW()),
('admin', 'mapping_soal',       'Mapping Soal',  'bi-link',                  '/admin/mapping/soal',       'mapping',1, 1, NOW()),
('admin', 'mapping_tryout',     'Mapping Tryout','bi-link-45deg',            '/admin/mapping/tryout',     'mapping',2, 1, NOW()),
('admin', 'promosi',            'Promosi',       'bi-megaphone',             '/admin/promosi',            NULL,     4, 1, NOW()),
('admin', 'voucher',            'Voucher',       'bi-ticket-perforated',     '/admin/voucher',            NULL,     5, 1, NOW()),
('admin', 'laporan',            'Laporan',       'bi-file-earmark-bar-graph','/admin/laporan/transaksi',  NULL,     6, 1, NOW()),
('admin', 'menu_mapping',       'Menu Mapping',  'bi-layout-sidebar',        '/admin/menu-mapping',       NULL,     7, 1, NOW());

-- Menu Mapping: role super_admin (sama dengan admin + tambahan)
INSERT INTO `menu_mapping` (`role`, `menu_key`, `label`, `icon`, `url`, `parent_key`, `urutan`, `is_visible`, `updated_at`) VALUES
('super_admin', 'dashboard',          'Dashboard',     'bi-speedometer2',          '/admin/dashboard',              NULL,     1, 1, NOW()),
('super_admin', 'master',             'Master Data',   'bi-database',              '#',                             NULL,     2, 1, NOW()),
('super_admin', 'master_user',        'Master User',   'bi-people',                '/admin/master/user',            'master', 1, 1, NOW()),
('super_admin', 'master_kategori',    'Kategori',      'bi-tags',                  '/admin/master/kategori',        'master', 2, 1, NOW()),
('super_admin', 'master_soal',        'Soal',          'bi-question-circle',       '/admin/master/soal',            'master', 3, 1, NOW()),
('super_admin', 'master_tryout',      'Tryout',        'bi-journal-text',          '/admin/master/tryout',          'master', 4, 1, NOW()),
('super_admin', 'master_produk',      'Produk',        'bi-bag',                   '/admin/master/produk',          'master', 5, 1, NOW()),
('super_admin', 'master_passing_grade','Passing Grade','bi-bar-chart',             '/admin/master/passing-grade',   'master', 6, 1, NOW()),
('super_admin', 'master_datafile',    'Data File',     'bi-file-earmark',          '/admin/master/datafile',        'master', 7, 1, NOW()),
('super_admin', 'master_aplikasi',    'Master Aplikasi','bi-gear',                 '/superadmin/master-aplikasi',   'master', 8, 1, NOW()),
('super_admin', 'mapping',            'Mapping',       'bi-diagram-3',             '#',                             NULL,     3, 1, NOW()),
('super_admin', 'mapping_soal',       'Mapping Soal',  'bi-link',                  '/admin/mapping/soal',           'mapping',1, 1, NOW()),
('super_admin', 'mapping_tryout',     'Mapping Tryout','bi-link-45deg',            '/admin/mapping/tryout',         'mapping',2, 1, NOW()),
('super_admin', 'promosi',            'Promosi',       'bi-megaphone',             '/admin/promosi',                NULL,     4, 1, NOW()),
('super_admin', 'voucher',            'Voucher',       'bi-ticket-perforated',     '/admin/voucher',                NULL,     5, 1, NOW()),
('super_admin', 'laporan',            'Laporan',       'bi-file-earmark-bar-graph','/admin/laporan/transaksi',      NULL,     6, 1, NOW()),
('super_admin', 'menu_mapping',       'Menu Mapping',  'bi-layout-sidebar',        '/admin/menu-mapping',           NULL,     7, 1, NOW()),
('super_admin', 'audit_log',          'Audit Log',     'bi-shield-check',          '/superadmin/audit-log',         NULL,     8, 1, NOW()),
('super_admin', 'akun',               'Manajemen Akun','bi-person-gear',           '/superadmin/akun',              NULL,     9, 1, NOW()),
('super_admin', 'backup',             'Backup & Restore','bi-cloud-download',      '/superadmin/backup',            NULL,    10, 1, NOW());
