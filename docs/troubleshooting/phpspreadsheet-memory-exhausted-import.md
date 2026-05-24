# PhpSpreadsheet Memory Exhausted saat Download Template Import

## Problem

Fatal error saat mengakses `/admin/master/soal/import` (download template):

```
Fatal error: Allowed memory size of 536870912 bytes exhausted
(tried to allocate 33554432 bytes) in
vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Worksheet/Worksheet.php on line 1287
```

## Root Cause

`SoalTemplateController` menggunakan PhpSpreadsheet untuk generate file `.xlsx` dengan banyak styling (fill, border, font, alignment per cell). PhpSpreadsheet sangat memory-intensive — setiap cell dengan style membutuhkan alokasi objek PHP yang besar. Dengan ratusan baris referensi kategori + styling, memory 512MB pun tidak cukup.

## Solution

Ganti output template dari **XLSX via PhpSpreadsheet** ke **CSV via `fputcsv()`** native PHP.

CSV tidak membutuhkan library eksternal, tidak ada styling, dan memory usage sangat minimal (stream langsung ke output).

```php
// ❌ SEBELUM — PhpSpreadsheet XLSX (memory exhausted)
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->getStyle('A1')->applyFromArray([...]); // boros memory
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// ✅ SESUDAH — CSV native PHP (ringan)
header('Content-Type: text/csv; charset=UTF-8');
echo "\xEF\xBB\xBF"; // BOM untuk Excel UTF-8
$out = fopen('php://output', 'w');
fputcsv($out, ['header1', 'header2', ...]);
fputcsv($out, ['data1', 'data2', ...]);
fclose($out);
exit;
```

## Format Perubahan Import

Sekaligus format kolom diperbarui — **kategori menggunakan ID** (bukan nama) untuk menghindari ambiguitas:

### Format Baru (CSV, 15 kolom)

| Kolom | Field | Keterangan |
|---|---|---|
| A | `kategori_id` * | ID integer dari tabel kategori |
| B | `pertanyaan` * | Teks pertanyaan |
| C–F | `pilihan_a–d` * | Pilihan A, B, C, D |
| G | `pilihan_e` | Pilihan E (opsional) |
| H | `kunci_jawaban` | **POINT**: huruf a/b/c/d/e |
| H–L | `nilai_a–e` | **SCORE**: angka 1–5, semua berbeda |
| M/I | `pembahasan` | Teks pembahasan (opsional) |
| O | `tryout_id` | ID tryout (opsional — auto-mapping) |

Tipe soal (POINT/SCORE) dideteksi otomatis dari `tipe_soal` di tabel `kategori` berdasarkan `kategori_id`.

### Keunggulan Format Baru

- **Tidak ambigu** — ID unik, tidak ada masalah nama kategori yang mirip
- **Kolom tryout_id** — soal langsung di-mapping ke tryout saat import
- **Template berisi referensi** — daftar ID kategori dan tryout tersedia di bawah template

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/Admin/Master/SoalTemplateController.php` | Ditulis ulang — output CSV via `fputcsv()`, tanpa PhpSpreadsheet |
| `app/Controllers/Admin/Master/SoalController.php` | `importProcess()` hanya terima CSV; lookup via ID; tambah kolom tryout_id; `validateImportRow()` dan `buildImportRow()` diperbarui |
| `app/Views/admin/master/soal/import.php` | Panduan format diperbarui, input file hanya `.csv` |

## Prevention

Untuk fitur download file di masa depan:
- Gunakan `fputcsv()` untuk data tabular sederhana
- Gunakan PhpSpreadsheet **hanya** jika benar-benar butuh formatting Excel (chart, formula, multi-sheet dengan style)
- Jika harus pakai PhpSpreadsheet, set `ini_set('memory_limit', '256M')` di awal method dan hindari styling per-cell massal

## Git Commands

```bash
git add app/Controllers/Admin/Master/SoalController.php
git add app/Controllers/Admin/Master/SoalTemplateController.php
git add app/Views/admin/master/soal/import.php

git commit -m "fix(soal): rewrite template as CSV to fix memory exhausted, use kategori_id instead of name"
```
