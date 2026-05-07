# Fix: Undefined Variable $isEdit di section page_header

## Problem

Semua halaman form admin/superadmin menampilkan error:

```
ErrorException: Undefined variable $isEdit
APPPATH\Views\admin\master\{module}\form.php at line 8
```

## Root Cause

CI4 memproses setiap `section()` secara terpisah. Variabel `$isEdit` dideklarasikan di dalam `section('content')` (line ~18), tapi dipakai di `section('page_header')` (line ~8) yang dirender lebih awal.

```php
// ❌ SALAH — $isEdit belum ada saat page_header dirender
<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
    <?= $isEdit ? 'Edit' : 'Tambah' ?>   ← ERROR di sini
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $isEdit = !empty($data); ?>        ← dideklarasikan terlambat
```

## Fix

Deklarasikan `$isEdit` di luar semua section, tepat setelah baris `extend()`:

```php
// ✅ BENAR
<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($data); ?>        ← sebelum semua section

<?= $this->section('page_header') ?>
    <?= $isEdit ? 'Edit' : 'Tambah' ?>   ← tersedia
<?= $this->endSection() ?>

<?= $this->section('content') ?>
```

## Files Fixed (9 files)

| File | Variabel |
|---|---|
| `app/Views/admin/master/kategori/form.php` | `$kategori` |
| `app/Views/admin/master/passing-grade/form.php` | `$passingGrade` |
| `app/Views/admin/master/produk/form.php` | `$produk` |
| `app/Views/admin/master/soal/form.php` | `$soal` |
| `app/Views/admin/master/tryout/form.php` | `$tryout` |
| `app/Views/admin/master/user/form.php` | `$user` |
| `app/Views/admin/promosi/form.php` | `$promosi` |
| `app/Views/admin/voucher/form.php` | `$voucher` |
| `app/Views/superadmin/akun/form.php` | `$user` |

## Prevention

Saat membuat form view baru yang menggunakan `$isEdit` di `page_header`, selalu deklarasikan di luar section:

```php
<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($varData); ?>  ← WAJIB di sini

<?= $this->section('page_header') ?>
...
```
