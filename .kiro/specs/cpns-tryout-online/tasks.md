# Tasks: CPNS Tryout Online

## Task List

- [x] 1. Setup Proyek & Infrastruktur Dasar
  - [x] 1.1 Inisialisasi proyek CodeIgniter 4 dan konfigurasi environment (.env)
  - [x] 1.2 Buat migrasi database untuk semua tabel (users, kategori, soal, tryout, produk, transaksi, dll.)
  - [x] 1.3 Buat database seeder untuk data awal (Super Admin, kategori CPNS, menu mapping default)
  - [x] 1.4 Konfigurasi Auth Filter dan Role Filter di `app/Config/Filters.php`
  - [x] 1.5 Buat layout view utama (main.php, admin.php, auth.php) dengan Bootstrap 5
  - [x] 1.6 Setup PHPUnit dan eris/eris untuk testing

- [x] 2. Autentikasi dan Manajemen Akun
  - [x] 2.1 Buat `UserModel` dengan method findByEmail, incrementLoginAttempts, lockAccount
  - [x] 2.2 Buat `AuthController` dengan action: register, login, logout, resetPassword
  - [x] 2.3 Implementasi registrasi: validasi input, hash bcrypt, kirim email verifikasi
  - [x] 2.4 Implementasi login: verifikasi kredensial, cek lockout, buat sesi
  - [x] 2.5 Implementasi logout: hapus sesi
  - [x] 2.6 Implementasi reset password: generate token, kirim email, validasi token 60 menit
  - [x] 2.7 Buat `EmailService` untuk pengiriman email (verifikasi, reset password, konfirmasi pembelian)
  - [x] 2.8 Tulis property tests untuk Property 1, 2, 3, 4, 5 (registrasi, login, logout, lockout)

- [x] 3. Dashboard User
  - [x] 3.1 Buat `User/DashboardController` dengan data: paket aktif, 5 riwayat tryout, statistik nilai
  - [x] 3.2 Buat view dashboard user dengan stats card dan tabel riwayat
  - [x] 3.3 Tulis unit test untuk tampilan dashboard (example test)

- [x] 4. Katalog Produk dan Pembelian
  - [x] 4.1 Buat `ProdukModel` dan `TransaksiModel`
  - [x] 4.2 Buat `User/ProdukController` untuk menampilkan katalog produk aktif
  - [x] 4.3 Buat `VoucherService` untuk validasi dan penerapan diskon voucher
  - [x] 4.4 Buat `MidtransService` dengan method createSnapToken dan verifyWebhookSignature
  - [x] 4.5 Buat `User/TransaksiController` untuk membuat transaksi dan menampilkan riwayat
  - [x] 4.6 Implementasi webhook handler di `WebhookController`: verifikasi signature, update status, aktivasi akses
  - [x] 4.7 Buat `UserProdukModel` untuk manajemen akses user ke produk
  - [x] 4.8 Tulis property tests untuk Property 6, 7, 8, 9, 10, 11 (transaksi, webhook, voucher, filter)

- [x] 5. Pelaksanaan Tryout Online
  - [x] 5.1 Buat `SesiTryoutModel` dan `JawabanUserModel`
  - [x] 5.2 Buat `User/TryoutController` dengan action: index, mulai, start, jawab, selesai
  - [x] 5.3 Implementasi cek akses: user harus memiliki produk aktif yang berisi tryout tersebut
  - [x] 5.4 Implementasi auto-save jawaban via AJAX saat berpindah soal
  - [x] 5.5 Implementasi countdown timer di frontend (JavaScript) dengan auto-submit saat waktu habis
  - [x] 5.6 Implementasi pencegahan sesi duplikat (cek sesi selesai sebelum mulai baru)
  - [x] 5.7 Tulis property tests untuk Property 12, 13 (auto-save jawaban, sesi tidak dapat diulang)

- [x] 6. Hasil dan Pembahasan Tryout
  - [x] 6.1 Buat `TryoutScoringService` untuk menghitung skor total, per kategori, dan peringkat
  - [x] 6.2 Buat `HasilTryoutModel` untuk menyimpan dan mengambil hasil
  - [x] 6.3 Buat view halaman hasil tryout: skor total, progress bar per kategori, peringkat
  - [x] 6.4 Buat view halaman pembahasan: daftar soal dengan jawaban user, jawaban benar, pembahasan
  - [x] 6.5 Tulis property tests untuk Property 14, 15, 16, 17 (kalkulasi skor, pembahasan, peringkat, persistensi)

- [x] 7. Dashboard Admin
  - [x] 7.1 Buat `Admin/DashboardController` dengan data: total user, transaksi hari ini, pendapatan bulan ini, sesi berlangsung
  - [x] 7.2 Buat view dashboard admin dengan Chart.js untuk grafik tren 30 hari dan tabel 10 transaksi terbaru

- [x] 8. Master Data — User, Kategori, Data File
  - [x] 8.1 Buat `Admin/Master/UserController` dengan CRUD akun user dan role
  - [x] 8.2 Buat `Admin/Master/KategoriController` dengan CRUD kategori dan sub-kategori
  - [x] 8.3 Buat `Admin/Master/DataFileController` dengan CRUD file pendukung (upload/delete)

- [x] 9. Master Data — Soal
  - [x] 9.1 Buat `Admin/Master/SoalController` dengan CRUD soal (pertanyaan, pilihan, kunci, pembahasan)
  - [x] 9.2 Implementasi fitur impor soal massal dari Excel/CSV dengan validasi baris per baris
  - [x] 9.3 Tulis property tests untuk Property (validasi file impor soal)

- [x] 10. Master Data — Tryout, Produk, Passing Grade
  - [x] 10.1 Buat `Admin/Master/TryoutController` dengan CRUD tryout (nama, durasi, jumlah soal)
  - [x] 10.2 Buat `Admin/Master/ProdukController` dengan CRUD produk; implementasi pencegahan hapus produk dengan transaksi aktif
  - [x] 10.3 Buat `Admin/Master/PassingGradeController` dengan CRUD passing grade per kategori/tryout
  - [x] 10.4 Tulis property test untuk Property 18 (produk dengan transaksi aktif tidak dapat dihapus)

- [x] 11. Mapping Soal dan Mapping Tryout
  - [x] 11.1 Buat `MappingSoalModel` dan `Admin/Mapping/MappingSoalController`
  - [x] 11.2 Implementasi antarmuka mapping soal ke tryout dengan pengaturan urutan
  - [x] 11.3 Buat `MappingTryoutModel` dan `Admin/Mapping/MappingTryoutController`
  - [x] 11.4 Implementasi antarmuka mapping tryout ke produk dengan pengaturan urutan
  - [x] 11.5 Tulis property tests untuk Property 19, 20 (duplikasi mapping, akses tryout setelah beli produk)

- [x] 12. Promosi dan Voucher
  - [x] 12.1 Buat `PromosiModel` dan `Admin/PromosiController` dengan CRUD promosi
  - [x] 12.2 Buat `VoucherModel` dan `Admin/VoucherController` dengan CRUD voucher
  - [x] 12.3 Implementasi auto-deactivate promosi kedaluwarsa (via middleware atau cron)
  - [x] 12.4 Implementasi auto-deactivate voucher saat batas penggunaan tercapai
  - [x] 12.5 Tulis property tests untuk Property 21, 22 (promosi kedaluwarsa, voucher batas penggunaan)

- [x] 13. Laporan Transaksi dan Tryout
  - [x] 13.1 Buat `Admin/LaporanController` dengan filter rentang tanggal, status, dan produk
  - [x] 13.2 Implementasi ekspor laporan ke Excel (PhpSpreadsheet) dan PDF (TCPDF atau DomPDF)
  - [x] 13.3 Buat view laporan dengan tabel DataTables dan ringkasan total pendapatan

- [x] 14. Menu Mapping
  - [x] 14.1 Buat `MenuMappingModel` dan `Admin/MenuMappingController`
  - [x] 14.2 Implementasi antarmuka konfigurasi visibilitas dan urutan menu per role
  - [x] 14.3 Implementasi preview menu sebelum disimpan
  - [x] 14.4 Pastikan perubahan menu mapping diterapkan langsung tanpa restart

- [x] 15. Master Aplikasi (Super Admin)
  - [x] 15.1 Buat `MasterAplikasiModel` dan `SuperAdmin/MasterAplikasiController`
  - [x] 15.2 Implementasi konfigurasi Midtrans (API key, client key, environment)
  - [x] 15.3 Implementasi konfigurasi email SMTP
  - [x] 15.4 Implementasi konfigurasi informasi umum aplikasi (nama, logo, deskripsi)
  - [x] 15.5 Implementasi konfigurasi kebijakan sesi (durasi timeout)
  - [x] 15.6 Validasi input konfigurasi sebelum disimpan

- [x] 16. Manajemen Super Admin
  - [x] 16.1 Implementasi manajemen akun (buat, ubah, nonaktifkan, hapus) untuk semua role
  - [x] 16.2 Implementasi pencabutan sesi aktif saat Admin dinonaktifkan
  - [x] 16.3 Buat `AuditLogModel` dan middleware untuk mencatat aktivitas Super Admin
  - [x] 16.4 Implementasi fitur backup dan restore database (hanya Super Admin)

- [x] 17. Keamanan dan Otorisasi
  - [x] 17.1 Pastikan semua route terproteksi menggunakan AuthFilter dan RoleFilter
  - [x] 17.2 Implementasi validasi input server-side di semua Controller (CI4 Validation)
  - [x] 17.3 Implementasi auto-logout sesi tidak aktif 60 menit
  - [x] 17.4 Konfigurasi HTTPS dan security headers
  - [x] 17.5 Tulis property tests untuk Property 23, 24, 25 (akses tanpa auth, akses lintas role, sesi timeout)
