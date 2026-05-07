<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-graph-up"></i></div>
        <div>
            <div class="ph-title">Laporan Transaksi</div>
            <div class="ph-subtitle">Rekap data transaksi pembelian</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/laporan/transaksi/export-excel?' . http_build_query([
            'tanggal_dari'  => $filters['tanggalDari'],
            'tanggal_sampai'=> $filters['tanggalSampai'],
            'status'        => $filters['status'],
            'produk_id'     => $filters['produkId'] ?? '',
        ])) ?>" class="ph-action">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <a href="<?= base_url('admin/laporan/transaksi/export-pdf?' . http_build_query([
            'tanggal_dari'  => $filters['tanggalDari'],
            'tanggal_sampai'=> $filters['tanggalSampai'],
            'status'        => $filters['status'],
            'produk_id'     => $filters['produkId'] ?? '',
        ])) ?>" class="ph-action" style="background: var(--bs-danger, #dc3545);">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/laporan/transaksi') ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-medium">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" class="form-control form-control-sm"
                       value="<?= esc($filters['tanggalDari']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" class="form-control form-control-sm"
                       value="<?= esc($filters['tanggalSampai']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-medium">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all"     <?= $filters['status'] === 'all'     ? 'selected' : '' ?>>Semua</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="success" <?= $filters['status'] === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failed"  <?= $filters['status'] === 'failed'  ? 'selected' : '' ?>>Failed</option>
                    <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Produk</label>
                <select name="produk_id" class="form-select form-select-sm">
                    <option value="">Semua Produk</option>
                    <?php foreach ($produks as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= (string) ($filters['produkId'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Card -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                    <div>
                        <div class="small opacity-75">Total Pendapatan</div>
                        <div class="fw-bold fs-5">
                            Rp <?= number_format($totalPendapatan, 0, ',', '.') ?>
                        </div>
                        <div class="small opacity-75">Transaksi berstatus success</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                        <i class="bi bi-receipt fs-5"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Total Transaksi</div>
                        <div class="fw-bold fs-5"><?= count($transaksis) ?></div>
                        <div class="small text-muted">Semua status</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning">
                        <i class="bi bi-calendar-range fs-5"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Periode</div>
                        <div class="fw-semibold">
                            <?= date('d M Y', strtotime($filters['tanggalDari'])) ?>
                        </div>
                        <div class="small text-muted">
                            s/d <?= date('d M Y', strtotime($filters['tanggalSampai'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelTransaksi" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Kode Transaksi</th>
                        <th>Nama User</th>
                        <th>Produk</th>
                        <th>Tanggal</th>
                        <th class="text-end">Harga Bayar</th>
                        <th class="text-center pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($transaksis)): ?>
                        <?php foreach ($transaksis as $i => $t): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td class="font-monospace small"><?= esc($t['kode_transaksi']) ?></td>
                                <td><?= esc($t['user_nama']) ?></td>
                                <td><?= esc($t['produk_nama']) ?></td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></small>
                                </td>
                                <td class="text-end fw-medium">
                                    Rp <?= number_format((float) $t['harga_bayar'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center pe-3">
                                    <?php
                                    $badgeClass = match($t['status']) {
                                        'success' => 'bg-success',
                                        'pending' => 'bg-warning text-dark',
                                        'failed'  => 'bg-danger',
                                        default   => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill">
                                        <?= esc($t['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Tidak ada data transaksi untuk filter ini
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tabelTransaksi').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ]
    });
});
</script>

<?= $this->endSection() ?>
