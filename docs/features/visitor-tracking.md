# Fitur: Visitor Tracking (Pengunjung Harian)

## Overview

Fitur tracking pengunjung harian yang mencatat unique visitor per hari berdasarkan IP address, menampilkan jumlah pengunjung hari ini di dashboard admin beserta persentase perbandingan dengan hari sebelumnya.

## Arsitektur

```
Request masuk → VisitorFilter (before) → INSERT IGNORE visitors → lanjut normal
                    ↓
              Skip jika:
              - Bukan GET request
              - User agent = bot/crawler
              - Route admin/superadmin (tidak di-apply)
```

## Files Created

| File | Fungsi |
|------|--------|
| `app/Database/Migrations/2026-06-05-000001_CreateVisitorsTable.php` | Migration tabel visitors |
| `app/Models/VisitorModel.php` | Model dengan method recordVisit, getDailyStats, getTrend |
| `app/Filters/VisitorFilter.php` | Filter HTTP yang mencatat pengunjung |

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Config/Filters.php` | Register alias `visitor` + apply ke route publik & user |
| `app/Controllers/Admin/DashboardController.php` | Tambah data visitorStats & trenVisitor |
| `app/Views/admin/dashboard.php` | Tambah card pengunjung + chart tren 30 hari |

## Tabel Database

```sql
CREATE TABLE visitors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    page_url VARCHAR(500) NULL,
    visited_at DATE NOT NULL,
    created_at DATETIME NULL,
    INDEX idx_visited_at (visited_at),
    UNIQUE KEY unique_visitor_per_day (ip_address, visited_at)
);
```

## Rules Perhitungan

- **Unique visitor**: 1 IP address = 1 pengunjung per hari (menggunakan UNIQUE constraint)
- **Persentase**: `((hari_ini - kemarin) / kemarin) × 100%`
- **Edge case**: Jika kemarin = 0 dan hari ini > 0, tampilkan +100%. Jika keduanya 0, tampilkan 0%.
- **Bot filtering**: User agent yang mengandung kata kunci bot/crawler di-skip

## Tampilan Dashboard

- Card "Pengunjung Hari Ini" dengan angka + persentase naik/turun (warna hijau/merah)
- Chart bar "Tren Pengunjung 30 Hari Terakhir" di samping chart transaksi

## Testing

```bash
# Pastikan tabel sudah dibuat
mysql -u root -P 3307 cpns_tryout -e "SELECT COUNT(*) FROM visitors;"

# Akses halaman publik, lalu cek apakah tercatat
curl http://localhost:8081/
mysql -u root -P 3307 cpns_tryout -e "SELECT * FROM visitors WHERE visited_at = CURDATE();"

# Cek dashboard admin menampilkan data
# Login sebagai admin → /admin/dashboard
```

## Git Workflow

```bash
git checkout -b ft-visitor-tracking
git add app/Database/Migrations/2026-06-05-000001_CreateVisitorsTable.php app/Models/VisitorModel.php app/Filters/VisitorFilter.php app/Config/Filters.php app/Controllers/Admin/DashboardController.php app/Views/admin/dashboard.php docs/features/visitor-tracking.md docs/README.md
git commit -m "feat(analytics): add daily visitor tracking with percentage comparison"
git push -u origin ft-visitor-tracking
```
