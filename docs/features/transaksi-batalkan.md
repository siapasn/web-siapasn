# Pembatalan Transaksi Pending

## Overview

Menambahkan fitur pembatalan transaksi oleh user. Hanya transaksi dengan status `pending` yang dapat dibatalkan. Pembatalan juga dikirim ke Midtrans API agar order di-cancel di sisi payment gateway.

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Config/Routes.php` | Tambah route `POST transaksi/(:num)/batalkan` |
| `app/Controllers/User/TransaksiController.php` | Tambah method `batalkan()`, update `validStatuses` & `cekStatus` untuk include `cancelled` |
| `app/Services/MidtransService.php` | Tambah method `cancelTransaction()` — cancel order via Midtrans Cancel API |
| `app/Views/user/transaksi/show.php` | Tombol "Batalkan Transaksi" + modal konfirmasi, support status `cancelled` |
| `app/Views/user/transaksi/index.php` | Tambah filter button & badge color untuk status `cancelled` |

---

## Flow Pembatalan

1. User buka halaman detail transaksi (`/user/transaksi/:id`) yang berstatus `pending`
2. Di bawah tombol "Bayar Sekarang" terdapat tombol "Batalkan Transaksi"
3. Klik → muncul modal konfirmasi dengan peringatan bahwa pembatalan tidak bisa dikembalikan
4. User konfirmasi "Ya, Batalkan" → form POST ke `/user/transaksi/{id}/batalkan`
5. Controller:
   - Validasi transaksi milik user dan status = `pending`
   - Update status ke `cancelled`
   - Cancel order di Midtrans via Cancel API (best-effort, tetap lanjut jika gagal)
   - Kirim notifikasi ke user
6. Redirect kembali ke detail transaksi dengan flash message sukses
7. Setelah dibatalkan, status banner berubah dan muncul tombol "Coba Beli Lagi"

---

## Status `cancelled`

Status baru `cancelled` ditambahkan ke seluruh flow:

- **Detail transaksi** — banner "Transaksi Dibatalkan" (warna dark, icon slash-circle)
- **List transaksi** — badge dark + filter button
- **cekStatus** — dianggap final, tidak perlu cek ulang ke Midtrans
- **Aksi setelah cancel** — tombol "Coba Beli Lagi" (sama seperti failed/expired)

---

## Midtrans Cancel API

Method baru `MidtransService::cancelTransaction(string $orderId)`:
- Endpoint: `POST /v2/{order_id}/cancel`
- Auth: Basic (server key)
- Hanya berhasil jika transaksi masih pending di Midtrans
- Error tidak blocking — jika gagal cancel di Midtrans, status lokal tetap di-update

---

## Testing

1. Buat transaksi baru (beli paket) → status pending
2. Buka `/user/transaksi/:id` → verifikasi tombol "Batalkan Transaksi" muncul
3. Klik → modal konfirmasi muncul
4. Klik "Ya, Batalkan" → status berubah ke `cancelled`, notifikasi terkirim
5. Verifikasi tombol "Coba Beli Lagi" muncul
6. Buka list transaksi → filter "Cancelled" bekerja, badge warna dark
7. Pastikan transaksi yang sudah `success`/`failed`/`expired` TIDAK punya tombol batalkan

---

## Git

```bash
git checkout -b ft-transaksi-batalkan
git add app/Config/Routes.php app/Controllers/User/TransaksiController.php app/Services/MidtransService.php app/Views/user/transaksi/show.php app/Views/user/transaksi/index.php
git commit -m "feat(transaksi): tambah fitur pembatalan transaksi pending oleh user"
```
