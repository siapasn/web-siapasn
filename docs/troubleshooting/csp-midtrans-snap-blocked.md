# Fix: Content Security Policy Memblokir Midtrans Snap

## Problem

Browser (Firefox/Chrome) menampilkan error CSP di console dan Snap popup tidak berfungsi normal:

```
Content-Security-Policy: memblokir eksekusi JavaScript (script-src) karena menyalahi direktif:
'self' — Tidak ada 'unsafe-eval'
```

Domain yang diblokir antara lain:
- `snap-assets.al-pc-id-b-cdn.gtflabs.io`
- `api.sandbox.midtrans.com`
- `pay.google.com`
- `gwk.gopayapi.com`
- `global.faro.katulampa.gopay.sh`

## Root Cause

Tidak ada header `Content-Security-Policy` yang dikonfigurasi di aplikasi, sehingga browser menerapkan policy default yang hanya mengizinkan `'self'`. Midtrans Snap.js membutuhkan:

1. **`unsafe-eval`** — Snap.js menggunakan `eval()` secara internal
2. **Domain eksternal** — CDN Snap, GoPay SDK, Google Pay, Faro monitoring

## Solution

Tambah CSP header di dua lapisan:

### Lapisan 1 — PHP (BaseController)
Paling reliable, tidak bergantung pada konfigurasi server:

```php
// app/Controllers/BaseController.php — initController()
$this->response->setHeader('Content-Security-Policy', $csp);
```

### Lapisan 2 — Apache (.htaccess)
Fallback jika `mod_headers` tersedia:

```apache
Header always set Content-Security-Policy "..."
```

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/BaseController.php` | Tambah CSP header di `initController()` |
| `public/.htaccess` | Tambah CSP header via Apache `mod_headers` |

## CSP Domains yang Diizinkan

| Direktif | Domain |
|---|---|
| `script-src` | `unsafe-inline`, `unsafe-eval`, midtrans.com, gtflabs.io, gopayapi.com, pay.google.com, cdn.jsdelivr.net, code.jquery.com |
| `style-src` | `unsafe-inline`, cdn.jsdelivr.net, fonts.googleapis.com, gtflabs.io |
| `font-src` | fonts.gstatic.com, cdn.jsdelivr.net, `data:` |
| `img-src` | `data:`, `blob:`, *.midtrans.com, *.gtflabs.io |
| `connect-src` | api.midtrans.com, *.gopayapi.com, faro.katulampa.gopay.sh |
| `frame-src` | app.midtrans.com, *.gtflabs.io |
| `worker-src` | `blob:` |
| `object-src` | `none` |

## Testing

1. Buka halaman detail transaksi dengan status `pending`
2. Klik "Bayar Sekarang" — Snap popup harus muncul tanpa error CSP di console
3. Verifikasi di DevTools → Console: tidak ada error merah CSP
4. Selesaikan pembayaran sandbox — status harus berubah ke `success`
