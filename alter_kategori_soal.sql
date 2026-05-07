-- Jalankan di phpMyAdmin → database cpns_tryout → tab SQL

-- 1. Tambah kolom tipe_soal di tabel kategori (hanya untuk sub-kategori)
ALTER TABLE `kategori`
    ADD COLUMN `tipe_soal` ENUM('SCORE', 'POINT') NULL DEFAULT NULL
    COMMENT 'Tipe penilaian: SCORE=pilihan ganda kunci jawaban, POINT=nilai per pilihan. Hanya untuk sub-kategori.'
    AFTER `parent_id`;

-- 2. Tambah kolom nilai_a sampai nilai_e di tabel soal (untuk tipe POINT)
ALTER TABLE `soal`
    ADD COLUMN `nilai_a` TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Nilai pilihan A (1-5, tipe POINT)' AFTER `pilihan_e`,
    ADD COLUMN `nilai_b` TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Nilai pilihan B (1-5, tipe POINT)' AFTER `nilai_a`,
    ADD COLUMN `nilai_c` TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Nilai pilihan C (1-5, tipe POINT)' AFTER `nilai_b`,
    ADD COLUMN `nilai_d` TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Nilai pilihan D (1-5, tipe POINT)' AFTER `nilai_c`,
    ADD COLUMN `nilai_e` TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Nilai pilihan E (1-5, tipe POINT)' AFTER `nilai_d`;

-- 3. Ubah kunci_jawaban menjadi nullable (tidak wajib untuk tipe POINT)
ALTER TABLE `soal`
    MODIFY COLUMN `kunci_jawaban` CHAR(1) NULL DEFAULT NULL COMMENT 'a/b/c/d/e — null untuk tipe POINT';
