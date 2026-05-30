<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between w-100">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="ph-title"><?= esc($tryout['nama']) ?></div>
            <div class="ph-subtitle">Riwayat & Statistik Sesi Tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('user/tryout') ?>" class="ph-action">
        <i class="bi bi-arrow-left me-1"></i>Paket Saya
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistik Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary"><?= $totalSesi ?></div>
                <div class="text-muted small">Total Sesi</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success"><?= number_format($skorTerbaik, 0) ?></div>
                <div class="text-muted small">Skor Terbaik (poin)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning"><?= number_format($skorRataRata, 0) ?></div>
                <div class="text-muted small">Rata-rata (poin)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold <?= $jumlahLulus > 0 ? 'text-success' : 'text-secondary' ?>">
                    <?= $jumlahLulus ?><span class="text-muted fw-normal" style="font-size:1rem">/<?= $totalSesi ?></span>
                </div>
                <div class="text-muted small">Kali Lulus</div>
                <?php if ($totalSesi > 0): ?>
                <div class="mt-1">
                    <div class="progress" style="height:4px;border-radius:2px">
                        <div class="progress-bar bg-success" style="width:<?= round(($jumlahLulus / $totalSesi) * 100) ?>%"></div>
                    </div>
                    <div class="text-muted" style="font-size:.65rem"><?= round(($jumlahLulus / $totalSesi) * 100) ?>% tingkat kelulusan</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Chart -->
    <?php if ($totalSesi > 0): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="bi bi-graph-up me-2 text-primary"></i>Grafik Perkembangan Skor
            </div>
            <div class="card-body">
                <canvas id="chartSkor" style="max-height:280px"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tombol Mulai Sesi Baru -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Riwayat Sesi (<?= $totalSesi ?>)</h6>
            <?php if (! empty($isExpired)): ?>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">
                    <i class="bi bi-clock-history me-1"></i>Akses Expired
                </span>
            <?php elseif ($sesiAktif): ?>
                <a href="<?= base_url('user/tryout/jawab/' . $sesiAktif['id'] . '?soal_index=1') ?>"
                   class="btn btn-warning btn-sm fw-semibold">
                    <i class="bi bi-play-circle me-1"></i>Lanjutkan Sesi Aktif
                </a>
            <?php else: ?>
                <a href="<?= base_url('user/tryout/' . $tryout['id'] . '/mulai') ?>"
                   class="btn btn-primary btn-sm fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i>Mulai Sesi Baru
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Daftar Riwayat -->
    <?php if (empty($riwayat)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                    Belum ada sesi yang diselesaikan.
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach (array_reverse($riwayat) as $ri => $r):
            $sesiNo    = $totalSesi - $ri;
            $skor      = (float) ($r['skor_total'] ?? 0);
            $totalNilai = (int) ($r['total_nilai'] ?? 0);
            $skorClass = $skor >= 70 ? 'text-success' : ($skor >= 50 ? 'text-warning' : 'text-danger');

            // Parse detail_kategori
            $detailKat = [];
            if (! empty($r['detail_kategori'])) {
                $dec = json_decode($r['detail_kategori'], true);
                if (is_array($dec)) $detailKat = $dec;
            }
        ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2">
                                    Sesi #<?= $sesiNo ?>
                                </span>
                                <div>
                                    <div class="fw-semibold" style="font-size:.9rem">
                                        <?= date('d M Y H:i', strtotime($r['mulai_at'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        <?php if ($r['selesai_at']): ?>
                                            Durasi: <?= round((strtotime($r['selesai_at']) - strtotime($r['mulai_at'])) / 60) ?> menit
                                        <?php endif; ?>
                                        <?php if ($r['status'] === 'timeout'): ?>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Timeout</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <!-- Skor -->
                                <div class="text-center">
                                    <div class="fw-bold fs-4 <?= $skorClass ?>"><?= $totalNilai > 0 ? $totalNilai : number_format($skor, 1) . '%' ?></div>
                                    <div class="text-muted" style="font-size:.72rem">Total Poin</div>
                                </div>
                                <!-- Status Lulus -->
                                <?php if ($r['status_lulus'] === 'lulus'): ?>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-patch-check-fill me-1"></i>Lulus
                                    </span>
                                <?php elseif ($r['status_lulus'] === 'tidak_lulus'): ?>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-x-circle-fill me-1"></i>Tidak Lulus
                                    </span>
                                <?php endif; ?>
                                <!-- Tombol -->
                                <div class="d-flex gap-2">
                                    <?php if ($r['hasil_id']): ?>
                                        <a href="<?= base_url('user/tryout/hasil/' . $r['sesi_id']) ?>"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-bar-chart me-1"></i>Hasil
                                        </a>
                                        <a href="<?= base_url('user/tryout/pembahasan/' . $r['sesi_id']) ?>"
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-book me-1"></i>Bahas
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail per sub kategori -->
                    <?php if (! empty($detailKat)): ?>
                        <div class="card-body py-2 px-3">
                            <div class="row g-2">
                                <?php foreach ($detailKat as $kat):
                                    $katSkor  = (float) ($kat['skor'] ?? 0);
                                    $katNilai = (int) ($kat['total_nilai'] ?? 0);
                                    $barClass = $katSkor >= 70 ? 'bg-success' : ($katSkor >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                    <div class="col-12 col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.78rem">
                                            <span class="fw-semibold"><?= esc($kat['sub_kategori_nama'] ?? $kat['kategori_nama']) ?></span>
                                            <span class="<?= $katSkor >= 70 ? 'text-success' : ($katSkor >= 50 ? 'text-warning' : 'text-danger') ?> fw-bold">
                                                <?= $katNilai > 0 ? $katNilai . ' poin' : number_format($katSkor, 1) . '%' ?>
                                            </span>
                                        </div>
                                        <div class="progress" style="height:5px;border-radius:3px">
                                            <div class="progress-bar <?= $barClass ?>" style="width:<?= $katSkor ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php if ($totalSesi > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx    = document.getElementById('chartSkor').getContext('2d');
    const labels = <?= json_encode($chartLabels) ?>;
    const data   = <?= json_encode($chartData) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Poin',
                data: data,
                borderColor: '#1a3a5c',
                backgroundColor: 'rgba(26,58,92,.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#1a3a5c',
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Total Poin: ' + ctx.parsed.y
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v + ' poin' },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}());
</script>
<?php endif; ?>

<?= $this->endSection() ?>
