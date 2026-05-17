<?php

/**
 * Root index.php — Entry point untuk shared hosting
 *
 * File ini memungkinkan aplikasi berjalan ketika document root
 * mengarah ke folder project (bukan ke folder public/).
 *
 * Cara deploy di shared hosting (Rumah Web / cPanel):
 * - Upload semua file project ke public_html/
 * - File ini akan menjadi entry point utama
 */

// Path ke folder public (web root sebenarnya)
$publicPath = __DIR__ . '/public';

// Pastikan folder public ada
if (! is_dir($publicPath)) {
    http_response_code(500);
    echo 'Folder public tidak ditemukan.';
    exit(1);
}

// Ubah current directory ke public agar semua path relatif benar
chdir($publicPath);

// Jalankan index.php dari folder public
require $publicPath . '/index.php';
