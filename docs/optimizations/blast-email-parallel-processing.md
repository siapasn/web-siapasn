# Optimasi: Blast Email Parallel Processing

## Overview

Mengubah proses pengiriman blast email dari sequential (1 per 1, blocking) menjadi parallel (multiple background processes bersamaan, non-blocking). Mirip konsep goroutine di Go — spawn multiple independent workers yang berjalan bersamaan.

## Arsitektur Baru

```
Cron dipanggil (/cron/process-blast-email)
  → Ambil 10 email pending
  → Tandai semua sebagai 'sending' (lock)
  → Spawn background processes paralel:
      php spark email:send 101  ─┐
      php spark email:send 102  ─┤ 5 proses bersamaan
      php spark email:send 103  ─┤ (fire-and-forget)
      php spark email:send 104  ─┤
      php spark email:send 105  ─┘
      [delay 0.5 detik]
      php spark email:send 106  ─┐
      php spark email:send 107  ─┤ batch berikutnya
      ...                        ─┘
  → Cron selesai dalam ~1-2 detik
  → Background processes kirim email secara independen
```

## Status Flow

```
pending → sending → done/failed
```

- `pending` — menunggu diproses
- `sending` — sudah di-pick oleh cron, proses background sedang berjalan
- `done` — berhasil terkirim
- `failed` — gagal kirim

## Perbandingan Performa

| Metrik | Sebelum (Sequential) | Sesudah (Parallel) |
|---|---|---|
| Email per cron call | 1 (timeout) | 10 (5 paralel × 2 batch) |
| Waktu cron selesai | ~30 detik (timeout) | ~1-2 detik |
| Email per menit | 1 | ~10 |
| Blocking | Ya | Tidak (fire-and-forget) |

## Files Created

| File | Fungsi |
|------|--------|
| `app/Commands/SendSingleEmail.php` | Spark CLI command: kirim 1 email by ID (`php spark email:send {id}`) |

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/CronController.php` | `processBlastEmail()` rewritten — parallel spawn + sequential fallback |

## Konfigurasi

```php
$batchSize   = 10; // jumlah email per cron call
$maxParallel = 5;  // jumlah proses paralel bersamaan
```

Tuning:
- Naikkan `$batchSize` untuk throughput lebih tinggi (misal 20-50)
- Naikkan `$maxParallel` jika server kuat (misal 10)
- Jaga agar `$batchSize × SMTP_time < cron_interval` untuk menghindari overlap

## Fallback

Jika `proc_open` diblokir oleh hosting (beberapa shared hosting disable fungsi ini), otomatis fallback ke mode **sequential** — kirim satu per satu seperti sebelumnya.

## Testing

```bash
# Test spark command langsung
php spark email:send 123

# Test cron endpoint
curl -H "X-Cron-Key: YOUR_KEY" https://yourdomain.com/cron/process-blast-email

# Cek response mode
# mode: "parallel" = spawn berhasil
# mode: "sequential" = fallback (proc_open disabled)
```

## Catatan cPanel

Jika `proc_open` diblokir, hubungi hosting untuk enable, atau gunakan alternatif:
1. Set cron job lebih sering (setiap 30 detik) dengan mode sequential
2. Atau gunakan multiple cron entry yang memanggil endpoint berbeda

## Git Workflow

```bash
git checkout -b perf-blast-email-parallel
git add app/Commands/SendSingleEmail.php app/Controllers/CronController.php docs/optimizations/blast-email-parallel-processing.md docs/README.md
git commit -m "perf(blast-email): implement parallel email processing via background CLI processes"
git push -u origin perf-blast-email-parallel
```
