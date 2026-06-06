# Fitur: Tombol Share (Bagikan)

## Overview

Menambahkan tombol "Bagikan" di halaman detail produk, detail event tryout, dan katalog buku. Tombol menggunakan dropdown dengan opsi share ke berbagai platform sosial + copy link + Web Share API native.

## Halaman yang Ditambahkan

| Halaman | Posisi | Konten yang di-share |
|---------|--------|---------------------|
| `/user/produk` | Per card produk, di bawah tombol Keranjang/Beli | URL detail produk + nama paket |
| `/user/produk/:id` | Sebelah tombol "Kembali ke Katalog" | URL detail produk + nama paket |
| `/user/tryout-event` | Per card event, di samping tombol Detail | URL detail event + nama event |
| `/user/tryout-event/:id` | Sebelah tombol "Kembali" | URL detail event + nama event |
| `/user/katalog-buku` | Per card buku, di bawah tombol "Beli di Shopee" | URL Shopee buku atau URL halaman |

## Opsi Share

- WhatsApp
- Telegram
- Facebook
- X (Twitter)
- Salin Link (copy to clipboard dengan toast notification)
- Lainnya... (Web Share API native — di mobile membuka menu share OS)

## Files Created

| File | Fungsi |
|------|--------|
| `app/Views/partials/share-button.php` | Partial reusable untuk tombol share dropdown |

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Views/user/produk/index.php` | Include share button per card produk |
| `app/Views/user/produk/show.php` | Include share button di area tombol kembali |
| `app/Views/user/tryout-event/index.php` | Include share button per card event |
| `app/Views/user/tryout-event/detail.php` | Include share button di area tombol kembali |
| `app/Views/user/katalog-buku/index.php` | Include share button per card buku |

## Penggunaan Partial

```php
<?php
$shareTitle    = 'Judul Konten';
$shareUrl      = current_url();           // opsional, default current_url()
$shareText     = 'Deskripsi singkat';     // opsional
$shareBtnClass = 'btn-outline-secondary'; // opsional
$shareBtnSize  = 'sm';                    // opsional, default 'sm'
echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText', 'shareBtnClass', 'shareBtnSize'));
?>
```

## Variabel Partial

| Variable | Required | Default | Keterangan |
|----------|----------|---------|------------|
| `$shareTitle` | Ya | — | Judul yang di-share |
| `$shareUrl` | Tidak | `current_url()` | URL yang di-share |
| `$shareText` | Tidak | `''` | Deskripsi tambahan |
| `$shareBtnClass` | Tidak | `btn-outline-secondary` | CSS class tombol |
| `$shareBtnSize` | Tidak | `sm` | Ukuran tombol Bootstrap |

## Testing

1. **Desktop** — klik "Bagikan" → dropdown muncul dengan semua opsi
2. **WhatsApp/Telegram/Facebook/Twitter** — membuka tab baru dengan URL pre-filled
3. **Salin Link** — toast "Link berhasil disalin!" muncul, link ada di clipboard
4. **Lainnya (mobile)** — menu share native OS muncul
5. **Lainnya (desktop tanpa Web Share API)** — fallback ke copy link

## Git Workflow

```bash
git checkout -b ft-share-button
git add app/Views/partials/share-button.php app/Views/user/produk/index.php app/Views/user/produk/show.php app/Views/user/tryout-event/index.php app/Views/user/tryout-event/detail.php app/Views/user/katalog-buku/index.php docs/features/share-button.md docs/README.md
git commit -m "feat(share): add share button to product, event, and book catalog pages (list + detail)"
git push -u origin ft-share-button
```
