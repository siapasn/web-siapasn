# Troubleshooting: Visitor Filter Tidak Mencatat Pengunjung

## Masalah

Setelah implementasi visitor tracking, tabel `visitors` tetap kosong meskipun sudah diakses dari device lain.

## Root Cause

1. **CI4 URI pattern `'/'` tidak match root URL** — CodeIgniter 4 `$filters` config menggunakan URI segment matching, dan pattern `'/'` tidak reliabel untuk mencocokkan homepage (root path kosong).

2. **NAT / IP sama** — Device berbeda di jaringan WiFi yang sama memiliki IP publik yang sama (karena NAT router). Karena constraint `UNIQUE (ip_address, visited_at)`, hanya 1 record per IP per hari.

## Solusi

### Fix 1: Ganti URI pattern ke wildcard `'*'`

```php
// app/Config/Filters.php
public array $filters = [
    'auth'    => ['before' => ['user/*', 'admin/*', 'superadmin/*']],
    'visitor' => ['before' => ['*']],  // Match SEMUA route
];
```

### Fix 2: Exclude admin/cron dari dalam filter

```php
// app/Filters/VisitorFilter.php — di method before()
$uri = service('uri')->getPath();
$excludedPrefixes = ['admin', 'superadmin', 'cron', 'webhook', 'file'];
foreach ($excludedPrefixes as $prefix) {
    if (str_starts_with(ltrim($uri, '/'), $prefix)) {
        return null;
    }
}
```

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
| `app/Config/Filters.php` | Pattern visitor diganti dari list URI ke `['*']` |
| `app/Filters/VisitorFilter.php` | Tambah exclusion logic untuk admin/superadmin/cron di dalam filter |

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
