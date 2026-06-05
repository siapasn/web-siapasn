# Troubleshooting: Visitor Filter Tidak Mencatat Pengunjung

## Masalah

Setelah implementasi visitor tracking, tabel `visitors` tetap kosong meskipun sudah diakses dari device lain.

## Root Cause

1. **CI4 Filter `$filters` config tidak reliabel** — Pattern URI di `$filters` array (termasuk `'*'` wildcard) tidak selalu ter-trigger di semua konfigurasi server. Filter hanya jalan jika route benar-benar match dan tidak ada early return (redirect, dll.).

2. **Solusi: Pindah ke BaseController** — Menempatkan logic tracking di `BaseController::initController()` menjamin eksekusi karena semua controller pasti melewati method ini.

3. **NAT / IP sama** — Device berbeda di jaringan WiFi yang sama memiliki IP publik yang sama (karena NAT router). Karena constraint `UNIQUE (ip_address, visited_at)`, hanya 1 record per IP per hari.

## Solusi

### Fix Final: Pindah tracking ke BaseController

CI4 Filter ternyata tidak reliabel untuk semua route. Solusi terbaik: pindah logic ke `BaseController::initController()`.

```php
// app/Controllers/BaseController.php — di initController()
$this->recordVisitor($request);
```

Method `recordVisitor()` ditambahkan di `BaseController`:
- Hanya GET request
- Skip route admin/superadmin/cron/webhook
- Skip bot/crawler (regex pattern)
- `INSERT IGNORE` ke tabel visitors (1 IP per hari)

### Pendekatan Sebelumnya (Tidak Berhasil)

1. **Filter `$filters` dengan URI list** — pattern `'/'` tidak match root URL
2. **Filter `$filters` dengan wildcard `'*'`** — tetap tidak reliabel di beberapa konfigurasi

## Checklist Debugging

| Cek | Cara |
|-----|------|
| Tabel ada? | `DESCRIBE visitors;` |
| Ada record? | `SELECT * FROM visitors;` |
| Server jalan? | Restart `php spark serve` setelah ubah config |
| IP sama? | Device WiFi sama = IP publik sama (NAT) → hanya 1 record/hari |
| User-Agent kosong? | Browser tanpa UA di-skip (dianggap bot) |

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Controllers/BaseController.php` | Tambah `recordVisitor()` method + panggil di `initController()` |
| `app/Config/Filters.php` | Hapus visitor filter dari `$filters` (tidak digunakan lagi) |
| `app/Filters/VisitorFilter.php` | Masih ada sebagai backup, tapi tidak aktif di config |

## Validasi

```sql
-- Cek isi tabel setelah akses homepage
SELECT * FROM visitors WHERE visited_at = CURDATE();

-- Cek apakah IP sudah tercatat hari ini
SELECT ip_address, visited_at, page_url FROM visitors ORDER BY created_at DESC LIMIT 5;
```

## Tips

- Untuk testing unique visitor, gunakan **mobile data (4G)** vs **WiFi** agar IP berbeda
- Atau gunakan VPN/proxy untuk mendapat IP yang berbeda
- Di localhost, semua request dari browser lokal akan tercatat sebagai `127.0.0.1` atau `::1`
