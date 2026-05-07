<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman utama — arahkan ke login
$routes->get('/', 'Auth\AuthController::index');

// Route autentikasi (publik)
$routes->get('/login', 'Auth\AuthController::login');
$routes->post('/login', 'Auth\AuthController::loginProcess');
$routes->get('/register', 'Auth\AuthController::register');
$routes->post('/register', 'Auth\AuthController::registerProcess');
$routes->get('/logout', 'Auth\AuthController::logout');
$routes->get('/reset-password', 'Auth\AuthController::resetPassword');
$routes->post('/reset-password', 'Auth\AuthController::resetPasswordProcess');
$routes->get('/reset-password/(:alphanum)', 'Auth\AuthController::resetPasswordForm/$1');
$routes->post('/reset-password/update', 'Auth\AuthController::resetPasswordUpdate');
$routes->get('/verify-email/(:alphanum)', 'Auth\AuthController::verifyEmail/$1');

// Route User — dilindungi oleh AuthFilter
$routes->group('user', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'User\DashboardController::index');

    // Katalog produk
    $routes->get('produk', 'User\ProdukController::index');
    $routes->get('produk/(:num)', 'User\ProdukController::show/$1');

    // Transaksi
    $routes->post('transaksi/beli/(:num)', 'User\TransaksiController::beli/$1');
    $routes->get('transaksi/pilih-metode/(:num)', 'User\TransaksiController::pilihMetode/$1');
    $routes->get('transaksi', 'User\TransaksiController::index');
    $routes->get('transaksi/riwayat', 'User\TransaksiController::riwayat');
    $routes->get('transaksi/(:num)', 'User\TransaksiController::show/$1');
    $routes->get('transaksi/(:num)/cek-status', 'User\TransaksiController::cekStatus/$1');

    // Tryout
    $routes->get('tryout', 'User\TryoutController::index');
    $routes->get('tryout/(:num)/mulai', 'User\TryoutController::mulai/$1');
    $routes->post('tryout/(:num)/start', 'User\TryoutController::start/$1');
    $routes->get('tryout/jawab/(:num)', 'User\TryoutController::jawab/$1');
    $routes->post('tryout/simpan-jawaban', 'User\TryoutController::simpanJawaban');
    $routes->post('tryout/selesai/(:num)', 'User\TryoutController::selesai/$1');
    $routes->get('tryout/hasil/(:num)', 'User\TryoutController::hasil/$1');
    $routes->get('tryout/pembahasan/(:num)', 'User\TryoutController::pembahasan/$1');
    // Backward-compat alias
    $routes->get('tryout/sesi/(:num)/soal/(:num)', 'User\TryoutController::soal/$1/$2');

    // Keranjang Belanja
    $routes->get('cart', 'User\CartController::index');
    $routes->post('cart/add', 'User\CartController::add');
    $routes->post('cart/remove', 'User\CartController::remove');
    $routes->post('cart/checkout', 'User\CartController::checkout');
});

// Webhook Midtrans — publik, tidak memerlukan autentikasi
$routes->post('/webhook/midtrans', 'WebhookController::midtrans');

// Cron Jobs — diamankan dengan API key (X-Cron-Key header atau ?key=...)
$routes->get('/cron/sync-payment-status', 'CronController::syncPaymentStatus');

// Route Admin — dilindungi oleh AuthFilter + AdminFilter (admin & super_admin)
$routes->group('admin', ['filter' => ['auth', 'admin_only']], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Master User
    $routes->get('master/user', 'Admin\Master\UserController::index');
    $routes->get('master/user/create', 'Admin\Master\UserController::create');
    $routes->post('master/user/store', 'Admin\Master\UserController::store');
    $routes->get('master/user/(:num)/edit', 'Admin\Master\UserController::edit/$1');
    $routes->post('master/user/(:num)/update', 'Admin\Master\UserController::update/$1');
    $routes->post('master/user/(:num)/delete', 'Admin\Master\UserController::delete/$1');

    // Master Kategori
    $routes->get('master/kategori', 'Admin\Master\KategoriController::index');
    $routes->get('master/kategori/create', 'Admin\Master\KategoriController::create');
    $routes->post('master/kategori/store', 'Admin\Master\KategoriController::store');
    $routes->get('master/kategori/(:num)/edit', 'Admin\Master\KategoriController::edit/$1');
    $routes->post('master/kategori/(:num)/update', 'Admin\Master\KategoriController::update/$1');
    $routes->post('master/kategori/(:num)/delete', 'Admin\Master\KategoriController::delete/$1');

    // Master Data File
    $routes->get('master/datafile', 'Admin\Master\DataFileController::index');
    $routes->post('master/datafile/upload', 'Admin\Master\DataFileController::upload');
    $routes->post('master/datafile/(:num)/delete', 'Admin\Master\DataFileController::delete/$1');

    // Master Soal
    $routes->get('master/soal', 'Admin\Master\SoalController::index');
    $routes->get('master/soal/create', 'Admin\Master\SoalController::create');
    $routes->post('master/soal/store', 'Admin\Master\SoalController::store');
    $routes->get('master/soal/import', 'Admin\Master\SoalController::import');
    $routes->post('master/soal/import', 'Admin\Master\SoalController::importProcess');
    $routes->get('master/soal/template', 'Admin\Master\SoalTemplateController::download');
    $routes->get('master/soal/sub-kategori/(:num)', 'Admin\Master\SoalController::getSubKategori/$1');
    $routes->get('master/soal/(:num)/edit', 'Admin\Master\SoalController::edit/$1');
    $routes->post('master/soal/(:num)/update', 'Admin\Master\SoalController::update/$1');
    $routes->post('master/soal/(:num)/delete', 'Admin\Master\SoalController::delete/$1');

    // Master Tryout
    $routes->get('master/tryout', 'Admin\Master\TryoutController::index');
    $routes->get('master/tryout/create', 'Admin\Master\TryoutController::create');
    $routes->post('master/tryout/store', 'Admin\Master\TryoutController::store');
    $routes->get('master/tryout/(:num)/edit', 'Admin\Master\TryoutController::edit/$1');
    $routes->post('master/tryout/(:num)/update', 'Admin\Master\TryoutController::update/$1');
    $routes->post('master/tryout/(:num)/delete', 'Admin\Master\TryoutController::delete/$1');

    // Master Produk
    $routes->get('master/produk', 'Admin\Master\ProdukController::index');
    $routes->get('master/produk/create', 'Admin\Master\ProdukController::create');
    $routes->post('master/produk/store', 'Admin\Master\ProdukController::store');
    $routes->get('master/produk/(:num)/edit', 'Admin\Master\ProdukController::edit/$1');
    $routes->post('master/produk/(:num)/update', 'Admin\Master\ProdukController::update/$1');
    $routes->post('master/produk/(:num)/delete', 'Admin\Master\ProdukController::delete/$1');

    // Mapping Soal
    $routes->get('mapping/soal', 'Admin\Mapping\MappingSoalController::index');
    $routes->get('mapping/soal/(:num)', 'Admin\Mapping\MappingSoalController::index/$1');
    $routes->post('mapping/soal/store', 'Admin\Mapping\MappingSoalController::store');
    $routes->post('mapping/soal/(:num)/delete', 'Admin\Mapping\MappingSoalController::delete/$1');
    $routes->post('mapping/soal/urutan', 'Admin\Mapping\MappingSoalController::updateUrutan');

    // Mapping Tryout
    $routes->get('mapping/tryout', 'Admin\Mapping\MappingTryoutController::index');
    $routes->get('mapping/tryout/(:num)', 'Admin\Mapping\MappingTryoutController::index/$1');
    $routes->post('mapping/tryout/store', 'Admin\Mapping\MappingTryoutController::store');
    $routes->post('mapping/tryout/(:num)/delete', 'Admin\Mapping\MappingTryoutController::delete/$1');
    $routes->post('mapping/tryout/urutan', 'Admin\Mapping\MappingTryoutController::updateUrutan');

    // Master Passing Grade
    $routes->get('master/passing-grade', 'Admin\Master\PassingGradeController::index');
    $routes->get('master/passing-grade/create', 'Admin\Master\PassingGradeController::create');
    $routes->post('master/passing-grade/store', 'Admin\Master\PassingGradeController::store');
    $routes->get('master/passing-grade/check-duplicate', 'Admin\Master\PassingGradeController::checkDuplicate');
    $routes->get('master/passing-grade/(:num)/edit', 'Admin\Master\PassingGradeController::edit/$1');
    $routes->post('master/passing-grade/(:num)/update', 'Admin\Master\PassingGradeController::update/$1');
    $routes->post('master/passing-grade/(:num)/delete', 'Admin\Master\PassingGradeController::delete/$1');

    // Promosi
    $routes->get('promosi', 'Admin\PromosiController::index');
    $routes->get('promosi/create', 'Admin\PromosiController::create');
    $routes->post('promosi/store', 'Admin\PromosiController::store');
    $routes->get('promosi/(:num)/edit', 'Admin\PromosiController::edit/$1');
    $routes->post('promosi/(:num)/update', 'Admin\PromosiController::update/$1');
    $routes->post('promosi/(:num)/delete', 'Admin\PromosiController::delete/$1');

    // Voucher
    $routes->get('voucher', 'Admin\VoucherController::index');
    $routes->get('voucher/create', 'Admin\VoucherController::create');
    $routes->post('voucher/store', 'Admin\VoucherController::store');
    $routes->get('voucher/(:num)/edit', 'Admin\VoucherController::edit/$1');
    $routes->post('voucher/(:num)/update', 'Admin\VoucherController::update/$1');
    $routes->post('voucher/(:num)/delete', 'Admin\VoucherController::delete/$1');

    // Laporan
    $routes->get('laporan/transaksi', 'Admin\LaporanController::transaksi');
    $routes->get('laporan/tryout', 'Admin\LaporanController::tryout');
    $routes->get('laporan/transaksi/export-excel', 'Admin\LaporanController::exportTransaksiExcel');
    $routes->get('laporan/transaksi/export-pdf', 'Admin\LaporanController::exportTransaksiPdf');

    // Menu Mapping
    $routes->get('menu-mapping', 'Admin\MenuMappingController::index');
    $routes->post('menu-mapping/save', 'Admin\MenuMappingController::save');
    $routes->get('menu-mapping/preview', 'Admin\MenuMappingController::preview');
});

// Route Super Admin — dilindungi oleh AuthFilter + SuperAdminFilter (super_admin saja)
$routes->group('superadmin', ['filter' => ['auth', 'superadmin_only']], function ($routes) {
    // Master Aplikasi
    $routes->get('master-aplikasi', 'SuperAdmin\MasterAplikasiController::index');
    $routes->post('master-aplikasi/save', 'SuperAdmin\MasterAplikasiController::save');

    // Manajemen Akun
    $routes->get('akun', 'SuperAdmin\AkunController::index');
    $routes->get('akun/create', 'SuperAdmin\AkunController::create');
    $routes->post('akun/store', 'SuperAdmin\AkunController::store');
    $routes->get('akun/(:num)/edit', 'SuperAdmin\AkunController::edit/$1');
    $routes->post('akun/(:num)/update', 'SuperAdmin\AkunController::update/$1');
    $routes->post('akun/(:num)/nonaktifkan', 'SuperAdmin\AkunController::nonaktifkan/$1');
    $routes->post('akun/(:num)/aktifkan', 'SuperAdmin\AkunController::aktifkan/$1');
    $routes->post('akun/(:num)/delete', 'SuperAdmin\AkunController::delete/$1');

    // Audit Log
    $routes->get('audit-log', 'SuperAdmin\AuditLogController::index');

    // Backup & Restore
    $routes->get('backup', 'SuperAdmin\BackupController::index');
    $routes->post('backup/create', 'SuperAdmin\BackupController::backup');
    $routes->post('backup/restore', 'SuperAdmin\BackupController::restore');
});
