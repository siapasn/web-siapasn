# Sitemap Dinamis & SEO Enhancement

## Overview

Fitur ini meningkatkan kemampuan mesin pencari (Google, Bing, dll.) untuk menemukan dan mengindeks konten halaman publik SiapASN — terutama **produk highlight** dan **tryout event aktif** menggunakan slug URL yang bersih.

Tiga lapisan SEO ditambahkan:
1. **`sitemap.xml` dinamis** — digenerate dari database, mencakup semua produk aktif dan event
2. **`robots.txt` diperbarui** — izinkan crawler ke `/user/produk/` dan `/user/tryout-event/`
3. **Meta tag SEO + JSON-LD** — di halaman detail produk, detail event, dan landing page

---

## Files Modified / Created

| File | Perubahan |
|---|---|
| `app/Controllers/SitemapController.php` | Rewrite total — tambah produk highlight, produk biasa, tryout event aktif |
| `public/robots.txt` | Tambah `Allow: /user/produk/` dan `Allow: /user/tryout-event/` |
| `app/Views/layouts/main.php` | Tambah section `seo_title` dan `seo_meta` yang bisa di-override per view |
| `app/Views/user/produk/show.php` | Tambah section `seo_title`, `seo_meta` (og:*, twitter:card), JSON-LD `Product` |
| `app/Views/user/tryout-event/detail.php` | Tambah section `seo_title`, `seo_meta` (og:*, twitter:card) |
| `app/Views/home/index.php` | Tambah JSON-LD `ItemList` untuk produk highlight (schema.org) |

---

## Sitemap Structure

URL `GET /sitemap.xml` menghasilkan XML dengan 4 kelompok entry:

| Kelompok | Priority | Changefreq | Keterangan |
|---|---|---|---|
| Halaman statis (home, syarat, privasi, kontak) | 1.0 – 0.4 | daily/monthly | Hardcoded |
| `web_content` tipe `halaman` yang aktif | 0.5 | monthly | Dinamis dari DB |
| **Produk `is_highlight=1`** | **0.9** | weekly | Prioritas tertinggi untuk SEO |
| Produk biasa (`is_highlight=0`) | 0.7 | weekly | Semua produk aktif yang punya tryout |
| Tryout event aktif (belum tutup) | 0.8 | daily | Hanya event yang masih berlangsung |

Contoh output:
```xml
<url>
    <loc>https://siapasnsimulationcenter.id/user/produk/prediksi-cat-skd-cpns-20262027-vol-1</loc>
    <lastmod>2026-06-07</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
</url>
<url>
    <loc>https://siapasnsimulationcenter.id/user/tryout-event/tryout-gratis-cat-skd-cpns-20262027-vol-1</loc>
    <lastmod>2026-06-07</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
</url>
```

Cache header `Cache-Control: public, max-age=3600` (1 jam) ditambahkan untuk efisiensi crawler.

---

## robots.txt

Sebelum:
```
Disallow: /user/
```

Sesudah:
```
Allow: /user/produk/
Allow: /user/tryout-event/

Disallow: /user/dashboard
Disallow: /user/transaksi
Disallow: /user/tryout/
# ... dst
```

Tanpa perubahan ini, Google **tidak akan mengindeks** URL produk dan event meski sudah ada di sitemap.

---

## Meta Tag SEO (Per Halaman)

### Layout Override Pattern

Layout `main.php` kini mendukung section yang bisa di-override:

```php
<!-- layouts/main.php -->
<title><?= $this->renderSection('seo_title') ?: 'SiapASN Simulation Center' ?></title>
<?= $this->renderSection('seo_meta') ?: '<meta name="robots" content="noindex, nofollow">' ?>
```

Default **noindex** untuk semua halaman user. Override di view spesifik:

```php
<?= $this->section('seo_title') ?>Analis Hukum Ahli Pertama — Paket Tryout CPNS | SiapASN<?= $this->endSection() ?>

<?= $this->section('seo_meta') ?>
<meta name="robots" content="index, follow">
<meta name="description" content="Paket tryout CPNS Analis Hukum Ahli Pertama...">
<meta name="keywords" content="tryout CPNS, Analis Hukum, ...">
<link rel="canonical" href="https://siapasnsimulationcenter.id/user/produk/analis-hukum-ahli-pertama">
<meta property="og:type" content="product">
<meta property="og:title" content="...">
<meta property="og:image" content="...">
<meta name="twitter:card" content="summary_large_image">
<?= $this->endSection() ?>
```

---

## JSON-LD Structured Data

### 1. Landing Page — `ItemList` (produk highlight)

Google bisa menampilkan **rich snippet daftar produk** di hasil pencarian:

```json
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Paket Tryout CPNS Unggulan — SiapASN",
    "numberOfItems": 8,
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "item": {
                "@type": "Product",
                "name": "Prediksi CAT SKD CPNS 2026/2027 Vol. 1",
                "url": "https://siapasnsimulationcenter.id/user/produk/prediksi-cat-skd-cpns-20262027-vol-1",
                "offers": { "@type": "Offer", "priceCurrency": "IDR", "price": "75000" }
            }
        }
    ]
}
```

### 2. Halaman Detail Produk — `Product`

Google bisa menampilkan **harga, ketersediaan, dan gambar** langsung di SERP:

```json
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Analis Hukum Ahli Pertama",
    "offers": {
        "@type": "Offer",
        "priceCurrency": "IDR",
        "price": "75000",
        "availability": "https://schema.org/InStock"
    }
}
```

---

## Cara Submit Sitemap ke Google

1. Buka [Google Search Console](https://search.google.com/search-console)
2. Pilih property `siapasnsimulationcenter.id`
3. Klik **Sitemaps** di sidebar kiri
4. Masukkan URL: `sitemap.xml`
5. Klik **Submit**

Cek juga validasi di: `https://siapasnsimulationcenter.id/sitemap.xml`

---

## Testing Recommendations

```bash
# 1. Cek output sitemap (harus XML valid)
curl https://siapasnsimulationcenter.id/sitemap.xml

# 2. Cek robots.txt
curl https://siapasnsimulationcenter.id/robots.txt

# 3. Validasi sitemap online
# https://www.xml-sitemaps.com/validate-xml-sitemap.html

# 4. Cek rich snippet produk
# https://search.google.com/test/rich-results

# 5. Cek meta tag di halaman detail produk
curl -s https://siapasnsimulationcenter.id/user/produk/prediksi-cat-skd-cpns-20262027-vol-1 | grep -E "og:|description|canonical"
```

Pastikan:
- Produk dengan `is_highlight = 1` muncul di sitemap dengan priority `0.9`
- Produk dengan `is_highlight = 0` muncul dengan priority `0.7`
- Event aktif muncul dengan priority `0.8`
- Halaman detail produk punya `<meta name="robots" content="index, follow">`
- URL canonical sesuai slug

---

## Git Workflow

```bash
git checkout -b ft-sitemap-seo

git add app/Controllers/SitemapController.php
git add public/robots.txt
git add app/Views/layouts/main.php
git add app/Views/user/produk/show.php
git add app/Views/user/tryout-event/detail.php
git add app/Views/home/index.php
git add docs/features/sitemap-seo.md
git add docs/README.md

git commit -m "feat(seo): sitemap dinamis produk highlight, meta OG, JSON-LD structured data

- SitemapController: tambah produk highlight (priority 0.9), produk biasa (0.7),
  event aktif (0.8) dengan slug URL
- robots.txt: Allow /user/produk/ dan /user/tryout-event/ untuk crawler
- layouts/main.php: section seo_title & seo_meta bisa di-override per view
- produk/show.php & tryout-event/detail.php: meta robots index, OG tags, canonical
- home/index.php: JSON-LD ItemList produk highlight untuk rich snippet Google"

git push origin ft-sitemap-seo
```
