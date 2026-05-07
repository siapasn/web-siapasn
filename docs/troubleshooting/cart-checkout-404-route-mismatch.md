# Fix: Cart Checkout 404 — Route Mismatch GET vs POST

## Problem

Klik tombol "Lanjut ke Pembayaran" di halaman `/user/cart` menghasilkan error:

```
404 — Can't find a route for 'GET: user/transaksi/beli/1'.
```

## Root Cause

`CartController::checkout()` melakukan redirect ke `user/transaksi/beli/{id}`, namun route tersebut hanya terdaftar sebagai `POST`:

```php
// Routes.php — hanya POST
$routes->post('transaksi/beli/(:num)', 'User\TransaksiController::beli/$1');
```

Redirect dari controller menghasilkan request `GET`, sehingga CI4 tidak menemukan route yang cocok dan melempar 404.

Selain itu, alur ini melewati halaman pemilihan metode pembayaran yang seharusnya muncul sebelum checkout.

## Fix

**File:** `app/Controllers/User/CartController.php`

Ubah redirect di method `checkout()` dari:
```php
// ❌ SALAH — route ini hanya POST
return redirect()->to(base_url('user/transaksi/beli/' . $produkId));
```

Menjadi:
```php
// ✅ BENAR — arahkan ke halaman pilih metode (GET route)
return redirect()->to(base_url('user/transaksi/pilih-metode/' . $produkId));
```

## Alur yang Benar

```
POST /user/cart/checkout
    → redirect GET /user/transaksi/pilih-metode/{produkId}
        → user pilih metode pembayaran
            → POST /user/transaksi/beli/{produkId}
                → GET /user/transaksi/{id}  (Snap popup)
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/User/CartController.php` | `checkout()` redirect ke `pilih-metode` bukan `beli` |

## Git Workflow

```bash
git checkout -b fx-cart-checkout-route

git add app/Controllers/User/CartController.php
git add docs/troubleshooting/cart-checkout-404-route-mismatch.md
git add docs/README.md

git commit -m "fix(cart): redirect checkout to pilih-metode instead of beli

CartController::checkout() was redirecting to POST-only route
user/transaksi/beli/{id} via GET, causing 404.
Now redirects to user/transaksi/pilih-metode/{id} (GET route)
so user selects payment method before proceeding."

git push origin fx-cart-checkout-route
```
