# SiapASN Simulation Center — Dokumentasi

Dokumentasi teknis untuk project **bimbel-cpns** (SiapASN Simulation Center), platform tryout CPNS berbasis web dengan CodeIgniter 4.

---

## Kategori Dokumentasi

- [`docs/features/`](./features/) — Fitur baru dan kapabilitas sistem
- [`docs/optimizations/`](./optimizations/) — Peningkatan performa
- [`docs/guides/`](./guides/) — Panduan penggunaan dan tutorial
- [`docs/api/`](./api/) — Dokumentasi endpoint API
- [`docs/architecture/`](./architecture/) — Keputusan arsitektur dan pola desain
- [`docs/troubleshooting/`](./troubleshooting/) — Masalah umum dan solusinya

---

## API

| Dokumen | Deskripsi |
|---|---|
| [cron-sync-payment-status.md](./api/cron-sync-payment-status.md) | `GET /cron/sync-payment-status` — sinkronisasi status pembayaran pending ke Midtrans, untuk cron job |

---

## Troubleshooting

| Dokumen | Deskripsi |
|---|---|
| [isedit-undefined-page-header-section.md](./troubleshooting/isedit-undefined-page-header-section.md) | `$isEdit` undefined di form admin — deklarasi harus sebelum `section('page_header')` |
| [csp-midtrans-snap-blocked.md](./troubleshooting/csp-midtrans-snap-blocked.md) | CSP memblokir Midtrans Snap — fix header di BaseController dan .htaccess |
| [midtrans-status-not-updating-localhost.md](./troubleshooting/midtrans-status-not-updating-localhost.md) | Status transaksi tidak berubah di localhost — pull-based fallback via Midtrans Status API |
| [cart-checkout-404-route-mismatch.md](./troubleshooting/cart-checkout-404-route-mismatch.md) | Fix 404 saat checkout dari keranjang — redirect ke POST-only route |

---

## Features

| Dokumen | Deskripsi |
|---|---|
| [user-profil-update.md](./features/user-profil-update.md) | Halaman profil user — update nama, no HP, dan password dengan validasi server-side + UX indikator kekuatan password |
| [filter-pencarian-paket-dan-redirect-mulai-tryout.md](./features/filter-pencarian-paket-dan-redirect-mulai-tryout.md) | Filter pencarian real-time nama paket di `/user/produk` & `/user/tryout`; tombol "Mulai Tryout" redirect ke halaman sesi |
| [passing-grade-per-sub-kategori.md](./features/passing-grade-per-sub-kategori.md) | Passing grade per sub kategori + scoring TKP (SCORE) berdasarkan nilai 1-5 |
| [midtrans-payment-methods.md](./features/midtrans-payment-methods.md) | Halaman pilih metode pembayaran (QRIS, GoPay, ShopeePay, Mandiri, BNI, BRI, Permata) sebelum checkout via Midtrans Snap |

---

## Recent Updates

| Tanggal | Kategori | Deskripsi |
|---|---|---|
| 2026-05-16 | Feature | Halaman profil user — update nama, no HP, dan password dengan validasi + indikator kekuatan password |
| 2026-05-16 | Feature | Filter pencarian real-time nama paket di `/user/produk` & `/user/tryout`; redirect "Mulai Tryout" ke halaman sesi |
|---|---|---|
| 2026-05-07 | Fix | Dashboard user — thumbnail, info sesi/soal/durasi pada paket aktif |
| 2026-05-07 | Fix | Tryout user — thumbnail, badge info, modal detail paket pada daftar tryout |
| 2026-05-07 | Fix | Katalog produk — promosi & harga promo tidak ditampilkan untuk produk yang sudah dibeli |
| 2026-05-07 | Fix | Transaksi beli — diskon promosi aktif tidak diterapkan ke harga_bayar |
| 2026-05-07 | Fix | Keranjang — produk tidak dihapus setelah pembayaran sukses (webhook & cekStatus) |
| 2026-05-07 | Feature | Halaman tryout — redesign dari list per tryout menjadi group per paket dengan collapse sesi |
| 2026-05-07 | Fix | Master Tryout — hapus field jumlah soal, hitung dari mapping_soal; sidebar admin bisa scroll |
| 2026-05-07 | Fix | User pages — jumlah soal dihitung dari mapping_soal di Dashboard, Produk, Tryout |
| 2026-05-07 | Fix | Halaman tryout — redesign grid 4 kolom seperti katalog produk, modal daftar sesi |
| 2026-05-07 | Fix | Menu "Tryout Saya" → "Paket Saya"; tombol Mulai Tryout hanya di modal popup |
| 2026-05-07 | Fix | Tryout index — `$adaBerlangsung` undefined, ganti dengan cek `jumlah_selesai` |
| 2026-05-07 | Fix | Katalog produk — paket sudah dibeli urutan belakang; detail produk tombol Mulai di list sesi |
| 2026-05-07 | Feature | Passing grade per sub kategori + scoring TKP (SCORE) nilai 1-5 di hasil tryout |
| 2026-05-07 | Fix | TryoutModel::getSoal() — hapus kolom `file_id` yang tidak ada, tambah `nilai_a-e` |
| 2026-05-07 | Fix | Produk detail — sembunyikan harga jika sudah beli; voucher & promosi 1x per user |
| 2026-05-07 | Fix | `$isEdit` undefined di semua form admin — pindah deklarasi ke sebelum section page_header (completed) |
| 2026-05-07 | API | Cron endpoint `GET /cron/sync-payment-status` — sync status pembayaran pending ke Midtrans |
| 2026-05-07 | Fix | CSP memblokir Midtrans Snap — tambah CSP header di BaseController dan .htaccess |
| 2026-05-07 | Fix | Midtrans status tidak update di localhost — tambah pull-based status check fallback |
| 2026-05-07 | Fix | Cart checkout 404 — redirect ke POST-only route, diperbaiki ke `pilih-metode` |
| 2026-05-07 | Feature | Midtrans payment method selection — 7 metode pembayaran dengan UI pilih metode sebelum Snap |

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | CodeIgniter 4.7 |
| PHP | 8.2 |
| Database | MySQL |
| Cache | Redis (via Predis) |
| Payment | Midtrans Snap |
| Frontend | Bootstrap 5.3 + Bootstrap Icons |
| Editor | Summernote |
| Excel | PhpSpreadsheet |
| PDF | DomPDF |
