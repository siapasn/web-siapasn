# Fitur: Blast Email — Expand ke Individual Records

## Overview

Mengubah rules blast email agar saat admin memilih penerima "Semua User" atau "Subscriber", sistem langsung expand menjadi individual records (1 email = 1 row di tabel `blast_email`). Cron job kemudian mengirim satu per satu.

## Perubahan Arsitektur

### Sebelum
- Tipe `all` / `subscribe` → 1 record di `blast_email`
- Cron job membaca ulang tabel `users` atau `users_subscribe` setiap kali dipanggil
- Tracking hanya di level job (total_sent/total_failed aggregate)

### Sesudah
- Semua tipe → di-expand langsung menjadi N individual records
- Setiap record: `tipe = 'single'`, `target_email` = alamat email, `target_nama` = nama
- Cron job ambil batch 50 record pending → kirim 1-1 → update status per record

## Flow Baru

```
Admin submit form blast email
  ├── tipe = 'single'           → 1 record (langsung)
  ├── tipe = 'all'              → Baca users → Insert N records
  ├── tipe = 'subscribe'        → Baca users_subscribe → Insert N records
  └── tipe = 'subscribe_single' → Baca selected subscribers → Insert N records

Cron job (/cron/process-blast-email)
  → SELECT * FROM blast_email WHERE status='pending' LIMIT 50
  → Kirim email per record
  → UPDATE status = 'done' atau 'failed'
```

## Keuntungan

- **Tracking per penerima** — bisa lihat siapa yang berhasil/gagal
- **Retry mudah** — set status kembali ke `pending` untuk re-send
- **Tidak perlu baca ulang** tabel users/subscriber setiap cron run
- **Konsisten** — semua record di tabel blast_email = 1 email = 1 penerima

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/Admin/BlastEmailController.php` | Method `send()` — expand recipients ke individual records |
| `app/Controllers/CronController.php` | Method `processBlastEmail()` — simplified, proses per record |

## Files Created

| File | Fungsi |
|------|--------|
| `app/Database/Migrations/2026-06-06-000001_AddTargetNamaToBlastEmail.php` | Tambah kolom `target_nama` |

## Database Change

```sql
ALTER TABLE blast_email ADD COLUMN target_nama VARCHAR(255) NULL AFTER target_email;
```

## Testing

1. **Tipe single** — submit → 1 record di blast_email, cron kirim 1 email
2. **Tipe all** — submit → N records (sesuai jumlah user aktif & verified), cron kirim batch 50
3. **Tipe subscribe** — submit → N records (sesuai jumlah subscriber), cron kirim batch 50
4. **Tipe subscribe_single** — submit → N records (sesuai subscriber terpilih)
5. **Cron retry** — set status record `failed` kembali ke `pending`, cron kirim ulang
6. **Riwayat** — halaman admin menampilkan per-email status

## Git Workflow

```bash
git checkout -b refactor-blast-email-single-expand
git add app/Controllers/Admin/BlastEmailController.php app/Controllers/CronController.php app/Database/Migrations/2026-06-06-000001_AddTargetNamaToBlastEmail.php docs/features/blast-email-single-expand.md docs/README.md
git commit -m "refactor(blast-email): expand all/subscribe recipients into individual records for per-email tracking"
git push -u origin refactor-blast-email-single-expand
```
