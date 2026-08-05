# Fix: Popup Notifikasi Tertutup & Keranjang Tidak Dikosongkan Setelah Pembayaran

## Overview

Dua bug diperbaiki pada halaman pilih metode pembayaran:

1. **Popup notifikasi tertutup oleh konten** — Dropdown notifikasi di topbar tertimpa oleh elemen `.sticky-top` pada order summary card karena z-index konflik.
2. **Keranjang tidak dikosongkan** — Setelah user memilih metode pembayaran dan submit, data di keranjang (Redis) tidak dihapus sehingga badge cart masih menampilkan jumlah item.

## Root Cause

### Popup Notifikasi
- `#topbar` memiliki `z-index: 999`
- Bootstrap `.sticky-top` memiliki `z-index: 1020`
- Akibatnya, order summary card yang menggunakan `.sticky-top` berada di atas topbar dan menutupi dropdown notifikasi.

### Cart Tidak Dikosongkan
- Method `beliCart()` di `TransaksiController` tidak memanggil `CartService::clear()` setelah transaksi berhasil dibuat.
- Cart hanya dibersihkan per-item saat webhook Midtrans mengkonfirmasi pembayaran (terlambat).

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Views/layouts/main.php` | `#topbar` z-index dinaikkan ke `1030`; `.notif-menu` ditambahkan `z-index: 1050` |
| `app/Controllers/User/TransaksiController.php` | Ditambahkan `$cartService->clear((int) $userId)` di akhir method `beliCart()` |

## Key Changes

### Layout (z-index fix)
```css
#topbar {
    z-index: 1030; /* sebelumnya 999 */
}

.notif-menu {
    z-index: 1050; /* baru ditambahkan */
}
```

### TransaksiController (cart clear)
```php
// Setelah semua transaksi berhasil dibuat
$cartService->clear((int) $userId);
```

## Testing Recommendations

1. Buka halaman pilih metode pembayaran dengan item di keranjang
2. Klik icon bell (notifikasi) — pastikan dropdown muncul di atas semua konten
3. Pilih metode pembayaran lalu submit
4. Setelah redirect ke halaman transaksi, cek badge cart di topbar — harus 0 / hilang
5. Kembali ke halaman cart — harus kosong

## Git Workflow

```bash
git checkout -b fx-notif-popup-and-cart-clear
git add app/Views/layouts/main.php app/Controllers/User/TransaksiController.php docs/troubleshooting/notif-popup-hidden-and-cart-not-cleared.md docs/README.md
git commit -m "fix: notification popup z-index overlap and clear cart after checkout"
git push -u origin fx-notif-popup-and-cart-clear
```
