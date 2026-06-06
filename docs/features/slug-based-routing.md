# Slug-Based Routing untuk Halaman Detail User

## Overview

Sebelumnya, halaman detail di sisi user menggunakan ID numerik di URL (contoh: `/user/produk/3`). Fitur ini mengganti semua URL detail menjadi berbasis **slug** yang human-readable dan SEO-friendly (contoh: `/user/produk/analis-hukum-ahli-pertama`).

Perubahan mencakup 4 halaman utama:
1. **Tryout Event Detail** — `/user/tryout-event/{slug}`
2. **User Produk Detail** — `/user/produk/{slug}`
3. **User Tryout Ranking/Leaderboard** — `/user/ranking/{slug}`
4. **Tryout Event Leaderboard** — `/user/tryout-event/{slug}/leaderboard`

Tombol **Bagikan** di semua halaman tersebut juga otomatis menggunakan URL slug.

---

## Files Modified / Created

### Baru
| File | Keterangan |
|---|---|
| `app/Helpers/slug_helper.php` | Helper `make_slug()` dan `make_unique_slug()` |
| `app/Database/Migrations/2026-06-06-000002_AddSlugToTables.php` | Migration idempotent — tambah kolom `slug` ke 3 tabel |
| `app/Database/Seeds/SlugSeeder.php` | Seeder isi slug untuk data lama |
| `app/Database/Seeds/MarkSlugMigration.php` | Tandai migration sebagai sudah dijalankan |

### Models
| File | Perubahan |
|---|---|
| `app/Models/TryoutEventModel.php` | Tambah `slug` ke `$allowedFields`, tambah `findBySlug()` |
| `app/Models/TryoutModel.php` | Tambah `slug` ke `$allowedFields`, tambah `findBySlug()` |
| `app/Models/ProdukModel.php` | Tambah `slug` ke `$allowedFields`, tambah `findBySlug()` |

### Admin Controllers (auto-generate slug)
| File | Perubahan |
|---|---|
| `app/Controllers/Admin/TryoutEventController.php` | `store()` dan `update()` generate slug via `make_unique_slug()` |
| `app/Controllers/Admin/Master/TryoutController.php` | idem |
| `app/Controllers/Admin/Master/ProdukController.php` | idem |

### User Controllers (lookup by slug)
| File | Perubahan |
|---|---|
| `app/Controllers/User/ProdukController.php` | `show(string $slug)` — lookup by slug + 301 redirect dari ID lama |
| `app/Controllers/User/TryoutEventController.php` | `detail/leaderboard/daftar/mulai` — semua pakai slug |
| `app/Controllers/User/RankingController.php` | `leaderboard(string $slug)` — lookup by slug |
| `app/Controllers/User/TransaksiController.php` | Redirect ke URL slug setelah cek akses |

### Routes
| File | Perubahan |
|---|---|
| `app/Config/Routes.php` | Ganti `(:num)` → `(:segment)` untuk 3 route detail user |

### Views
| File | Perubahan |
|---|---|
| `app/Views/user/tryout-event/index.php` | Link detail & share button pakai slug |
| `app/Views/user/tryout-event/detail.php` | Share button & link leaderboard pakai slug |
| `app/Views/user/tryout-event/leaderboard.php` | Back link pakai slug |
| `app/Views/user/tryout-event/kalender.php` | Link detail pakai slug |
| `app/Views/user/produk/index.php` | Link detail & share button pakai slug |
| `app/Views/user/produk/show.php` | Share button pakai slug |
| `app/Views/user/ranking/index.php` | Link leaderboard pakai slug |
| `app/Views/user/ranking/leaderboard.php` | Share button baru (ditambahkan) + URL pakai slug |
| `app/Views/user/dashboard.php` | Link event & produk pakai slug |
| `app/Views/user/tryout/index.php` | Link "Detail Produk" pakai slug |
| `app/Views/user/transaksi/pilih-metode.php` | Back link pakai slug |
| `app/Views/home/index.php` | Link login redirect ke slug (landing page) |

---

## Database Changes

Kolom `slug VARCHAR(255) NULL UNIQUE` ditambahkan ke:
- `tryout_event`
- `tryout`
- `produk`

Data lama otomatis diisi slug saat seeder dijalankan. Contoh hasil:

| Nama | Slug |
|---|---|
| Tryout Gratis CAT SKD CPNS 2026/2027 Vol 1 | `tryout-gratis-cat-skd-cpns-20262027-vol-1` |
| Prediksi CAT SKD CPNS 2026/2027 Vol. 1 | `prediksi-cat-skd-cpns-20262027-vol-1` |
| Analis Hukum Ahli Pertama | `analis-hukum-ahli-pertama` |

Slug bersifat **unique** — jika ada nama yang sama, suffix `-2`, `-3`, dst. ditambahkan otomatis.

---

## Key Design Decisions

### Backward Compatibility (301 Redirect)
Semua controller user mendukung akses via ID lama. Jika URL mengandung angka (`ctype_digit()`), akan di-redirect 301 ke URL slug yang baru:

```php
// Di semua controller user
$produk = ctype_digit($slug)
    ? $this->produkModel->find((int) $slug)
    : $this->produkModel->findBySlug($slug);

// Redirect permanen jika masih pakai ID
if (ctype_digit($slug) && ! empty($produk['slug'])) {
    return redirect()->to(base_url('user/produk/' . $produk['slug']), 301);
}
```

### Slug Generation
Admin controller memanggil `make_unique_slug()` saat `store()` dan `update()`:

```php
helper('slug');
$slug = make_unique_slug('produk', $this->request->getPost('nama'), $id);
$this->produkModel->update($id, ['slug' => $slug, ...]);
```

Saat nama produk diubah, slug ikut diperbarui otomatis.

### Route Order (Tryout Event)
Route lebih spesifik harus di atas wildcard:

```php
$routes->get('tryout-event/kalender', 'User\TryoutEventController::kalender');  // literal dulu
$routes->get('tryout-event/(:segment)', 'User\TryoutEventController::detail/$1');  // wildcard belakang
$routes->get('tryout-event/(:segment)/leaderboard', '...::leaderboard/$1');  // 3-segmen aman
```

### POST Routes Tetap Pakai `(:num)`
Route `daftar` dan `mulai` adalah POST dengan `eventId` dari hidden form — tetap `(:num)` karena form submit tidak pakai slug:

```php
$routes->post('tryout-event/(:num)/daftar', 'User\TryoutEventController::daftar/$1');
$routes->post('tryout-event/(:num)/mulai',  'User\TryoutEventController::mulai/$1');
```

Di dalam controller, redirect setelah POST sudah menggunakan `$event['slug']`.

---

## Cara Deploy ke Environment Baru

Jika database belum punya kolom slug, jalankan seeder:

```bash
php spark db:seed SlugSeeder
```

Seeder ini:
1. Tambah kolom `slug` ke tabel `tryout_event`, `tryout`, `produk` (idempotent — cek dulu sebelum ALTER)
2. Tambah UNIQUE INDEX
3. Isi slug untuk semua baris yang belum punya slug

---

## Testing Recommendations

1. **Akses halaman detail via slug baru** — pastikan halaman tampil normal
2. **Akses via ID lama** (contoh: `/user/produk/3`) — pastikan di-redirect 301 ke slug
3. **Tombol Bagikan** — cek URL yang di-share sudah berupa slug, bukan ID
4. **Tombol Daftar / Mulai Event** — form POST harus tetap berjalan normal
5. **Buat produk/tryout/event baru** dari admin — pastikan slug ter-generate otomatis
6. **Edit nama produk** — pastikan slug ikut berubah

```bash
# Quick smoke test via curl
curl -I http://localhost:8081/user/produk/1
# Harus: HTTP 302 → Location: /user/produk/{slug}

curl -I http://localhost:8081/user/produk/analis-hukum-ahli-pertama
# Harus: HTTP 200
```

---

## Git Workflow

```bash
# Buat feature branch
git checkout -b ft-slug-based-routing

# Stage semua perubahan
git add app/Helpers/slug_helper.php
git add app/Database/Migrations/2026-06-06-000002_AddSlugToTables.php
git add app/Database/Seeds/SlugSeeder.php
git add app/Database/Seeds/MarkSlugMigration.php
git add app/Models/TryoutEventModel.php
git add app/Models/TryoutModel.php
git add app/Models/ProdukModel.php
git add app/Controllers/Admin/TryoutEventController.php
git add app/Controllers/Admin/Master/TryoutController.php
git add app/Controllers/Admin/Master/ProdukController.php
git add app/Controllers/User/ProdukController.php
git add app/Controllers/User/TryoutEventController.php
git add app/Controllers/User/RankingController.php
git add app/Controllers/User/TransaksiController.php
git add app/Config/Routes.php
git add app/Views/user/tryout-event/index.php
git add app/Views/user/tryout-event/detail.php
git add app/Views/user/tryout-event/leaderboard.php
git add app/Views/user/tryout-event/kalender.php
git add app/Views/user/produk/index.php
git add app/Views/user/produk/show.php
git add app/Views/user/ranking/index.php
git add app/Views/user/ranking/leaderboard.php
git add app/Views/user/dashboard.php
git add app/Views/user/tryout/index.php
git add app/Views/user/transaksi/pilih-metode.php
git add app/Views/home/index.php
git add docs/features/slug-based-routing.md
git add docs/README.md

# Commit
git commit -m "feat(routing): slug-based URLs untuk halaman detail tryout, produk, dan ranking

- Tambah kolom slug ke tabel tryout_event, tryout, produk
- Auto-generate slug saat create/update dari admin
- User controller lookup by slug dengan backward-compat 301 redirect dari ID
- Update semua views dan share button menggunakan slug
- Tambah slug_helper dengan make_slug() dan make_unique_slug()"

# Push
git push origin ft-slug-based-routing
```
