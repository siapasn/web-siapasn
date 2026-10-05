<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SesiTryoutModel;
use App\Services\TryoutScoringService;

/**
 * FinalisasiSesi
 *
 * Memfinalisasi sesi tryout yang menggantung (status 'berlangsung') —
 * yaitu sesi milik user yang mulai mengerjakan lalu menutup browser tanpa
 * submit. Sesi ditandai 'timeout' lalu di-scoring dengan jawaban apa adanya
 * (soal yang belum dijawab dihitung kosong/0), sehingga hasil tercatat dan
 * leaderboard/riwayat bisa diakses.
 *
 * Contoh penggunaan:
 *   php spark app:finalisasi-sesi --dry-run           # lihat kandidat tanpa mengubah apa pun
 *   php spark app:finalisasi-sesi --sesi 123          # finalisasi 1 sesi tertentu
 *   php spark app:finalisasi-sesi --user 39           # finalisasi semua sesi menggantung milik user
 *   php spark app:finalisasi-sesi --event 1           # finalisasi semua sesi menggantung pada tryout milik event
 *   php spark app:finalisasi-sesi                     # finalisasi SEMUA sesi menggantung yang durasinya habis
 *   php spark app:finalisasi-sesi --force             # abaikan cek durasi (paksa finalisasi walau durasi belum habis)
 */
class FinalisasiSesi extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:finalisasi-sesi';
    protected $description = 'Finalisasi (timeout + scoring) sesi tryout yang menggantung agar hasil tercatat.';
    protected $usage       = 'app:finalisasi-sesi [--sesi id] [--user id] [--event id] [--dry-run] [--force]';
    protected $options     = [
        '--sesi'    => 'Finalisasi hanya sesi dengan ID ini.',
        '--user'    => 'Finalisasi hanya sesi menggantung milik user ini.',
        '--event'   => 'Finalisasi hanya sesi pada tryout yang dipakai event ini.',
        '--dry-run' => 'Tampilkan kandidat tanpa melakukan perubahan.',
        '--force'   => 'Finalisasi walau durasi pengerjaan belum habis.',
    ];

    public function run(array $params)
    {
        $db        = \Config\Database::connect();
        $sesiModel = new SesiTryoutModel();
        $scoring   = new TryoutScoringService();

        $sesiId  = $params['sesi']  ?? CLI::getOption('sesi');
        $userId  = $params['user']  ?? CLI::getOption('user');
        $eventId = $params['event'] ?? CLI::getOption('event');
        $dryRun  = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $force   = array_key_exists('force', $params)   || CLI::getOption('force');

        // Bangun query sesi 'berlangsung' sesuai filter.
        $builder = $db->table('sesi_tryout st')
            ->select('st.id, st.user_id, st.tryout_id, st.mulai_at, st.status, t.durasi, u.nama AS user_nama')
            ->join('tryout t', 't.id = st.tryout_id')
            ->join('users u', 'u.id = st.user_id', 'left')
            ->where('st.status', 'berlangsung');

        if ($sesiId) {
            $builder->where('st.id', (int) $sesiId);
        }
        if ($userId) {
            $builder->where('st.user_id', (int) $userId);
        }
        if ($eventId) {
            $event = $db->table('tryout_event')->where('id', (int) $eventId)->get()->getRowArray();
            if (! $event) {
                CLI::error("Event #{$eventId} tidak ditemukan.");
                return;
            }
            $builder->where('st.tryout_id', (int) $event['tryout_id']);
        }

        $sesiList = $builder->orderBy('st.mulai_at', 'ASC')->get()->getResultArray();

        if (empty($sesiList)) {
            CLI::write('Tidak ada sesi berstatus "berlangsung" yang cocok dengan filter.', 'yellow');
            return;
        }

        CLI::write('Ditemukan ' . count($sesiList) . ' sesi "berlangsung".', 'yellow');
        CLI::newLine();

        $now        = time();
        $difinalkan = 0;
        $dilewati   = 0;

        foreach ($sesiList as $s) {
            $id          = (int) $s['id'];
            $durasiMenit = (int) ($s['durasi'] ?? 0);
            $mulaiAt     = $s['mulai_at'] ?? null;

            $durasiHabis = false;
            $sisaMenit   = null;
            if ($durasiMenit > 0 && ! empty($mulaiAt)) {
                $batas       = strtotime($mulaiAt) + ($durasiMenit * 60);
                $durasiHabis = $now >= $batas;
                $sisaMenit   = (int) ceil(($batas - $now) / 60);
            }

            $info = sprintf(
                'Sesi #%d | user %s (#%d) | tryout #%d | mulai %s | durasi %d menit',
                $id,
                $s['user_nama'] ?? '-',
                (int) $s['user_id'],
                (int) $s['tryout_id'],
                $mulaiAt ?? '-',
                $durasiMenit
            );

            // Tanpa --force, hanya finalisasi sesi yang durasinya sudah habis.
            if (! $force && ! $durasiHabis) {
                CLI::write("[LEWAT] {$info} — durasi belum habis (sisa ~{$sisaMenit} menit). Pakai --force untuk memaksa.", 'light_gray');
                $dilewati++;
                continue;
            }

            if ($dryRun) {
                CLI::write("[DRY-RUN] akan difinalisasi: {$info}", 'cyan');
                $difinalkan++;
                continue;
            }

            try {
                $sesiModel->selesaikan($id, 'timeout');
                $hasil = $scoring->hitung($id);
                CLI::write(sprintf(
                    "[OK] %s → total_nilai: %s, skor_total: %s, lulus: %s",
                    $info,
                    $hasil['total_nilai'] ?? 0,
                    $hasil['skor_total'] ?? 0,
                    $hasil['status_lulus'] ?? 'null'
                ), 'green');
                $difinalkan++;
            } catch (\Throwable $e) {
                CLI::error("[ERROR] Sesi #{$id}: " . $e->getMessage());
            }
        }

        CLI::newLine();
        if ($dryRun) {
            CLI::write("Dry-run selesai. {$difinalkan} sesi akan difinalisasi, {$dilewati} dilewati.", 'green');
        } else {
            CLI::write("Selesai. {$difinalkan} sesi difinalisasi, {$dilewati} dilewati.", 'green');
        }
    }
}
