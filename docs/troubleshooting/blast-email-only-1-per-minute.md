# Troubleshooting: Blast Email Hanya Kirim 1 per Menit

## Masalah

Cron job di-set per 1 menit, tapi hanya 1 email terkirim per cron call. Batch 50 tidak berjalan — hanya email pertama yang terproses sebelum PHP timeout.

## Root Cause

PHP `max_execution_time` di cPanel default 30 detik. Setiap email membutuhkan koneksi SMTP baru (connect → TLS handshake → auth → send → disconnect) yang bisa memakan 10-30 detik. Setelah kirim 1 email, PHP timeout dan proses berhenti.

## Solusi

1. **`set_time_limit(300)`** — perpanjang execution time jadi 5 menit khusus untuk endpoint cron blast email
2. **`SMTPTimeout = 5`** — kurangi timeout SMTP dari 10 detik ke 5 detik agar per email lebih cepat

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/CronController.php` | Tambah `set_time_limit(300)` + kurangi SMTP timeout ke 5 detik |

## Estimasi Setelah Fix

| SMTP Timeout | Waktu per Email | Batch 50 Selesai Dalam |
|---|---|---|
| 5 detik (baru) | ~3-5 detik | ~2.5-4 menit |
| 10 detik (lama) | ~10-15 detik | ~8-12 menit (timeout duluan) |

## Alternatif Jika Masih Lambat

- Tambah `php_value max_execution_time 300` di `.htaccess`
- Atau set di `public/php.ini`: `max_execution_time=300`
- Kurangi `$batchSize` ke 10-20 jika server tetap timeout

## Git Workflow

```bash
git checkout -b perf-blast-email-batch-timeout
git add app/Controllers/CronController.php docs/troubleshooting/blast-email-only-1-per-minute.md docs/README.md
git commit -m "perf(blast-email): fix batch only sending 1 email per cron - increase execution time and reduce SMTP timeout"
git push -u origin perf-blast-email-batch-timeout
```
