# Import Soal Format Baru & Fitur Salin Soal Antar Tryout

## Overview

Dua perubahan besar pada modul Master Soal:

1. **Format import diperbarui** — kolom `nama_sub_kategori` dihapus, tipe soal dideteksi langsung dari `nama_kategori`
2. **Fitur Salin Soal** — salin semua soal yang sudah di-mapping dari satu tryout ke tryout lain

---

## 1. Format Import Baru

### Perubahan Kolom

**Sheet POINT (kunci jawaban):**

| Kolom | Format Lama | Format Baru |
|---|---|---|
| A | nama_kategori | nama_kategori |
| B | ~~nama_sub_kategori~~ | **pertanyaan** |
| C | pertanyaan | pilihan_a |
| D–G | pilihan_a–d | pilihan_b–d |
| H | pilihan_e | pilihan_e |
| I | kunci_jawaban | **kunci_jawaban** |
| J | pembahasan | pembahasan |

**Sheet SCORE (nilai per pilihan):**

| Kolom | Format Lama | Format Baru |
|---|---|---|
| A | nama_kategori | nama_kategori |
| B | ~~nama_sub_kategori~~ | **pertanyaan** |
| C | pertanyaan | pilihan_a |
| D–G | pilihan_a–d | pilihan_b–d |
| H | pilihan_e | pilihan_e |
| I–M | nilai_a–e | **nilai_a–e** |
| N | pembahasan | **M** = pembahasan |

### Deteksi Tipe Soal

Tipe soal (POINT/SCORE) dideteksi dari kolom `tipe_soal` di tabel `kategori` berdasarkan `nama_kategori` di kolom A. Fallback: jika kategori tidak punya tipe, sistem cek isi kolom H — huruf a/b/c/d/e → POINT, angka 1–5 → SCORE.

### Lookup Kategori

Sebelumnya hanya mencari di kategori induk (`parent_id IS NULL`). Sekarang mencari di **semua kategori** (induk maupun sub), sehingga bisa langsung tulis nama seperti `TWK`, `TIU`, `TKP`.

---

## 2. Fitur Salin Soal Antar Tryout

### Cara Kerja

1. Buka halaman **Master Soal** → klik tombol **Salin Soal**
2. Pilih **Tryout Sumber** (yang soalnya akan disalin)
3. Pilih **Tryout Tujuan** (yang akan menerima soal)
4. Klik **Salin Soal Sekarang** → konfirmasi
5. Sistem menyalin semua `mapping_soal` dari sumber ke tujuan
6. Soal yang sudah ada di tujuan dilewati (tidak duplikat)
7. Urutan soal dilanjutkan dari urutan terakhir di tujuan

### Validasi

- Tryout sumber dan tujuan tidak boleh sama
- Tryout sumber harus memiliki soal
- Duplikat soal di tujuan dilewati otomatis

### Hasil

Setelah proses, sistem menampilkan:
- Jumlah soal yang berhasil disalin
- Jumlah soal yang dilewati (sudah ada di tujuan)

---

## Files Modified / Created

| File | Perubahan |
|---|---|
| `app/Controllers/Admin/Master/SoalController.php` | Update `importProcess()`, `validateImportRow()`, `buildImportRow()`; tambah `salinSoal()`, `salinSoalProcess()` |
| `app/Controllers/Admin/Master/SoalTemplateController.php` | Update `buildSheetPoint()` dan `buildSheetScore()` sesuai format baru |
| `app/Config/Routes.php` | Tambah route `GET/POST master/soal/salin` |
| `app/Views/admin/master/soal/index.php` | Tambah tombol "Salin Soal" di header |
| `app/Views/admin/master/soal/import.php` | Update panduan format sesuai kolom baru |
| `app/Views/admin/master/soal/salin.php` | **Baru** — halaman form salin soal |

---

## Testing Recommendations

### Import
1. Download template baru → pastikan kolom sesuai format baru (tanpa sub_kategori)
2. Isi sheet POINT dengan nama kategori valid (cek sheet Referensi) → import → berhasil
3. Isi sheet SCORE dengan nama kategori valid → import → berhasil
4. Isi nama kategori yang tidak ada → import → error validasi
5. Isi nilai SCORE yang sama (duplikat) → import → error validasi

### Salin Soal
1. Buka `/admin/master/soal/salin`
2. Pilih tryout sumber = tujuan → tombol disabled + alert muncul
3. Pilih tryout sumber yang tidak punya soal → error "tidak memiliki soal"
4. Pilih tryout sumber dan tujuan berbeda → konfirmasi → salin berhasil
5. Salin ulang ke tujuan yang sama → semua soal dilewati (0 disalin, N dilewati)

---

## Git Commands

```bash
git checkout -b ft-soal-import-salin

git add app/Controllers/Admin/Master/SoalController.php
git add app/Controllers/Admin/Master/SoalTemplateController.php
git add app/Config/Routes.php
git add app/Views/admin/master/soal/index.php
git add app/Views/admin/master/soal/import.php
git add app/Views/admin/master/soal/salin.php

git commit -m "feat(soal): update import format without sub_kategori, add salin soal feature"

git push origin ft-soal-import-salin
```
