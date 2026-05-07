# Midtrans Payment Methods — Halaman Pilih Metode Pembayaran

## Overview

Menambahkan halaman pemilihan metode pembayaran sebelum checkout, menggantikan alur langsung ke Snap UI. User kini dapat memilih metode pembayaran spesifik sehingga Snap hanya menampilkan satu metode yang relevan.

**7 Metode yang tersedia:**
- QRIS (semua e-wallet & m-banking)
- GoPay
- ShopeePay
- Bank Transfer Mandiri
- Bank Transfer BNI
- Bank Transfer BRI
- Bank Transfer Permata

---

## Alur Pembayaran Baru

```
Katalog Produk
    → Detail Produk (klik "Pilih Metode & Beli")
        → /user/transaksi/pilih-metode/{produkId}   ← BARU
            → POST /user/transaksi/beli/{produkId}
                → /user/transaksi/{id}  (Snap popup)
                    → Webhook Midtrans
                        → Akses produk aktif
```

---

## Files Modified / Created

### Baru
| File | Keterangan |
|---|---|
| `app/Views/user/transaksi/pilih-metode.php` | Halaman UI pilih metode pembayaran |

### Diubah
| File | Perubahan |
|---|---|
| `app/Services/MidtransService.php` | Tambah `PAYMENT_METHODS` constant, `enabled_payments` di Snap payload, `detectPaymentInfo()`, support sandbox/production URL otomatis |
| `app/Controllers/User/TransaksiController.php` | Tambah method `pilihMetode()`, update `beli()` terima `payment_method`, `show()` kirim `isProduction` |
| `app/Controllers/WebhookController.php` | Simpan `payment_method` & `payment_channel` dari payload webhook |
| `app/Models/TransaksiModel.php` | Tambah `payment_method` & `payment_channel` ke `allowedFields` |
| `app/Views/user/produk/show.php` | Tombol "Beli Sekarang" → link ke halaman pilih metode |
| `app/Views/user/transaksi/show.php` | Badge metode pembayaran, Snap.js URL dinamis (sandbox/production) |
| `app/Config/Routes.php` | Tambah route `GET user/transaksi/pilih-metode/(:num)` |

### Database
```sql
ALTER TABLE transaksi
  ADD COLUMN payment_method  VARCHAR(50) NULL DEFAULT NULL AFTER snap_token,
  ADD COLUMN payment_channel VARCHAR(50) NULL DEFAULT NULL AFTER payment_method;
```

---

## Key Changes

### 1. `MidtransService::PAYMENT_METHODS` Constant

```php
public const PAYMENT_METHODS = [
    'qris'      => ['label' => 'QRIS',                  'enabled' => ['qris'],        ...],
    'gopay'     => ['label' => 'GoPay',                 'enabled' => ['gopay'],       ...],
    'shopeepay' => ['label' => 'ShopeePay',             'enabled' => ['shopeepay'],   ...],
    'mandiri'   => ['label' => 'Bank Transfer Mandiri', 'enabled' => ['echannel'],    ...],
    'bni'       => ['label' => 'Bank Transfer BNI',     'enabled' => ['bni_va'],      ...],
    'bri'       => ['label' => 'Bank Transfer BRI',     'enabled' => ['bri_va'],      ...],
    'permata'   => ['label' => 'Bank Transfer Permata', 'enabled' => ['permata_va'],  ...],
];
```

### 2. Snap Token dengan `enabled_payments`

```php
// Hanya tampilkan metode yang dipilih user
$payload['enabled_payments'] = MidtransService::PAYMENT_METHODS[$paymentMethod]['enabled'];
// Contoh untuk BNI: ['bni_va']
```

### 3. Snap.js URL Dinamis

```php
// sandbox vs production otomatis dari konfigurasi master_aplikasi
<script src="<?= $isProduction
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
    data-client-key="<?= esc($clientKey) ?>">
```

### 4. Deteksi Payment Info dari Webhook

```php
// WebhookController.php — setelah update status
$paymentInfo = $midtransService->detectPaymentInfo($payload);
$transaksiModel->update($transaksi['id'], [
    'payment_method'  => $paymentInfo['method'],   // e.g. 'bni'
    'payment_channel' => $paymentInfo['channel'],  // e.g. 'bni'
]);
```

---

## Mapping Midtrans `payment_type` → `payment_method`

| Midtrans `payment_type` | `payment_method` DB | `enabled_payments` Snap |
|---|---|---|
| `qris` | `qris` | `['qris']` |
| `gopay` | `gopay` | `['gopay']` |
| `shopeepay` | `shopeepay` | `['shopeepay']` |
| `echannel` | `mandiri` | `['echannel']` |
| `bank_transfer` (bni) | `bni` | `['bni_va']` |
| `bank_transfer` (bri) | `bri` | `['bri_va']` |
| `permata_va` | `permata` | `['permata_va']` |

---

## Testing Recommendations

### Manual Testing (Sandbox)
1. Buka halaman detail produk → klik "Pilih Metode & Beli"
2. Pilih setiap metode satu per satu, pastikan:
   - Card ter-highlight saat dipilih
   - Tombol "Lanjut" berubah label sesuai metode
   - Snap popup hanya menampilkan metode yang dipilih
3. Selesaikan pembayaran di Snap sandbox
4. Verifikasi webhook diterima dan `payment_method` tersimpan di DB
5. Verifikasi akses produk aktif setelah pembayaran sukses

### Sandbox Test Credentials Midtrans
- QRIS: scan QR di Snap UI
- GoPay: gunakan nomor HP test di Snap
- Bank Transfer: gunakan VA number yang muncul di Snap

### Database Check
```sql
SELECT id, kode_transaksi, status, payment_method, payment_channel
FROM transaksi
ORDER BY created_at DESC
LIMIT 10;
```

---

## Git Workflow

```bash
# Buat feature branch
git checkout -b ft-midtrans-payment-methods

# Stage semua perubahan
git add app/Services/MidtransService.php
git add app/Controllers/User/TransaksiController.php
git add app/Controllers/WebhookController.php
git add app/Models/TransaksiModel.php
git add app/Views/user/transaksi/pilih-metode.php
git add app/Views/user/transaksi/show.php
git add app/Views/user/produk/show.php
git add app/Config/Routes.php
git add docs/features/midtrans-payment-methods.md
git add docs/README.md

# Commit
git commit -m "feat(payment): add payment method selection page with Midtrans

- Add payment method selection page before checkout
- Support 7 methods: QRIS, GoPay, ShopeePay, Mandiri, BNI, BRI, Permata
- Restrict Snap UI to selected payment method via enabled_payments
- Auto-detect sandbox/production Snap.js URL from config
- Store payment_method and payment_channel from webhook
- Add payment_method and payment_channel columns to transaksi table"

# Push
git push origin ft-midtrans-payment-methods
```
