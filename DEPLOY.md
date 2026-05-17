# Panduan Deploy ke Shared Hosting (Rumah Web / cPanel)

## Struktur yang Diupload

Upload **semua file project** ke folder `public_html/` di hosting:

```
public_html/
├── .htaccess          ← root .htaccess (baru)
├── index.php          ← root entry point (baru)
├── app/
├── public/
│   ├── .htaccess      ← sudah ada
│   ├── index.php      ← sudah ada
│   └── assets/
├── vendor/
├── writable/
├── .env
└── ...
```

## Langkah-langkah Deploy

### 1. Siapkan file .env untuk production

Edit file `.env`:
```
CI_ENVIRONMENT = production
app.baseURL = 'https://namadomain.com/'
app.name    = 'SiapASN Simulation Center'

database.default.hostname = localhost
database.default.database = nama_database_di_hosting
database.default.username = nama_user_db
database.default.password = password_db
database.default.DBDriver = MySQLi
database.default.port     = 3306
```

### 2. Upload via FTP / File Manager cPanel

Upload semua file ke `public_html/`. Pastikan struktur folder seperti di atas.

### 3. Import Database

- Buka phpMyAdmin di cPanel
- Buat database baru
- Import file SQL dari project (atau jalankan migration)

### 4. Jalankan Migration (via SSH jika tersedia)

```bash
php spark migrate --all
php spark db:seed DatabaseSeeder
```

Jika tidak ada SSH, import file SQL langsung via phpMyAdmin.

### 5. Set Permission Folder

Pastikan folder `writable/` bisa ditulis:
```
writable/          → 755 atau 775
writable/cache/    → 755 atau 775
writable/logs/     → 755 atau 775
writable/session/  → 755 atau 775
writable/uploads/  → 755 atau 775
writable/backups/  → 755 atau 775
```

### 6. Verifikasi

Buka `https://namadomain.com/` — seharusnya tampil landing page.

## Troubleshooting

### Error 500
- Cek `writable/logs/` untuk log error
- Pastikan PHP version ≥ 8.2 (set di cPanel → PHP Selector)
- Pastikan `mod_rewrite` aktif

### Halaman tidak ditemukan (404)
- Pastikan `.htaccess` di root terupload dengan benar
- Cek apakah `AllowOverride All` aktif (biasanya sudah di shared hosting)

### Database error
- Cek kredensial database di `.env`
- Port MySQL di shared hosting biasanya `3306` (bukan 3307)
