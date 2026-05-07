# API: Cron Sync Payment Status

## Overview

Endpoint untuk sinkronisasi status pembayaran semua transaksi `pending` langsung ke Midtrans Status API. Dirancang untuk dijalankan sebagai **cron job** secara berkala.

**Endpoint:** `GET /cron/sync-payment-status`  
**Auth:** API Key via header atau query param  
**Controller:** `app/Controllers/CronController.php`

---

## Authentication

Diamankan dengan API key yang disimpan di tabel `master_aplikasi` (`config_key = 'cron_api_key'`).

### Cara kirim API key

**Via Header (direkomendasikan):**
```bash
curl -s -H "X-Cron-Key: {API_KEY}" https://yourdomain.com/cron/sync-payment-status
```

**Via Query Param:**
```bash
curl -s "https://yourdomain.com/cron/sync-payment-status?key={API_KEY}"
```

### Ambil API key
```sql
SELECT config_value FROM master_aplikasi WHERE config_key = 'cron_api_key';
```

---

## Response

### Sukses (`200 OK`)
```json
{
  "success": true,
  "processed": 3,
  "updated": 1,
  "results": [
    {
      "id": 5,
      "kode": "TRX-69FC3365A1CEB",
      "old_status": "pending",
      "new_status": "success"
    }
  ],
  "errors": [],
  "duration_ms": 842,
  "timestamp": "2026-05-07 14:30:00"
}
```

| Field | Keterangan |
|---|---|
| `processed` | Jumlah transaksi pending yang dicek ke Midtrans |
| `updated` | Jumlah transaksi yang statusnya berubah |
| `results` | Detail transaksi yang diupdate |
| `errors` | Transaksi yang gagal dicek (Midtrans API error) |
| `duration_ms` | Waktu eksekusi dalam milidetik |

### Unauthorized (`401`)
```json
{ "success": false, "message": "Unauthorized. API key tidak valid." }
```

---

## Logic

1. Verifikasi API key
2. Ambil semua transaksi `status = pending` yang dibuat **> 1 menit lalu** (hindari race condition)
3. Untuk setiap transaksi → `GET api.midtrans.com/v2/{order_id}/status`
4. Map `transaction_status` Midtrans ke status aplikasi:

| Midtrans `transaction_status` | Status DB |
|---|---|
| `settlement`, `capture` | `success` |
| `deny`, `cancel` | `failed` |
| `expire` | `expired` |
| `pending` | tidak berubah |

5. Jika `success` → aktifkan `user_produk` (akses produk)
6. Simpan `payment_method` & `payment_channel` dari response Midtrans

---

## Files

| File | Keterangan |
|---|---|
| `app/Controllers/CronController.php` | Controller baru dengan method `syncPaymentStatus()` |
| `app/Config/Routes.php` | Tambah route `GET /cron/sync-payment-status` |
| `master_aplikasi` (DB) | Row baru `cron_api_key` dengan nilai SHA256 |

---

## Cron Job Setup

### cPanel / Linux crontab
```bash
# Setiap 5 menit
*/5 * * * * curl -s -H "X-Cron-Key: {API_KEY}" https://yourdomain.com/cron/sync-payment-status >> /var/log/cron-payment.log 2>&1

# Setiap 1 menit (lebih responsif)
* * * * * curl -s -H "X-Cron-Key: {API_KEY}" https://yourdomain.com/cron/sync-payment-status
```

### Ganti API Key
Untuk rotate API key, update langsung di DB:
```sql
UPDATE master_aplikasi
SET config_value = SHA2(CONCAT('siapasn-cron-', NOW()), 256),
    updated_at   = NOW()
WHERE config_key = 'cron_api_key';
```

---

## Testing

```bash
# 1. Ambil API key
mysql -u root cpns_tryout -e "SELECT config_value FROM master_aplikasi WHERE config_key='cron_api_key';"

# 2. Panggil endpoint
curl -s -H "X-Cron-Key: {API_KEY}" http://localhost:8081/cron/sync-payment-status | python -m json.tool

# 3. Verifikasi DB setelah ada transaksi yang diupdate
mysql -u root cpns_tryout -e "SELECT id, kode_transaksi, status, payment_method FROM transaksi ORDER BY updated_at DESC LIMIT 5;"
```
