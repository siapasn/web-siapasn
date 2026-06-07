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

## Guides

| Dokumen | Deskripsi |
|---|---|
| [datatables-standard-pattern.md](./guides/datatables-standard-pattern.md) | Pola standar DataTables di semua admin views — `section('scripts')`, `dom` dengan padding, CSS scoped, empty state |

---

## Optimizations

| Dokumen | Deskripsi |
|---|---|
| [blast-email-parallel-processing.md](./optimizations/blast-email-parallel-processing.md) | Blast email parallel processing via background CLI processes (mirip goroutine) |

---

## Troubleshooting

| Dokumen | Deskripsi |
|---|---|
| [csrf-regenerate-multi-form.md](./troubleshooting/csrf-regenerate-multi-form.md) | Tombol aksi di tabel gagal diam-diam — fix: `CSRF regenerate=false` + `redirect=true` |
| [blast-email-only-1-per-minute.md](./troubleshooting/blast-email-only-1-per-minute.md) | Blast email hanya kirim 1 per menit — fix: `set_time_limit(300)` + kurangi SMTP timeout |
| [dashboard-buy-button-ignores-launch-date.md](./troubleshooting/dashboard-buy-button-ignores-launch-date.md) | Tombol beli di dashboard rekomendasi mengabaikan launch_date — fix: tambah pengecekan config sebelum render tombol |
| [visitor-filter-not-recording.md](./troubleshooting/visitor-filter-not-recording.md) | Visitor filter tidak mencatat — fix: pindah tracking ke `BaseController::initController()` |
| [mapping-tryout-produk-tidak-muncul.md](./troubleshooting/mapping-tryout-produk-tidak-muncul.md) | Data produk tidak muncul di mapping tryout — kolom `jumlah_soal` sudah dihapus, harus dihitung dari `mapping_soal` |
| [csp-datatables-summernote-blocked.md](./troubleshooting/csp-datatables-summernote-blocked.md) | `tooltip is not a function` — CSP tidak whitelist `cdn.datatables.net` + jQuery harus di-load sebelum Bootstrap |
| [select2-jquery-not-defined-scripts-section.md](./troubleshooting/select2-jquery-not-defined-scripts-section.md) | `$ is not defined` pada Select2/jQuery — script view harus di `section('scripts')`, bukan `section('content')` |
| [isedit-undefined-page-header-section.md](./troubleshooting/isedit-undefined-page-header-section.md) | `$isEdit` undefined di form admin — deklarasi harus sebelum `section('page_header')` |
| [csp-midtrans-snap-blocked.md](./troubleshooting/csp-midtrans-snap-blocked.md) | CSP memblokir Midtrans Snap — fix header di BaseController dan .htaccess |
| [midtrans-status-not-updating-localhost.md](./troubleshooting/midtrans-status-not-updating-localhost.md) | Status transaksi tidak berubah di localhost — pull-based fallback via Midtrans Status API |
| [cart-checkout-404-route-mismatch.md](./troubleshooting/cart-checkout-404-route-mismatch.md) | Fix 404 saat checkout dari keranjang — redirect ke POST-only route |

---

## Features

| Dokumen | Deskripsi |
|---|---|
| [email-verification-required.md](./features/email-verification-required.md) | User nonaktif saat register, aktif setelah verifikasi email; tombol resend di halaman login |
| [daftar-formasi-skb.md](./features/daftar-formasi-skb.md) | Halaman daftar formasi SKB dengan status ketersediaan tryout per formasi, live search, modal request |
| [slug-based-routing.md](./features/slug-based-routing.md) | URL halaman detail produk, tryout event, dan ranking berbasis slug SEO-friendly (ganti dari ID numerik) |
| [sitemap-seo.md](./features/sitemap-seo.md) | Sitemap dinamis dengan produk highlight, event aktif, meta tag OG, JSON-LD structured data produk |
| [blast-email-single-expand.md](./features/blast-email-single-expand.md) | Blast email: expand all/subscribe ke individual records (1 row per penerima) untuk tracking per email |
| [share-button.md](./features/share-button.md) | Tombol Bagikan (WhatsApp, Telegram, Facebook, X, Copy Link) di produk, event, dan katalog buku (list + detail) |
| [ranking-access-control.md](./features/ranking-access-control.md) | Ranking & leaderboard hanya untuk user yang sudah mengerjakan tryout (termasuk event leaderboard) |
| [visitor-tracking.md](./features/visitor-tracking.md) | Tracking pengunjung harian unik (per IP/hari) + persentase perbandingan vs kemarin di dashboard admin |
| [soal-import-salin-antar-tryout.md](./features/soal-import-salin-antar-tryout.md) | Import soal format baru (tanpa sub_kategori) + fitur Salin Soal antar tryout |
| [soal-form-tipe-soal-dari-kategori.md](./features/soal-form-tipe-soal-dari-kategori.md) | Form soal — hapus sub kategori, tipe soal (SCORE/POINT) langsung dari kategori yang dipilih |
| [select2-kategori-passing-grade-form.md](./features/select2-kategori-passing-grade-form.md) | Select2 search pada form Kategori (parent dropdown) & Passing Grade (kategori dropdown); nilai minimum jadi opsional |
| [user-profil-update.md](./features/user-profil-update.md) | Halaman profil user — update nama, no HP, dan password dengan validasi server-side + UX indikator kekuatan password |
| [filter-pencarian-paket-dan-redirect-mulai-tryout.md](./features/filter-pencarian-paket-dan-redirect-mulai-tryout.md) | Filter pencarian real-time nama paket di `/user/produk` & `/user/tryout`; tombol "Mulai Tryout" redirect ke halaman sesi |
| [passing-grade-per-sub-kategori.md](./features/passing-grade-per-sub-kategori.md) | Passing grade per sub kategori + scoring TKP (SCORE) berdasarkan nilai 1-5 |
| [midtrans-payment-methods.md](./features/midtrans-payment-methods.md) | Halaman pilih metode pembayaran (QRIS, GoPay, ShopeePay, Mandiri, BNI, BRI, Permata) sebelum checkout via Midtrans Snap |

---

## Recent Updates

| Tanggal | Kategori | Deskripsi |
|---|---|---|
| 2026-06-07 | Fix | CSRF regenerate=true menyebabkan tombol aksi tabel kedua gagal diam-diam — fix regenerate=false |
| 2026-06-07 | Feature | Email verification required — user nonaktif saat register, aktif setelah klik link verifikasi |
| 2026-06-07 | Feature | Halaman daftar formasi SKB — 258 formasi per kategori, status tersedia/request tryout, live search |
| 2026-06-07 | Feature | Sitemap dinamis — produk highlight + event aktif + meta OG + JSON-LD structured data |
| 2026-06-07 | Feature | Slug-based routing — URL detail produk, tryout event, dan ranking berbasis slug SEO-friendly |
| 2026-06-06 | Perf | Blast email parallel processing — spawn background CLI processes (5 paralel) |
| 2026-06-06 | Perf | Blast email hanya kirim 1/menit — fix execution time + SMTP timeout |
| 2026-06-06 | Feature | Blast email: expand all/subscribe ke individual records untuk per-email tracking |
| 2026-06-06 | Feature | Tombol Bagikan (share) di halaman produk detail, event detail, dan katalog buku |
| 2026-06-05 | Fix | Tombol beli di dashboard rekomendasi mengabaikan launch_date — tambah pengecekan config |
| 2026-06-05 | Feature | Ranking access control — hanya user yang beli produk atau daftar event yang bisa lihat leaderboard |
| 2026-06-05 | Feature | Tracking pengunjung harian unik + persentase perbandingan vs kemarin di dashboard admin |
| 2026-06-05 | Fix | Visitor filter tidak mencatat — pindah tracking ke BaseController::initController() |
| 2026-05-21 | Feature | Import soal format baru (tanpa sub_kategori) + fitur Salin Soal antar tryout |
| 2026-05-21 | Guide | Pola standar DataTables — `section('scripts')`, `dom` padding, CSS scoped, diterapkan ke 14 halaman admin |
| 2026-05-21 | Feature | Form soal — hapus sub kategori, tipe soal langsung dari kategori yang dipilih |
| 2026-05-21 | Feature | Select2 search pada form Kategori & Passing Grade; nilai minimum passing grade jadi opsional |
| 2026-05-21 | Fix | `$ is not defined` — script view dipindah ke `section('scripts')` agar jQuery/Select2 ter-load lebih dulu |
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
