<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorModel extends Model
{
    protected $table         = 'visitors';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['ip_address', 'user_agent', 'page_url', 'visited_at', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Catat kunjungan unik per IP per hari.
     * Menggunakan INSERT IGNORE agar tidak duplikat.
     */
    public function recordVisit(string $ipAddress, ?string $userAgent = null, ?string $pageUrl = null): bool
    {
        $today = date('Y-m-d');

        // Gunakan raw query INSERT IGNORE untuk performa optimal
        $db = \Config\Database::connect();
        $sql = "INSERT IGNORE INTO {$this->table} (ip_address, user_agent, page_url, visited_at, created_at) VALUES (?, ?, ?, ?, ?)";

        return $db->query($sql, [
            $ipAddress,
            $userAgent ? mb_substr($userAgent, 0, 500) : null,
            $pageUrl ? mb_substr($pageUrl, 0, 500) : null,
            $today,
            date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Hitung jumlah pengunjung unik pada tanggal tertentu.
     */
    public function countByDate(string $date): int
    {
        return $this->where('visited_at', $date)->countAllResults();
    }

    /**
     * Dapatkan statistik pengunjung hari ini vs kemarin beserta persentase perubahan.
     */
    public function getDailyStats(): array
    {
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $todayCount     = $this->countByDate($today);
        $yesterdayCount = $this->countByDate($yesterday);

        // Hitung persentase perubahan
        if ($yesterdayCount > 0) {
            $percentage = round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 1);
        } elseif ($todayCount > 0) {
            $percentage = 100.0; // Ada pengunjung hari ini tapi kemarin 0
        } else {
            $percentage = 0.0; // Keduanya 0
        }

        return [
            'today_count'     => $todayCount,
            'yesterday_count' => $yesterdayCount,
            'percentage'      => $percentage,
            'trend'           => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Dapatkan tren pengunjung N hari terakhir (untuk chart).
     */
    public function getTrend(int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));

        return $this->select('visited_at as tanggal, COUNT(*) as jumlah')
            ->where('visited_at >=', $startDate)
            ->groupBy('visited_at')
            ->orderBy('visited_at', 'ASC')
            ->findAll();
    }
}
