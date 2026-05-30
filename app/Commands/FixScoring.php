<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\TryoutScoringService;

class FixScoring extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:fix-scoring';
    protected $description = 'Re-calculate scoring untuk semua hasil tryout yang belum punya status_lulus.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Ambil semua sesi yang sudah selesai
        $sesiList = $db->table('sesi_tryout')
            ->select('id')
            ->whereIn('status', ['selesai', 'timeout'])
            ->get()->getResultArray();

        CLI::write("Found " . count($sesiList) . " sesi to check.", 'yellow');

        $scoringService = new TryoutScoringService();
        $fixed = 0;

        foreach ($sesiList as $sesi) {
            $sesiId = (int) $sesi['id'];

            // Cek apakah hasil sudah ada dan punya status_lulus
            $hasil = $db->table('hasil_tryout')
                ->where('sesi_tryout_id', $sesiId)
                ->get()->getRowArray();

            if ($hasil && ! empty($hasil['status_lulus']) && ! empty($hasil['detail_passing_grade'])) {
                continue; // Sudah OK
            }

            CLI::write("Re-scoring sesi #{$sesiId}...", 'white');

            try {
                $result = $scoringService->hitung($sesiId);
                CLI::write("  → status: " . ($result['status_lulus'] ?? 'null') . ", total_nilai: " . ($result['total_nilai'] ?? 0), 'green');
                $fixed++;
            } catch (\Throwable $e) {
                CLI::write("  → ERROR: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("\nDone. Fixed {$fixed} results.", 'green');
    }
}
