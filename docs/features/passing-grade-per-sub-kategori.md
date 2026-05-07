# Passing Grade per Sub Kategori & Scoring TKP

## Overview

Implementasi sistem passing grade per sub kategori pada hasil tryout, beserta perbaikan scoring untuk soal bertipe SCORE (TKP) yang menggunakan nilai 1-5 per pilihan.

---

## Perubahan

### 1. Database

```sql
ALTER TABLE hasil_tryout
  ADD COLUMN status_lulus ENUM('lulus','tidak_lulus') NULL DEFAULT NULL AFTER peringkat,
  ADD COLUMN detail_passing_grade JSON NULL DEFAULT NULL AFTER status_lulus;
```

### 2. Scoring TKP (SCORE)

Sebelumnya semua soal dihitung benar/salah. Sekarang soal bertipe `SCORE` dihitung berdasarkan nilai pilihan:

```
skor_sub_kategori = (total_nilai_dipilih / max_nilai_possible) * 100
max_nilai = jumlah_soal * 5  (nilai max per soal TKP = 5)
```

### 3. Passing Grade Logic

Method `hitungPassingGrade()` di `TryoutScoringService`:
- Query `passing_grade` untuk tryout spesifik + global (tryout_id IS NULL)
- Cek skor aktual vs `nilai_minimum` per sub kategori
- `status_lulus = 'lulus'` hanya jika **semua** passing grade terpenuhi
- Simpan detail per sub kategori ke `detail_passing_grade` (JSON)

### 4. Konfigurasi Passing Grade

Dikelola via menu **Master Passing Grade** (admin). Contoh konfigurasi:

| Kategori | Sub Kategori | Nilai Minimum |
|---|---|---|
| CPNS | TWK | 50.0 |
| CPNS | TIU | 55.0 |
| CPNS | TKP | 45.0 |

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Services/TryoutScoringService.php` | Scoring SCORE/POINT, method `hitungPassingGrade()` |
| `app/Models/HasilTryoutModel.php` | Tambah `status_lulus`, `detail_passing_grade` ke `allowedFields` |
| `app/Views/user/tryout/hasil.php` | Badge lulus/tidak lulus, card passing grade, badge per sub kategori |

---

## Format JSON `detail_passing_grade`

```json
[
  { "label": "TWK", "kategori_id": 1, "sub_kategori_id": 6, "nilai_minimum": 50.0, "skor_aktual": 62.5, "lulus": true },
  { "label": "TIU", "kategori_id": 1, "sub_kategori_id": 7, "nilai_minimum": 55.0, "skor_aktual": 48.0, "lulus": false },
  { "label": "TKP", "kategori_id": 1, "sub_kategori_id": 8, "nilai_minimum": 45.0, "skor_aktual": 71.2, "lulus": true }
]
```

## Format JSON `detail_kategori` (diperbarui)

```json
[
  {
    "kategori_id": 1, "kategori_nama": "CPNS",
    "sub_kategori_id": 6, "sub_kategori_nama": "TWK",
    "tipe_soal": "POINT",
    "benar": 25, "salah": 10, "kosong": 5, "total": 40,
    "skor": 62.5, "total_nilai": 0, "max_nilai": 0
  },
  {
    "sub_kategori_id": 8, "sub_kategori_nama": "TKP",
    "tipe_soal": "SCORE",
    "benar": 30, "salah": 5, "kosong": 0, "total": 35,
    "skor": 71.2, "total_nilai": 124, "max_nilai": 175
  }
]
```

---

## Testing

1. Pastikan passing grade sudah dikonfigurasi di **Master Passing Grade**
2. Kerjakan tryout yang memiliki soal TWK, TIU, dan TKP
3. Selesaikan tryout → cek halaman hasil
4. Verifikasi:
   - Badge LULUS/TIDAK LULUS muncul di atas skor
   - Card Passing Grade menampilkan status per sub kategori
   - Soal TKP menampilkan `Nilai: X/Y` bukan hanya benar/salah
5. DB check:
```sql
SELECT status_lulus, detail_passing_grade
FROM hasil_tryout
ORDER BY id DESC LIMIT 1;
```
