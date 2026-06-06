# Fitur: Tombol Hasil & Pembahasan di Detail Event Tryout

## Overview

Menambahkan tombol "Lihat Hasil" dan "Lihat Pembahasan" pada halaman `/user/tryout-event/:id` ketika user telah menyelesaikan tryout. Sebelumnya, halaman ini hanya menampilkan skor terbaik tanpa cara navigasi ke halaman detail hasil atau pembahasan soal.

## Perubahan

### Controller (`TryoutEventController::detail()`)

- Tambah query `$semuaHasil` — mengambil semua hasil percobaan user (diurutkan terbaru) untuk ditampilkan sebagai riwayat
- Pass variabel `semuaHasil` ke view

### View (`detail.php`)

Ketika `$hasilUser` ada (user telah menyelesaikan tryout):

1. **Skor terbaik** + badge lulus/belum lulus (sudah ada)
2. **Tombol "Lihat Hasil"** → link ke `/user/tryout/hasil/{sesiId}`
3. **Tombol "Lihat Pembahasan"** → link ke `/user/tryout/pembahasan/{sesiId}`
4. **Riwayat semua percobaan** — jika > 1 percobaan, tampilkan list dengan:
   - Nomor percobaan + tanggal
   - Skor masing-masing
   - Tombol icon untuk hasil & pembahasan per percobaan

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/User/TryoutEventController.php` | Tambah query `$semuaHasil` + pass ke view |
| `app/Views/user/tryout-event/detail.php` | Tambah tombol hasil/pembahasan + riwayat percobaan |

## URL yang Digunakan

| Aksi | URL | Parameter |
|------|-----|-----------|
| Lihat Hasil | `/user/tryout/hasil/{sesiId}` | `sesi_tryout.id` |
| Lihat Pembahasan | `/user/tryout/pembahasan/{sesiId}` | `sesi_tryout.id` |

## Testing

1. **User yang sudah selesai tryout event** — tombol "Lihat Hasil" dan "Lihat Pembahasan" harus muncul
2. **User dengan > 1 percobaan** — riwayat percobaan ditampilkan dengan tombol per sesi
3. **User belum selesai / belum daftar** — tombol tidak muncul
4. **Klik tombol** — redirect ke halaman hasil/pembahasan yang benar
5. **Event fase apapun** — selama user punya hasil, tombol harus muncul (fase pelaksanaan maupun selesai)

## Git Workflow

```bash
git checkout -b ft-event-detail-hasil-pembahasan
git add app/Controllers/User/TryoutEventController.php app/Views/user/tryout-event/detail.php docs/features/tryout-event-hasil-pembahasan.md docs/README.md
git commit -m "feat(tryout-event): add hasil and pembahasan buttons on event detail page"
git push -u origin ft-event-detail-hasil-pembahasan
```
