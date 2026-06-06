# Troubleshooting: Tombol Beli di Dashboard Mengabaikan Launch Date

## Masalah

Di halaman `/user/dashboard`, tombol "Beli Sekarang" pada section "Rekomendasi Tryout" selalu aktif meskipun `launch_date` di `master_aplikasi` belum terlewati. User bisa bypass launch gate lewat dashboard.

Halaman `/user/produk` sudah benar — tombol di-disable saat belum launching.

## Root Cause

View `dashboard.php` tidak mengecek config `launch_date` dari tabel `master_aplikasi`. Tombol "Beli Sekarang" di-render langsung sebagai link aktif tanpa kondisi apapun.

## Solusi

Tambahkan pengecekan `launch_date` di view `dashboard.php` (sama persis dengan logika di `produk/index.php`):

```php
// Cek status launching
$_db        = \Config\Database::connect();
$_cfgRows   = $_db->table('master_aplikasi')
    ->whereIn('config_key', ['launch_date', 'launch_message'])
    ->get()->getResultArray();
$_cfgMap     = array_column($_cfgRows, 'config_value', 'config_key');
$_launchDate = $_cfgMap['launch_date'] ?? '';
$_isLaunched = empty($_launchDate) || strtotime($_launchDate) <= time();
```

Kemudian kondisikan tombol:
- `$_isLaunched = true` → tampilkan link "Beli Sekarang" (aktif)
- `$_isLaunched = false` → tampilkan button disabled + label "Segera dibuka"

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Views/user/dashboard.php` | Tambah launch date check + kondisikan tombol beli di rekomendasi produk |

## Konsistensi Antar Halaman

| Halaman | Launch Date Check |
|---------|-------------------|
| `/user/produk` (index) | ✅ Ada — tombol disabled + banner countdown |
| `/user/produk/{id}` (detail) | ✅ Ada — tombol disabled |
| `/user/dashboard` (rekomendasi) | ✅ Ada (setelah fix) — tombol disabled + label "Segera dibuka" |

## Testing

1. Set `launch_date` di `master_aplikasi` ke tanggal masa depan
2. Akses `/user/dashboard` — tombol "Beli Sekarang" harus disabled
3. Set `launch_date` ke tanggal lampau atau kosongkan — tombol harus aktif
4. Pastikan tombol "Detail" tetap aktif di semua kondisi

## Git Workflow

```bash
git checkout -b fx-dashboard-launch-date-check
git add app/Views/user/dashboard.php docs/troubleshooting/dashboard-buy-button-ignores-launch-date.md docs/README.md
git commit -m "fix(dashboard): disable buy button on recommended products when launch date not reached"
git push -u origin fx-dashboard-launch-date-check
```
