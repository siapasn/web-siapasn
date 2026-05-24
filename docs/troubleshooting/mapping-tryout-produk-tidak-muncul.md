# Mapping Tryout — Data Produk Tidak Muncul di Dropdown

## Problem

Halaman `/admin/mapping/tryout` tidak menampilkan data produk di dropdown "Pilih Produk", meskipun data produk sudah ada di master.

## Root Cause

Query untuk mengambil tryout tersedia menggunakan kolom `jumlah_soal` langsung dari tabel `tryout`:

```php
// ❌ SALAH — kolom jumlah_soal sudah dihapus dari tabel tryout
$builder = $db->table('tryout')
    ->select('id, nama, durasi, jumlah_soal')  // kolom ini tidak ada
    ->orderBy('id', 'ASC');
```

Kolom `jumlah_soal` sudah dihapus dari tabel `tryout` (dihitung dinamis dari `mapping_soal`). Query ini menyebabkan error database yang membuat halaman tidak render dengan benar, sehingga dropdown produk pun tidak muncul.

## Solution

Ganti query tryout untuk menghitung `jumlah_soal` secara dinamis via `COUNT` dari tabel `mapping_soal`:

```php
// ✅ BENAR — hitung jumlah soal dari mapping_soal
$builder = $db->table('tryout t')
    ->select('t.id, t.nama, t.durasi, COUNT(ms.soal_id) AS jumlah_soal')
    ->join('mapping_soal ms', 'ms.tryout_id = t.id', 'left')
    ->groupBy('t.id')
    ->orderBy('t.nama', 'ASC');
```

Sekaligus query produk diganti ke raw DB builder agar lebih eksplisit:

```php
// Sebelum — bisa terpengaruh scope model
$produks = $this->produkModel->findAll();

// Sesudah — eksplisit, tidak ada scope tersembunyi
$produks = $db->table('produk')
    ->select('id, nama')
    ->orderBy('nama', 'ASC')
    ->get()->getResultArray();
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/Admin/Mapping/MappingTryoutController.php` | Fix query tryout (COUNT dari mapping_soal), query produk via raw DB builder |
| `app/Views/admin/mapping/tryout/index.php` | Select2 untuk dropdown produk, script ke `section('scripts')` |

## Prevention

Setiap kali menghapus kolom dari tabel, cari semua referensi kolom tersebut di seluruh codebase:

```bash
# Cari semua penggunaan kolom jumlah_soal
grep -r "jumlah_soal" app/
```

Untuk kolom yang dihitung (derived), selalu gunakan subquery atau JOIN + COUNT daripada menyimpan nilai di kolom.

## Git Commands

```bash
git add app/Controllers/Admin/Mapping/MappingTryoutController.php
git add app/Views/admin/mapping/tryout/index.php

git commit -m "fix(mapping-tryout): fix missing produk data, add Select2 for produk selector, use COUNT for jumlah_soal"
```
