# Fitur: Ranking Access Control

## Overview

Membatasi akses halaman perangkingan (`/user/ranking`) agar hanya bisa dilihat oleh user yang telah membeli produk terkait atau terdaftar pada event tryout. Sebelumnya, semua user yang login bisa melihat seluruh leaderboard tanpa validasi kepemilikan.

## Aturan Akses

| Kondisi | Akses Ranking |
|---------|---------------|
| User membeli produk yang mengandung tryout (& belum expired) | ✅ Diizinkan |
| User terdaftar di event tryout yang menggunakan tryout tersebut | ✅ Diizinkan |
| User belum beli produk & belum daftar event | ❌ Ditolak |

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/User/RankingController.php` | Tambah validasi akses di `index()` dan `leaderboard()` |
| `app/Views/user/ranking/index.php` | Update empty state + flash message error |

## Perubahan Detail

### RankingController::index()

1. Query `user_produk` untuk mendapatkan produk yang dimiliki user (belum expired)
2. Query `tryout_event_peserta` untuk mendapatkan tryout dari event yang diikuti user
3. Gabungkan kedua list → hanya tampilkan tryout yang ada di list tersebut
4. Jika tidak ada akses sama sekali, tampilkan empty state dengan CTA ke halaman produk & event

### RankingController::leaderboard($tryoutId)

1. Cek apakah user punya produk yang mengandung tryout ini (`mapping_tryout` + `user_produk`)
2. Cek apakah user terdaftar di event yang menggunakan tryout ini (`tryout_event_peserta` + `tryout_event`)
3. Jika keduanya 0, redirect ke `/user/ranking` dengan flash message error

### View index.php

- Empty state diperbarui dengan pesan informatif + tombol ke halaman Paket dan Event
- Tambah alert dismissible untuk flash message error dari redirect leaderboard

## Flow Validasi

```
User akses /user/ranking
  → Query produk yang dimiliki (user_produk, expired_at valid)
  → Query event yang diikuti (tryout_event_peserta)
  → Gabungkan tryout_id yang bisa diakses
  → Filter daftar tryout hanya yang accessible
  → Tampilkan

User akses /user/ranking/{id}
  → Cek produk access (mapping_tryout + user_produk)
  → Cek event access (tryout_event_peserta + tryout_event)
  → Jika keduanya 0 → redirect + error message
  → Jika salah satu > 0 → tampilkan leaderboard
```

## Testing

1. **User tanpa produk & tanpa event** — halaman ranking harus kosong dengan pesan "Belum ada data perangkingan"
2. **User dengan produk aktif** — hanya tryout dari produk yang dimiliki yang muncul
3. **User terdaftar event** — tryout dari event tersebut juga muncul
4. **Akses langsung URL leaderboard tanpa hak** — redirect ke index dengan pesan error
5. **User dengan produk expired** — tryout dari produk tersebut tidak muncul

## Git Workflow

```bash
git checkout -b ft-ranking-access-control
git add app/Controllers/User/RankingController.php app/Views/user/ranking/index.php docs/features/ranking-access-control.md docs/README.md
git commit -m "feat(ranking): restrict leaderboard access to product owners and event participants"
git push -u origin ft-ranking-access-control
```
