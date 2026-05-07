<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-speedometer2"></i></div>
        <div>
            <div class="ph-title">Dashboard</div>
            <div class="ph-subtitle">Ringkasan aktivitas dan statistik sistem</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">

    <!-- Total User -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total User</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalUser) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success flex-shrink-0">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Transaksi Hari Ini</div>
                    <div class="fs-4 fw-bold"><?= number_format($totalTransaksiHariIni) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan Bulan Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning flex-shrink-0">
                    <i class="bi bi-currency-dollar fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Pendapatan Bulan Ini</div>
                    <div class="fs-5 fw-bold">Rp <?= number_format($pendapatanBulanIni, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sesi Berlangsung -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger flex-shrink-0">
                    <i class="bi bi-play-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Sesi Berlangsung</div>
                    <div class="fs-4 fw-bold"><?= number_format($sesiSedangBerlangsung) ?></div>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.row stats cards -->

<!-- Chart Row -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-graph-up me-2 text-primary"></i>Tren Transaksi 30 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <canvas id="chartTrenTransaksi" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Latest Transactions Table -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>10 Transaksi Terbaru</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelTransaksiTerbaru" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:50px">No</th>
                                <th>Nama User</th>
                                <th>Produk</th>
                                <th>Tanggal</th>
                                <th class="text-end">Jumlah Bayar</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transaksiTerbaru)): ?>
                                <?php foreach ($transaksiTerbaru as $i => $trx): ?>
                                    <tr>
                                        <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                        <td>
                                            <div class="fw-medium"><?= esc($trx['user_nama']) ?></div>
                                        </td>
                                        <td class="text-muted"><?= esc($trx['produk_nama']) ?></td>
                                        <td class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($trx['created_at'])) ?>
                                        </td>
                                        <td class="text-end fw-medium">
                                            Rp <?= number_format((float)$trx['harga_bayar'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center pe-3">
                                            <?php
                                            $statusMap = [
                                                'success' => ['bg-success', 'Sukses'],
                                                'pending' => ['bg-warning text-dark', 'Pending'],
                                                'failed'  => ['bg-danger', 'Gagal'],
                                                'expired' => ['bg-secondary', 'Kedaluwarsa'],
                                            ];
                                            $statusInfo = $statusMap[$trx['status']] ?? ['bg-secondary', esc($trx['status'])];
                                            ?>
                                            <span class="badge <?= $statusInfo[0] ?> rounded-pill px-2">
                                                <?= $statusInfo[1] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                        Belum ada transaksi
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Encode chart data as JSON for Chart.js
$chartLabels    = array_column($trenTransaksi, 'tanggal');
$chartJumlah    = array_map('intval', array_column($trenTransaksi, 'jumlah'));
$chartPendapatan = array_map('floatval', array_column($trenTransaksi, 'pendapatan'));
?>

<script>
(function () {
    // Chart.js — Tren Transaksi 30 Hari
    const labels      = <?= json_encode($chartLabels) ?>;
    const jumlahData  = <?= json_encode($chartJumlah) ?>;
    const pendapatanData = <?= json_encode($chartPendapatan) ?>;

    const ctx = document.getElementById('chartTrenTransaksi');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Jumlah Transaksi',
                        data: jumlahData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'yJumlah',
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: pendapatanData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'yPendapatan',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (context.dataset.yAxisID === 'yPendapatan') {
                                    return ' Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return ' Transaksi: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    yJumlah: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi',
                        }
                    },
                    yPendapatan: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        title: {
                            display: true,
                            text: 'Pendapatan (Rp)',
                        }
                    }
                }
            }
        });
    }

    // DataTables — Transaksi Terbaru
    $(document).ready(function () {
        $('#tabelTransaksiTerbaru').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            },
            pageLength: 10,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            columnDefs: [
                { orderable: false, targets: [0, 5] }
            ]
        });
    });
}());
</script>

<?= $this->endSection() ?>
