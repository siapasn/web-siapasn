<?php
/**
 * Fallback entry point — hanya dipakai jika mod_rewrite tidak aktif.
 * Normalnya root .htaccess yang handle routing ke public/index.php.
 */
$publicPath = __DIR__ . '/public';

if (! is_dir($publicPath)) {
    http_response_code(500);
    echo 'Folder public tidak ditemukan.';
    exit(1);
}

chdir($publicPath);
require $publicPath . '/index.php';
