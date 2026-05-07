# Fix: Status Transaksi Tidak Berubah Setelah Bayar di Sandbox (Localhost)

## Problem

Pembayaran berhasil di Snap UI Midtrans sandbox, tapi status transaksi di aplikasi tetap `pending`.

## Root Cause

Di environment **localhost/development**, Midtrans tidak dapat mengirim webhook notification karena URL `localhost:8081/webhook/midtrans` tidak dapat diakses dari internet publik.

```
Snap bayar ✅
    → Midtrans coba kirim POST ke localhost:8081/webhook/midtrans
        → ❌ Connection refused / timeout
            → Status DB tidak pernah diupdate
```

Ini adalah keterbatasan fundamental development environment, bukan bug kode.

## Solution — Pull-based Status Check Fallback

Tambah endpoint yang **aktif mengambil status** dari Midtrans API setelah Snap callback, sebagai fallback ketika webhook tidak diterima.

```
Snap bayar ✅
    → onSuccess/onPending callback
        → GET /user/transaksi/{id}/cek-status  ← BARU
            → MidtransService::checkTransactionStatus()
                → GET api.sandbox.midtrans.com/v2/{order_id}/status
                    → Update DB ✅
                        → Aktifkan akses ✅
                            → Redirect dashboard ✅
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Services/MidtransService.php` | Tambah `checkTransactionStatus(string $orderId): array` |
| `app/Controllers/User/TransaksiController.php` | Tambah method `cekStatus(int $id)` |
| `app/Views/user/transaksi/show.php` | Update `onSuccess`/`onPending` Snap callback — panggil `cekStatus` sebelum redirect |
| `app/Config/Routes.php` | Tambah `GET user/transaksi/(:num)/cek-status` |

## Key Code

### MidtransService::checkTransactionStatus()

```php
public function checkTransactionStatus(string $orderId): array
{
    $baseUrl = $this->isProduction
        ? 'https://api.midtrans.com/v2/'
        : 'https://api.sandbox.midtrans.com/v2/';

    $url = $baseUrl . urlencode($orderId) . '/status';
    // GET dengan Basic Auth server_key
    // Return: array dari Midtrans (transaction_status, fraud_status, payment_type, dll)
}
```

### Snap.js onSuccess Callback (show.php)

```javascript
snap.pay(snapToken, {
    onSuccess: function () { cekDanRedirect(); },  // ← pull status dulu
    onPending: function () { cekDanRedirect(); },  // ← bukan langsung reload
    onError:   function () { alert('Gagal.'); },
    onClose:   function () { }
});

function cekDanRedirect() {
    fetch('/user/transaksi/{id}/cek-status')
        .then(r => r.json())
        .then(data => {
            if (data.redirect) window.location.href = data.redirect;
            else window.location.reload();
        });
}
```

### TransaksiController::cekStatus()

```php
// GET user/transaksi/{id}/cek-status
// 1. Cek status di DB — jika sudah final, return langsung
// 2. Jika masih pending → query Midtrans Status API
// 3. Update DB dengan status baru
// 4. Jika success → aktivasi akses user_produk
// 5. Return JSON { transaction_status, redirect }
```

## Behavior di Production

Di production dengan URL publik, **webhook tetap berjalan normal** dan menjadi sumber utama update status. `cekStatus` hanya berfungsi sebagai fallback tambahan — tidak ada konflik karena ada idempotency check di webhook:

```php
// WebhookController.php
if ($transaksi['status'] === 'success') {
    return $this->response->setStatusCode(200)->setBody('OK'); // skip
}
```

## Testing

1. Lakukan pembayaran di Snap sandbox
2. Setelah Snap menampilkan "Pembayaran Berhasil", aplikasi harus otomatis redirect ke dashboard
3. Cek DB: `SELECT status, payment_method FROM transaksi ORDER BY id DESC LIMIT 1;`
4. Cek `user_produk`: `SELECT * FROM user_produk ORDER BY id DESC LIMIT 1;`

## Git Workflow

```bash
git checkout -b fx-midtrans-status-localhost

git add app/Services/MidtransService.php
git add app/Controllers/User/TransaksiController.php
git add app/Views/user/transaksi/show.php
git add app/Config/Routes.php
git add docs/troubleshooting/midtrans-status-not-updating-localhost.md
git add docs/README.md

git commit -m "fix(payment): add pull-based status check fallback for localhost

Midtrans webhook cannot reach localhost in development.
Add GET user/transaksi/{id}/cek-status endpoint that queries
Midtrans Status API directly after Snap onSuccess/onPending callback.
Updates DB status and activates user access without relying on webhook."

git push origin fx-midtrans-status-localhost
```
