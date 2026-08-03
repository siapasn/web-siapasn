# Cart Checkout Multi Produk — Pilih Metode Pembayaran

## Overview

Sebelumnya, saat user checkout dari keranjang yang berisi lebih dari 1 paket, sistem hanya menampilkan 1 produk di halaman pilih metode pembayaran. Ini karena `CartController::checkout()` hanya redirect ke `/user/transaksi/pilih-metode/{produkId}` untuk item pertama.

Perbaikan ini menambahkan flow multi-produk sehingga halaman pilih metode menampilkan **semua item keranjang** dengan detail harga, diskon, dan total yang benar.

---

## Perubahan

### Files Modified

| File | Perubahan |
|---|---|
| `app/Config/Routes.php` | Tambah route `GET pilih-metode-cart` dan `POST beli-cart` |
| `app/Controllers/User/CartController.php` | `checkout()` redirect ke `pilih-metode-cart` |
| `app/Controllers/User/TransaksiController.php` | Tambah `pilihMetodeCart()` dan `beliCart()` |
| `app/Views/user/transaksi/pilih-metode.php` | Support dual mode: single produk & multi produk (cart) |

### Route Baru

```
GET  user/transaksi/pilih-metode-cart   → TransaksiController::pilihMetodeCart()
POST user/transaksi/beli-cart           → TransaksiController::beliCart()
```

### Flow Baru (Cart Multi Produk)

1. User klik "Lanjut ke Pembayaran" dari halaman keranjang
2. `CartController::checkout()` redirect ke `user/transaksi/pilih-metode-cart`
3. `pilihMetodeCart()` mengambil semua item dari Redis cart, hitung harga & promo per item
4. View menampilkan ringkasan pesanan dengan semua paket (thumbnail, nama, tryout count, harga/diskon)
5. User pilih metode pembayaran lalu submit
6. `beliCart()` membuat transaksi terpisah untuk setiap produk (masing-masing snap token sendiri)
7. Redirect ke transaksi pertama

### Flow Lama (Single Produk) — Tetap Berfungsi

Route `/user/transaksi/pilih-metode/{id}` tetap bekerja normal untuk beli langsung dari halaman produk.

---

## Detail Teknis

### View Dual Mode

View `pilih-metode.php` mendeteksi mode berdasarkan variabel `$produkList`:

```php
$isCart = ! empty($produkList);
```

- **Mode Single**: Menampilkan thumbnail besar 1:1, nama produk, jumlah tryout, harga, diskon promo
- **Mode Cart**: Menampilkan list item dengan thumbnail kecil 48px, subtotal, total diskon, grand total, dan badge jumlah paket

### Voucher pada Multi Produk

Voucher hanya di-apply ke item pertama yang diproses. Setelah digunakan, voucher tidak digunakan ulang untuk item selanjutnya.

### Edge Cases

- Jika keranjang hanya berisi 1 item yang belum dibeli → `pilihMetodeCart()` redirect ke `pilih-metode/{id}` (single mode)
- Produk yang sudah dimiliki di-skip saat proses
- Produk tidak aktif di-skip

---

## Testing

1. Tambahkan 2+ paket ke keranjang
2. Klik "Lanjut ke Pembayaran"
3. Verifikasi semua paket muncul di ringkasan pesanan (kanan)
4. Verifikasi subtotal = jumlah semua harga, diskon = jumlah semua diskon promo, total = subtotal - diskon
5. Pilih metode → submit → verifikasi transaksi terpisah dibuat untuk setiap produk
6. Test juga beli langsung dari halaman produk (single mode) — harus tetap normal

---

## Git

```bash
git checkout -b fx-cart-checkout-multi-produk-detail
git add app/Config/Routes.php app/Controllers/User/CartController.php app/Controllers/User/TransaksiController.php app/Views/user/transaksi/pilih-metode.php
git commit -m "fix(cart): tampilkan semua item keranjang di halaman pilih metode + responsive mobile UI"
```
