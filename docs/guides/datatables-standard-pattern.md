# Panduan Standar DataTables di Admin Views

## Overview

Semua halaman admin/superadmin yang menampilkan tabel data menggunakan pola standar yang sama:
- Script DataTables di `section('scripts')` — bukan inline di `section('content')`
- `dom` dengan padding konsisten untuk Show, Search, info, dan pagination
- CSS scoped per tabel agar tidak ada konflik antar halaman

---

## Pola Standar

### 1. CSS — di dalam card, sebelum `table-responsive`

```html
<div class="card border-0 shadow-sm">
    <style>
        #tabelXxx_wrapper .dataTables_length label,
        #tabelXxx_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelXxx_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelXxx_wrapper .dataTables_info,
        #tabelXxx_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelXxx_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelXxx" class="table table-hover align-middle mb-0" style="width:100%">
                ...
            </table>
        </div>
    </div>
</div>
```

### 2. Script — di `section('scripts')`, SETELAH `endSection()` content

```php
<?= $this->endSection() ?>   <!-- tutup section content dulu -->

<?= $this->section('scripts') ?>
<script>
$('#tabelXxx').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
    },
    pageLength: 25,
    ordering: true,
    order: [],
    columnDefs: [
        { orderable: false, targets: [-1] },   // kolom Aksi tidak bisa di-sort
    ],
    dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
});
</script>
<?= $this->endSection() ?>
```

### 3. Jika data bisa kosong — inisialisasi kondisional (WAJIB)

DataTables akan error "Incorrect column count" jika tabel berisi baris `<td colspan="X">` sebagai empty state. **Jangan pernah taruh empty state di dalam `<tbody>` tabel DataTables.**

Pola yang benar — pisahkan tabel dan empty state:

```php
<div class="card border-0 shadow-sm">
    <?php if (! empty($items)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelXxx" style="width:100%">
                <thead>...</thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                        <tr>...</tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty state — DataTables TIDAK dirender -->
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada data</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="...">menambah data baru</a></div>
    </div>
    <?php endif; ?>
</div>
```

Dan di section('scripts'):

```php
<?= $this->section('scripts') ?>
<script>
<?php if (! empty($items)): ?>
$('#tabelXxx').DataTable({
    ...
});
<?php endif; ?>
</script>
<?= $this->endSection() ?>
```

---

## Kenapa `section('scripts')` bukan inline?

Script yang ditulis di dalam `section('content')` dirender ke tengah HTML — **sebelum** jQuery, DataTables, dan Select2 di-load di bagian bawah layout. Akibatnya `$` belum terdefinisi saat script dieksekusi.

```
❌ SALAH — script di section('content'):
  Layout render section('content')  ← script dieksekusi, $ belum ada
  ...
  <script src="jquery.min.js">      ← jQuery baru tersedia di sini

✅ BENAR — script di section('scripts'):
  <script src="jquery.min.js">
  <script src="datatables.min.js">
  <script src="select2.min.js">
  <?= $this->renderSection('scripts') ?>  ← semua library sudah tersedia
```

---

## Files Updated (2026-05-21)

| File | Table ID |
|---|---|
| `admin/konten/index.php` | `tabelKonten` |
| `admin/laporan/transaksi.php` | `tabelTransaksi` |
| `admin/laporan/tryout.php` | `tabelTryout` |
| `admin/promosi/index.php` | `tabelPromosi` |
| `admin/voucher/index.php` | `tabelVoucher` |
| `admin/master/kategori/index.php` | `tabelKategori` |
| `admin/master/produk/index.php` | `tabelProduk` |
| `admin/master/soal/index.php` | `tabelSoal` |
| `admin/master/tryout/index.php` | `tabelTryout` |
| `admin/master/user/index.php` | `tabelUser` |
| `admin/master/passing-grade/index.php` | `tabelPassingGrade` |
| `admin/master/datafile/index.php` | `tabelDataFile` |
| `superadmin/akun/index.php` | `tabelAkun` |
| `superadmin/audit-log/index.php` | `tabelAuditLog` |

---

## Files Excluded (intentional)

| File | Alasan |
|---|---|
| `admin/mapping/soal/index.php` | Tabel statis + SortableJS, bukan DataTables |
| `admin/mapping/tryout/index.php` | Tabel statis + SortableJS, bukan DataTables |
| `superadmin/backup/index.php` | Tabel statis, tidak perlu search/pagination |
| `admin/laporan/pdf_transaksi.php` | Template PDF print, bukan halaman interaktif |

---

## Checklist Membuat Halaman Baru dengan Tabel

```
[ ] Beri id unik pada <table> (contoh: tabelNamaHalaman)
[ ] Tambah style="width:100%" pada <table>
[ ] Tambah CSS scoped di dalam card sebelum table-responsive
[ ] Inisialisasi DataTables di section('scripts') setelah endSection()
[ ] Tambah dom dengan padding standar
[ ] Jika data bisa kosong: gunakan kondisional + empty state card
[ ] Kolom Aksi: tambah orderable: false di columnDefs
```
