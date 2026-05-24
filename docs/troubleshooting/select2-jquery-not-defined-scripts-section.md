# Select2 / jQuery `$ is not defined` — Scripts Section Fix

## Problem

Select2 tidak berjalan dan console browser menampilkan error:

```
Uncaught ReferenceError: $ is not defined
```

Terjadi pada halaman form yang menggunakan Select2 (form Kategori dan form Passing Grade).

## Root Cause

Script di view ditulis di dalam `section('content')`. CodeIgniter 4 merender section ini dan menyisipkannya ke tengah HTML layout — **sebelum** tag `<script>` library (jQuery, Select2) yang ada di bagian bawah `</body>`.

Urutan eksekusi yang salah:
```
1. Layout render section('content')  ← script view dieksekusi di sini
2. ...HTML body...
3. <script> Bootstrap JS
4. <script> jQuery              ← jQuery baru tersedia di sini
5. <script> Select2
```

Karena script view berjalan sebelum jQuery di-load, `$` belum terdefinisi.

## Solution

Tambahkan section `scripts` di layout tepat sebelum `</body>`, setelah semua library JS ter-load. Pindahkan semua `<script>` block dari view ke section ini.

### 1. Layout — tambah `renderSection('scripts')`

```php
<!-- app/Views/layouts/admin.php -->

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // sidebar toggle, dll
</script>

<?= $this->renderSection('scripts') ?>  <!-- ← tambahkan di sini -->

</body>
</html>
```

### 2. View — pisahkan HTML dan script ke section berbeda

```php
<?= $this->section('content') ?>

<!-- HTML form, modal, dll -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>   <!-- ← script dipindah ke sini -->
<script>
(function () {
    $('#my-select').select2({ theme: 'bootstrap-5', ... });
}());
</script>
<?= $this->endSection() ?>
```

Urutan eksekusi yang benar setelah fix:
```
1. Layout render section('content')  ← hanya HTML, tidak ada script
2. Bootstrap JS
3. jQuery
4. DataTables
5. Select2
6. Layout render section('scripts')  ← script view dieksekusi di sini, $ sudah tersedia
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Views/layouts/admin.php` | Tambah `<?= $this->renderSection('scripts') ?>` sebelum `</body>` |
| `app/Views/admin/master/kategori/form.php` | Pindah `<script>` block dari `section('content')` ke `section('scripts')` |
| `app/Views/admin/master/passing-grade/form.php` | Pindah `<script>` block dari `section('content')` ke `section('scripts')` |

## Prevention

Untuk semua view admin yang membutuhkan JavaScript (terutama yang menggunakan jQuery, Select2, DataTables, atau Summernote):

- **Jangan** taruh `<script>` di dalam `section('content')`
- **Selalu** gunakan `section('scripts')` untuk semua JavaScript di view

```php
// ✗ SALAH — script di dalam content
<?= $this->section('content') ?>
<div>...</div>
<script>$('#el').select2();</script>
<?= $this->endSection() ?>

// ✓ BENAR — script di section terpisah
<?= $this->section('content') ?>
<div>...</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>$('#el').select2();</script>
<?= $this->endSection() ?>
```

## Git Commands

```bash
git add app/Views/layouts/admin.php
git add app/Views/admin/master/kategori/form.php
git add app/Views/admin/master/passing-grade/form.php

git commit -m "fix(admin): move view scripts to dedicated section to ensure jQuery/Select2 loaded first"
```
