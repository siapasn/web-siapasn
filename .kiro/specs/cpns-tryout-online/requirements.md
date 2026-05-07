# Requirements Document

## Introduction

Aplikasi web tryout online untuk persiapan tes CPNS (Calon Pegawai Negeri Sipil) berbasis PHP CodeIgniter 4 dengan UI Bootstrap 5. Aplikasi ini memungkinkan peserta untuk membeli paket tryout, mengerjakan soal secara online, dan melihat hasil beserta pembahasannya. Sistem dikelola oleh Admin dan Super Admin yang memiliki akses ke manajemen konten, transaksi, dan konfigurasi sistem.

## Glossary

- **System**: Aplikasi web tryout online CPNS
- **User**: Peserta terdaftar yang dapat membeli dan mengerjakan tryout
- **Admin**: Pengelola konten dan laporan dengan akses terbatas
- **Super_Admin**: Pengelola sistem dengan akses penuh ke seluruh fitur
- **Paket**: Lihat definisi Produk; istilah lama yang digantikan oleh Produk
- **Tryout**: Sesi ujian online dengan kumpulan soal dalam batas waktu tertentu
- **Soal**: Pertanyaan pilihan ganda beserta pilihan jawaban dan pembahasan
- **Transaksi**: Catatan pembelian paket oleh User melalui payment gateway
- **Midtrans**: Payment gateway pihak ketiga untuk memproses pembayaran
- **Voucher**: Kode diskon yang dapat diterapkan pada pembelian paket
- **Promosi**: Penawaran harga khusus untuk paket tertentu dalam periode waktu tertentu
- **Hasil_Tryout**: Rekap nilai dan jawaban User setelah menyelesaikan sesi tryout
- **Pembahasan**: Penjelasan jawaban benar untuk setiap soal setelah tryout selesai
- **Menu_Mapping**: Konfigurasi visibilitas dan urutan menu berdasarkan role
- **Token**: Kredensial autentikasi sesi yang diterbitkan setelah login berhasil
- **Produk**: Paket tryout yang dapat dibeli oleh User, berisi satu atau lebih Tryout (sebelumnya disebut Paket)
- **Kategori**: Pengelompokan soal berdasarkan bidang materi (misal: TWK, TIU, TKP)
- **Master_Data_File**: File pendukung konten seperti gambar atau dokumen yang digunakan dalam soal
- **Passing_Grade**: Nilai ambang batas minimum yang harus dicapai User pada setiap kategori atau keseluruhan Tryout
- **Mapping_Soal**: Proses pengaitan Soal ke dalam sesi Tryout tertentu
- **Mapping_Tryout**: Proses pengaitan Tryout ke dalam Produk tertentu
- **Master_Aplikasi**: Konfigurasi pengaturan global aplikasi yang dikelola oleh Super_Admin

---

## Requirements

### Requirement 1: Autentikasi dan Manajemen Akun

**User Story:** Sebagai pengguna sistem, saya ingin dapat mendaftar dan masuk ke aplikasi, sehingga saya dapat mengakses fitur sesuai role saya.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman registrasi untuk User baru dengan field nama lengkap, email, nomor telepon, dan password.
2. WHEN User mengirimkan form registrasi dengan data valid, THE System SHALL membuat akun baru dengan role User dan mengirimkan email verifikasi.
3. IF email yang digunakan sudah terdaftar, THEN THE System SHALL menampilkan pesan error "Email sudah terdaftar".
4. WHEN User mengirimkan form login dengan kredensial valid, THE System SHALL menerbitkan Token sesi dan mengarahkan User ke dashboard sesuai role.
5. IF User mengirimkan kredensial login yang salah sebanyak 5 kali berturut-turut, THEN THE System SHALL mengunci akun selama 15 menit dan menampilkan pesan informasi.
6. WHEN User meminta reset password, THE System SHALL mengirimkan tautan reset ke email terdaftar yang berlaku selama 60 menit.
7. WHEN User mengklik tombol logout, THE System SHALL menghapus Token sesi dan mengarahkan ke halaman login.
8. THE System SHALL mengenkripsi password menggunakan algoritma bcrypt sebelum disimpan ke database.

---

### Requirement 2: Dashboard User

**User Story:** Sebagai User, saya ingin melihat ringkasan aktivitas saya di dashboard, sehingga saya dapat memantau progress belajar saya.

#### Acceptance Criteria

1. WHEN User berhasil login, THE System SHALL menampilkan dashboard yang memuat ringkasan paket aktif, riwayat tryout terakhir, dan statistik nilai rata-rata.
2. THE System SHALL menampilkan daftar Paket yang telah dibeli User beserta status kedaluwarsa akses.
3. WHEN User belum memiliki Paket aktif, THE System SHALL menampilkan tombol ajakan untuk membeli Paket.
4. THE System SHALL menampilkan 5 riwayat Tryout terakhir beserta nilai yang diperoleh pada dashboard User.

---

### Requirement 3: Pembelian Paket Tryout

**User Story:** Sebagai User, saya ingin membeli paket tryout, sehingga saya dapat mengakses sesi tryout yang tersedia.

#### Acceptance Criteria

1. THE System SHALL menampilkan katalog Paket yang tersedia beserta nama, deskripsi, harga, dan jumlah sesi tryout yang termasuk.
2. WHEN User memilih Paket dan mengklik tombol beli, THE System SHALL membuat Transaksi dengan status "pending" dan mengarahkan User ke halaman pembayaran Midtrans.
3. WHEN Midtrans mengirimkan notifikasi pembayaran berhasil (webhook), THE System SHALL memperbarui status Transaksi menjadi "success" dan mengaktifkan akses User ke Paket yang dibeli.
4. IF Midtrans mengirimkan notifikasi pembayaran gagal atau kedaluwarsa, THEN THE System SHALL memperbarui status Transaksi menjadi "failed" atau "expired".
5. WHERE Voucher tersedia, THE System SHALL memvalidasi kode Voucher dan menerapkan diskon sebelum membuat Transaksi.
6. IF kode Voucher tidak valid atau sudah kedaluwarsa, THEN THE System SHALL menampilkan pesan error yang deskriptif tanpa memproses Transaksi.
7. WHEN Transaksi berhasil, THE System SHALL mengirimkan konfirmasi pembelian ke email User.
8. THE System SHALL memverifikasi signature key dari setiap notifikasi webhook Midtrans sebelum memproses perubahan status Transaksi.

---

### Requirement 4: Daftar Transaksi User

**User Story:** Sebagai User, saya ingin melihat riwayat transaksi pembelian saya, sehingga saya dapat memantau pengeluaran dan status pembayaran.

#### Acceptance Criteria

1. THE System SHALL menampilkan daftar seluruh Transaksi milik User yang sedang login, diurutkan berdasarkan tanggal terbaru.
2. THE System SHALL menampilkan informasi nama Paket, tanggal transaksi, jumlah pembayaran, dan status untuk setiap Transaksi.
3. WHEN User mengklik detail Transaksi dengan status "pending", THE System SHALL menampilkan tombol untuk melanjutkan pembayaran ke Midtrans.
4. THE System SHALL menyediakan filter Transaksi berdasarkan status (semua, pending, success, failed, expired).

---

### Requirement 5: Pelaksanaan Tryout Online

**User Story:** Sebagai User, saya ingin mengerjakan tryout online, sehingga saya dapat berlatih menghadapi tes CPNS.

#### Acceptance Criteria

1. WHEN User memilih sesi Tryout dari Paket yang aktif, THE System SHALL menampilkan halaman konfirmasi sebelum memulai sesi.
2. WHEN User mengkonfirmasi memulai Tryout, THE System SHALL mencatat waktu mulai, menampilkan soal pertama, dan memulai countdown timer sesuai durasi yang ditentukan.
3. WHILE sesi Tryout berlangsung, THE System SHALL menampilkan nomor soal, pilihan jawaban, navigasi antar soal, dan sisa waktu secara real-time.
4. WHILE sesi Tryout berlangsung, THE System SHALL menyimpan jawaban User secara otomatis setiap kali User berpindah soal.
5. WHEN waktu Tryout habis, THE System SHALL secara otomatis mengakhiri sesi dan menyimpan seluruh jawaban yang telah diisi.
6. WHEN User mengklik tombol selesai sebelum waktu habis, THE System SHALL menampilkan konfirmasi dan mengakhiri sesi setelah User mengkonfirmasi.
7. IF koneksi internet User terputus selama Tryout, THEN THE System SHALL menyimpan jawaban terakhir secara lokal dan melanjutkan sesi ketika koneksi pulih.
8. THE System SHALL mencegah User mengakses sesi Tryout yang sama lebih dari satu kali setelah sesi tersebut selesai.

---

### Requirement 6: Hasil dan Pembahasan Tryout

**User Story:** Sebagai User, saya ingin melihat hasil dan pembahasan tryout, sehingga saya dapat memahami kesalahan dan meningkatkan kemampuan saya.

#### Acceptance Criteria

1. WHEN sesi Tryout selesai, THE System SHALL menghitung dan menampilkan Hasil_Tryout yang mencakup skor total, jumlah jawaban benar, salah, dan tidak dijawab.
2. THE System SHALL menampilkan rincian nilai per sub-kategori soal pada halaman Hasil_Tryout.
3. WHEN User mengakses halaman pembahasan, THE System SHALL menampilkan setiap Soal beserta jawaban User, jawaban benar, dan Pembahasan.
4. THE System SHALL menampilkan peringkat User dibandingkan peserta lain yang mengerjakan Tryout yang sama pada halaman Hasil_Tryout.
5. THE System SHALL menyimpan seluruh Hasil_Tryout secara permanen sehingga User dapat mengaksesnya kembali kapan saja.

---

### Requirement 7: Dashboard Admin

**User Story:** Sebagai Admin, saya ingin melihat ringkasan data sistem di dashboard, sehingga saya dapat memantau aktivitas platform.

#### Acceptance Criteria

1. WHEN Admin berhasil login, THE System SHALL menampilkan dashboard yang memuat total User terdaftar, total Transaksi hari ini, total pendapatan bulan ini, dan jumlah sesi Tryout yang sedang berlangsung.
2. THE System SHALL menampilkan grafik tren transaksi dan pendapatan dalam 30 hari terakhir pada dashboard Admin.
3. THE System SHALL menampilkan 10 Transaksi terbaru pada dashboard Admin.

---

### Requirement 8: Master Data

**User Story:** Sebagai Admin, saya ingin mengelola master data aplikasi melalui sub-menu yang terstruktur, sehingga konten tryout selalu terkini dan relevan.

#### Acceptance Criteria

1. THE System SHALL menyediakan menu Master Data dengan delapan sub-menu: Master User, Master Kategori, Master Data File, Master Produk, Master Data Soal, Master Passing Grade, Master Tryout, dan Master Aplikasi.
2. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master User untuk mengelola akun pengguna beserta role-nya.
3. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Kategori untuk mengelola kategori dan sub-kategori Soal.
4. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Data File untuk mengelola file pendukung konten seperti gambar atau dokumen yang digunakan dalam Soal.
5. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Produk untuk mengelola data Produk beserta nama, deskripsi, harga, dan status aktif.
6. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Data Soal untuk mengelola Soal beserta pilihan jawaban, kunci jawaban, Kategori, dan Pembahasan.
7. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Passing Grade untuk mengelola nilai ambang batas minimum per Kategori atau per Tryout.
8. THE System SHALL menyediakan antarmuka CRUD pada sub-menu Master Tryout untuk mengelola sesi Tryout beserta nama, durasi, dan jumlah soal.
9. WHEN Admin menghapus Produk yang masih memiliki Transaksi aktif, THE System SHALL menampilkan peringatan konfirmasi dan mencegah penghapusan.
10. THE System SHALL menyediakan fitur impor Soal secara massal melalui file Excel atau CSV pada sub-menu Master Data Soal.
11. WHEN Admin mengunggah file impor Soal, THE System SHALL memvalidasi format dan isi file, lalu menampilkan ringkasan hasil impor beserta baris yang gagal diproses.

---

### Requirement 9: Promosi dan Voucher

**User Story:** Sebagai Admin, saya ingin mengelola promosi dan voucher diskon, sehingga dapat meningkatkan penjualan paket tryout.

#### Acceptance Criteria

1. THE System SHALL menyediakan antarmuka CRUD untuk mengelola Promosi dengan field nama, deskripsi, persentase atau nominal diskon, tanggal mulai, dan tanggal berakhir.
2. THE System SHALL menyediakan antarmuka CRUD untuk mengelola Voucher dengan field kode unik, jenis diskon (persentase atau nominal), nilai diskon, batas penggunaan, dan tanggal kedaluwarsa.
3. WHEN tanggal saat ini melewati tanggal berakhir Promosi, THE System SHALL secara otomatis menonaktifkan Promosi tersebut.
4. WHEN batas penggunaan Voucher tercapai, THE System SHALL secara otomatis menonaktifkan Voucher tersebut.
5. THE System SHALL mencatat riwayat penggunaan setiap Voucher beserta data User yang menggunakannya.
6. IF Admin mencoba membuat Voucher dengan kode yang sudah ada, THEN THE System SHALL menampilkan pesan error "Kode voucher sudah digunakan".

---

### Requirement 10: Laporan Transaksi dan Tryout

**User Story:** Sebagai Admin, saya ingin mengakses laporan transaksi dan tryout, sehingga dapat menganalisis performa platform.

#### Acceptance Criteria

1. THE System SHALL menyediakan laporan Transaksi yang dapat difilter berdasarkan rentang tanggal, status, dan Paket.
2. THE System SHALL menyediakan laporan Tryout yang menampilkan statistik pengerjaan per sesi, termasuk rata-rata nilai, nilai tertinggi, dan nilai terendah.
3. THE System SHALL menyediakan fitur ekspor laporan Transaksi dan Tryout ke format Excel dan PDF.
4. THE System SHALL menampilkan ringkasan total pendapatan berdasarkan periode yang dipilih pada laporan Transaksi.

---

### Requirement 11: Menu Mapping

**User Story:** Sebagai Admin, saya ingin mengkonfigurasi visibilitas menu berdasarkan role, sehingga setiap pengguna hanya melihat menu yang relevan.

#### Acceptance Criteria

1. THE System SHALL menyediakan antarmuka untuk mengatur visibilitas dan urutan menu untuk setiap role (User, Admin, Super_Admin).
2. WHEN Admin menyimpan konfigurasi Menu_Mapping, THE System SHALL menerapkan perubahan secara langsung tanpa memerlukan restart aplikasi.
3. THE System SHALL menampilkan preview tampilan menu sesuai konfigurasi yang sedang diedit sebelum disimpan.

---

### Requirement 14: Mapping Soal ke Tryout

**User Story:** Sebagai Admin, saya ingin memetakan soal-soal ke dalam sesi tryout tertentu, sehingga setiap tryout memiliki kumpulan soal yang sesuai.

#### Acceptance Criteria

1. THE System SHALL menyediakan menu Mapping dengan dua sub-menu: Mapping Soal dan Mapping Tryout.
2. THE System SHALL menyediakan antarmuka Mapping_Soal untuk mengaitkan Soal yang telah dibuat ke dalam sesi Tryout yang dipilih dari Master Tryout.
3. WHEN Admin membuka halaman Mapping Soal, THE System SHALL menampilkan daftar Tryout yang tersedia dan daftar Soal yang dapat dipilih untuk dikaitkan.
4. THE System SHALL memungkinkan Admin menambahkan lebih dari satu Soal ke dalam satu Tryout dalam satu sesi mapping.
5. THE System SHALL memungkinkan Admin mengatur urutan tampil Soal di dalam Tryout pada halaman Mapping Soal.
6. WHEN Admin menghapus mapping Soal dari Tryout, THE System SHALL menampilkan konfirmasi sebelum menghapus keterkaitan tersebut.
7. IF Soal yang akan di-mapping sudah terkait dengan Tryout yang sama, THEN THE System SHALL menampilkan pesan peringatan dan mencegah duplikasi mapping.
8. THE System SHALL menampilkan jumlah total Soal yang sudah di-mapping untuk setiap Tryout pada halaman Mapping Soal.

---

### Requirement 15: Mapping Tryout ke Produk

**User Story:** Sebagai Admin, saya ingin memetakan tryout ke dalam produk, sehingga setiap produk memiliki kumpulan tryout yang dapat diakses oleh pembeli.

#### Acceptance Criteria

1. THE System SHALL menyediakan antarmuka Mapping_Tryout untuk mengaitkan satu atau lebih Tryout dari Master Tryout ke dalam Produk yang dipilih dari Master Produk.
2. THE System SHALL mendukung relasi satu Produk memiliki banyak Tryout (one-to-many Produk → Tryout).
3. WHEN Admin membuka halaman Mapping Tryout, THE System SHALL menampilkan daftar Produk yang tersedia dan daftar Tryout yang dapat dipilih untuk dikaitkan.
4. THE System SHALL memungkinkan Admin menambahkan lebih dari satu Tryout ke dalam satu Produk dalam satu sesi mapping.
5. THE System SHALL memungkinkan Admin mengatur urutan tampil Tryout di dalam Produk pada halaman Mapping Tryout.
6. WHEN Admin menghapus mapping Tryout dari Produk, THE System SHALL menampilkan konfirmasi sebelum menghapus keterkaitan tersebut.
7. IF Tryout yang akan di-mapping sudah terkait dengan Produk yang sama, THEN THE System SHALL menampilkan pesan peringatan dan mencegah duplikasi mapping.
8. THE System SHALL menampilkan jumlah total Tryout yang sudah di-mapping untuk setiap Produk pada halaman Mapping Tryout.
9. WHEN User membeli Produk, THE System SHALL memberikan akses ke seluruh Tryout yang telah di-mapping ke Produk tersebut.

---

### Requirement 16: Master Aplikasi

**User Story:** Sebagai Super Admin, saya ingin mengelola seluruh pengaturan aplikasi melalui satu sub-menu terpusat, sehingga konfigurasi sistem dapat dikelola dengan mudah.

#### Acceptance Criteria

1. THE System SHALL menyediakan sub-menu Master Aplikasi di dalam menu Master Data yang hanya dapat diakses oleh Super_Admin.
2. THE System SHALL menyediakan antarmuka pada Master Aplikasi untuk mengatur konfigurasi Midtrans, termasuk API key, client key, dan environment (sandbox atau production).
3. THE System SHALL menyediakan antarmuka pada Master Aplikasi untuk mengatur konfigurasi email SMTP, termasuk host, port, username, dan enkripsi.
4. THE System SHALL menyediakan antarmuka pada Master Aplikasi untuk mengatur informasi umum aplikasi, termasuk nama aplikasi, logo, dan deskripsi.
5. THE System SHALL menyediakan antarmuka pada Master Aplikasi untuk mengatur kebijakan sesi, termasuk durasi timeout sesi pengguna.
6. WHEN Super_Admin menyimpan perubahan konfigurasi pada Master Aplikasi, THE System SHALL menerapkan perubahan tanpa memerlukan restart aplikasi.
7. IF Super_Admin memasukkan nilai konfigurasi yang tidak valid pada Master Aplikasi, THEN THE System SHALL menampilkan pesan error yang deskriptif dan tidak menyimpan perubahan tersebut.

---

### Requirement 12: Manajemen Super Admin

**User Story:** Sebagai Super Admin, saya ingin memiliki akses penuh ke seluruh fitur sistem, sehingga dapat mengelola dan mengkonfigurasi platform secara menyeluruh.

#### Acceptance Criteria

1. THE Super_Admin SHALL memiliki akses ke seluruh fitur yang tersedia untuk role Admin.
2. THE System SHALL menyediakan antarmuka manajemen akun untuk Super_Admin, termasuk membuat, mengubah, menonaktifkan, dan menghapus akun User dan Admin.
3. THE System SHALL menyediakan antarmuka konfigurasi sistem untuk Super_Admin, termasuk pengaturan Midtrans (API key, environment), pengaturan email SMTP, dan pengaturan umum aplikasi.
4. THE System SHALL menyediakan antarmuka manajemen role dan hak akses untuk Super_Admin.
5. WHEN Super_Admin menonaktifkan akun Admin, THE System SHALL mencabut seluruh sesi aktif Admin tersebut secara langsung.
6. THE System SHALL mencatat seluruh aktivitas Super_Admin dalam audit log yang tidak dapat diubah atau dihapus oleh Admin.
7. THE System SHALL menyediakan fitur backup dan restore database yang hanya dapat diakses oleh Super_Admin.

---

### Requirement 13: Keamanan dan Otorisasi

**User Story:** Sebagai pengelola sistem, saya ingin memastikan setiap pengguna hanya dapat mengakses fitur sesuai role-nya, sehingga keamanan data terjaga.

#### Acceptance Criteria

1. WHEN pengguna yang tidak terautentikasi mencoba mengakses halaman yang memerlukan login, THE System SHALL mengarahkan ke halaman login.
2. WHEN pengguna terautentikasi mencoba mengakses halaman di luar hak akses role-nya, THE System SHALL menampilkan halaman error 403 Forbidden.
3. THE System SHALL memvalidasi seluruh input dari pengguna di sisi server untuk mencegah SQL injection dan XSS.
4. THE System SHALL menggunakan HTTPS untuk seluruh komunikasi antara browser dan server.
5. WHILE sesi pengguna tidak aktif selama 60 menit, THE System SHALL secara otomatis mengakhiri sesi dan mengarahkan ke halaman login.
