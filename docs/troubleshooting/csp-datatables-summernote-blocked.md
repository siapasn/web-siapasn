# CSP Memblokir DataTables & Summernote — `tooltip is not a function`

## Problem

Summernote editor tidak muncul dan console browser menampilkan error:

```
Uncaught TypeError: n.attr(...).tooltip is not a function
  at summernote-bs5.min.js
```

Disertai CSP violation:

```
Content-Security-Policy: script-src memblokir
  https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js
```

## Root Cause

Dua masalah yang harus diperbaiki bersamaan:

### 1. `cdn.datatables.net` tidak ada dalam whitelist CSP

`BaseController.php` tidak meng-whitelist `cdn.datatables.net` di `script-src`. Akibatnya jQuery DataTables diblokir → jQuery plugin chain terganggu → Summernote gagal init.

### 2. jQuery di-load setelah Bootstrap JS

Urutan load JS yang salah di `layouts/admin.php`:

```
❌ SALAH:
Bootstrap JS  ← butuh jQuery tapi jQuery belum ada
jQuery
DataTables
Summernote    ← $.fn.tooltip tidak tersedia → error
```

`summernote-bs5` menggunakan `$.fn.tooltip` dari Bootstrap yang di-wrap jQuery. Jika jQuery belum ada saat Bootstrap di-load, tooltip tidak terdaftar sebagai jQuery plugin.

## Solution

### Fix 1 — Tambah `cdn.datatables.net` ke CSP (`BaseController.php`)

```php
"script-src 'self' 'unsafe-inline' 'unsafe-eval'"
    . " https://cdn.jsdelivr.net"
    . " https://code.jquery.com"
    . " https://cdn.datatables.net"   // ← tambahkan
    . " ...",

"style-src 'self' 'unsafe-inline'"
    . " https://cdn.jsdelivr.net"
    . " https://cdn.datatables.net"   // ← tambahkan
    . " ...",

"font-src 'self' data:"
    . " https://cdn.jsdelivr.net"
    . " https://cdn.datatables.net"   // ← tambahkan
    . " ...",

"img-src 'self' data: blob:"
    . " https://cdn.datatables.net"   // ← tambahkan (DataTables sort icons)
    . " ...",
```

### Fix 2 — Pindah jQuery ke posisi pertama (`layouts/admin.php`)

```html
✅ BENAR — urutan yang wajib diikuti:

<!-- 1. jQuery — HARUS pertama -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- 2. Bootstrap — butuh jQuery untuk tooltip/modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 3. DataTables — butuh jQuery -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- 4. Chart.js — standalone -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- 5. Summernote — butuh jQuery + Bootstrap tooltip -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<!-- 6. Select2 — butuh jQuery -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- 7. section('scripts') — kode view, semua library sudah tersedia -->
<?= $this->renderSection('scripts') ?>
```

### Fix 3 — Ganti `summernote-bs5` ke `summernote-lite`

`summernote-bs5` (versi 0.8.20 maupun 0.9.0) bergantung pada `$.fn.tooltip` dari Bootstrap yang di-wrap jQuery. Bootstrap 5.3 tidak lagi menyediakan jQuery wrapper untuk tooltip, sehingga selalu error.

**Solusi permanen:** gunakan `summernote-lite` yang standalone dan tidak bergantung Bootstrap tooltip.

```html
<!-- CSS — ganti bs5 ke lite -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">

<!-- JS — ganti bs5 ke lite -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
```

Inisialisasi tetap sama, hanya hapus `lang: 'id-ID'` (lite build tidak include locale):

```js
// ✅ BENAR untuk summernote-lite
$('#editor').summernote({
    tabsize: 2,
    height: 220,
    toolbar: [ ... ]
});

// ❌ JANGAN — lite tidak include locale id-ID
$('#editor').summernote({ lang: 'id-ID', ... });
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/BaseController.php` | Tambah `cdn.datatables.net` ke `script-src`, `style-src`, `font-src`, `img-src` |
| `app/Views/layouts/admin.php` | Pindah jQuery ke posisi pertama; ganti `summernote-bs5` → `summernote-lite` |
| `app/Views/admin/master/soal/form.php` | Hapus `lang: 'id-ID'` dari Summernote config |
| `app/Views/admin/master/produk/form.php` | Hapus `lang: 'id-ID'` dari Summernote config |
| `app/Views/admin/konten/form.php` | Hapus `lang: 'id-ID'` dari Summernote config |

## Prevention

### Aturan urutan JS (wajib diikuti)
```
jQuery → Bootstrap → jQuery plugins (DataTables, Summernote, Select2) → standalone libs
```

### Checklist tambah CDN baru
```
[ ] Tambah <link> atau <script> di layouts/admin.php
[ ] Perhatikan urutan load (jQuery harus pertama)
[ ] Tambah domain ke script-src (jika JS)
[ ] Tambah domain ke style-src (jika CSS)
[ ] Tambah domain ke font-src (jika ada font)
[ ] Tambah domain ke img-src (jika ada gambar/icon)
[ ] Hard refresh browser (Ctrl+Shift+R) untuk clear CSP cache
```

## Git Commands

```bash
git add app/Controllers/BaseController.php
git add app/Views/layouts/admin.php
git add app/Views/admin/master/soal/form.php
git add app/Views/admin/master/produk/form.php
git add app/Views/admin/konten/form.php

git commit -m "fix(summernote): switch to summernote-lite, fix CSP and jQuery load order"
```
