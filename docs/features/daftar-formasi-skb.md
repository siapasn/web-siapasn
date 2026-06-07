# Halaman Daftar Formasi SKB

## Overview

Menambahkan halaman baru `/user/formasi` untuk role user — menampilkan tabel lengkap semua formasi SKB yang tersedia, dikelompokkan per kategori formasi, diurutkan berdasarkan nama formasi. Kolom **Tryout SKB** menunjukkan status ketersediaan paket tryout per formasi.

| Status | Kondisi | Tampilan |
|---|---|---|
| ✓ Tersedia | Sudah ada produk aktif dengan tryout ter-mapping | Link hijau ke halaman produk |
| Requested | User sudah pernah request (pending/approved) | Badge warning kuning |
| Request Tryout | Belum ada produk | Tombol biru navy, buka modal form |

Data saat ini: **258 formasi** dari 14 kategori, 12 tersedia produk, 246 bisa di-request.

---

## Files Created / Modified

### Baru
| File | Keterangan |
|---|---|
| `app/Controllers/User/FormasiController.php` | Controller baru — query formasi + cek produk + cek request user |
| `app/Views/user/formasi/index.php` | View — tabel per kategori, modal request, live search |
| `app/Database/Seeds/AddFormasiMenu.php` | Seeder tambah menu ke `menu_mapping` |

### Dimodifikasi
| File | Perubahan |
|---|---|
| `app/Config/Routes.php` | Tambah `GET user/formasi` → `User\FormasiController::index` |

### Database (via seeder, sudah dijalankan)
- Tambah entry `menu_mapping`: role `user`, key `formasi`, label `Daftar Formasi`, icon `bi-briefcase`, url `user/formasi`, urutan **4**
- Urutan menu user final: Dashboard(1) → Tryout Event(2) → Paket Tryout(3) → **Daftar Formasi(4)** → Katalog Buku(5) → Transaksi(6) → Paket Saya(7) → Perangkingan(8)

---

## Controller Logic

```php
// 1. Ambil semua formasi aktif, urutkan: kategori.urutan ASC → formasi.nama ASC
$formasiRaw = $db->table('formasi f')
    ->select('f.id, f.nama, kf.nama AS kategori_nama, kf.icon AS kategori_icon, ...')
    ->join('kategori_formasi kf', ...)
    ->where('f.is_active', 1)
    ->orderBy('kf.urutan', 'ASC')
    ->orderBy('kf.nama', 'ASC')
    ->orderBy('f.nama', 'ASC')
    ->get()->getResultArray();

// 2. Cek formasi mana yang sudah punya produk aktif + tryout
$produkFormasiRows = $db->table('produk p')
    ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
    ->where('p.is_active', 1)
    ->where('p.formasi_id IS NOT NULL', null, false)  // CI4 syntax
    ->groupBy('p.formasi_id')
    ->having('COUNT(mt.id) >', 0)
    ->get()->getResultArray();

// 3. Cek request pending/approved user saat ini (untuk status "Requested")
$requestedFormasiIds = $db->table('request_formasi')
    ->where('user_id', $userId)
    ->whereIn('status', ['pending', 'approved'])
    ->get()->getResultArray();

// 4. Kelompokkan per kategori + enrich status
```

---

## View Features

- **Tabel per kategori** — header gradient biru dengan icon Bootstrap Icons
- **Kolom Tryout SKB**:
  - `✓ Tersedia` — link ke `/user/produk/{slug}`
  - `Requested` — badge kuning (sudah pernah request)
  - `Request Tryout` — tombol buka modal
- **Modal Request** — reuse route `POST user/request-formasi` yang sudah ada
- **Live search** — filter nama formasi real-time, card kategori otomatis tersembunyi jika kosong
- **Stat cards** — Total Formasi, Tryout Tersedia, Segera Hadir

---

## Deploy Notes

Jika deploy ke environment baru, jalankan seeder untuk menambah menu:

```bash
php spark db:seed AddFormasiMenu
```

---

## Testing Recommendations

1. Buka `/user/formasi` — pastikan tabel muncul per kategori, urut nama formasi
2. Formasi dengan produk → link "Tersedia" mengarah ke halaman produk yang benar
3. Klik "Request Tryout" → modal terbuka dengan nama formasi yang tepat
4. Submit request → redirect back dengan flash success, status berubah ke "Requested"
5. Request ulang formasi yang sama → flash error "sudah pernah request"
6. Live search → ketik nama formasi, kategori tanpa hasil tersembunyi
7. Pastikan menu "Daftar Formasi" muncul di sidebar user di posisi ke-4

---

## Git Workflow

```bash
git checkout -b ft-daftar-formasi-skb

git add app/Controllers/User/FormasiController.php
git add app/Views/user/formasi/index.php
git add app/Database/Seeds/AddFormasiMenu.php
git add app/Config/Routes.php
git add docs/features/daftar-formasi-skb.md
git add docs/README.md

git commit -m "feat(user): halaman daftar formasi SKB dengan status ketersediaan tryout

- Halaman /user/formasi: tabel 258 formasi dikelompokkan per 14 kategori
- Status per formasi: Tersedia (link produk), Requested (badge), Request Tryout (modal)
- Live search filter nama formasi, kategori tersembunyi jika tidak ada hasil
- Stat cards: total formasi, tersedia, segera hadir
- Modal request reuse route POST user/request-formasi yang sudah ada
- Menu 'Daftar Formasi' ditambahkan ke sidebar user (urutan 4)"

git push origin ft-daftar-formasi-skb
```
