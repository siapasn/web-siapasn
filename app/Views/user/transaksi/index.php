<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="ph-title">Riwayat Transaksi</div>
            <div class="ph-subtitle">Kelola semua transaksi pembelian Anda</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('user/produk') ?>" class="btn btn-sm"
       style="background:rgba(245,166,35,.2);border:1px solid rgba(245,166,35,.5);color:var(--sa-accent);font-weight:600;">
        <i class="bi bi-cart-plus me-1"></i>Beli Paket
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter Status -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small me-1">Filter:</span>
            <a href="<?= base_url('user/transaksi') ?>"
               class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Semua
            </a>
            <?php foreach (['pending' => 'warning', 'success' => 'success', 'failed' => 'danger', 'expired' => 'secondary'] as $status => $color): ?>
                <a href="<?= base_url('user/transaksi?status=' . $status) ?>"
                   class="btn btn-sm <?= $statusFilter === $status ? 'btn-' . $color : 'btn-outline-' . $color ?>">
                    <?= ucfirst($status) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($transaksi)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt fs-1 d-block mb-3"></i>
                <p class="mb-0">Belum ada transaksi<?= $statusFilter ? ' dengan status <strong>' . esc($statusFilter) . '</strong>' : '' ?>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Paket</th>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $t): ?>
                            <tr>
                                <td>
                                    <span class="font-monospace small"><?= esc($t['kode_transaksi']) ?></span>
                                </td>
                                <td><?= esc($t['produk_nama']) ?></td>
                                <td>
                                    <span class="small"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></span>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp <?= number_format($t['harga_bayar'], 0, ',', '.') ?>
                                    <?php if ($t['diskon'] > 0): ?>
                                        <br><small class="text-muted text-decoration-line-through">
                                            Rp <?= number_format($t['harga_asli'], 0, ',', '.') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $badgeMap = [
                                            'pending' => 'warning',
                                            'success' => 'success',
                                            'failed'  => 'danger',
                                            'expired' => 'secondary',
                                        ];
                                        $badgeColor = $badgeMap[$t['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>-subtle text-<?= $badgeColor ?> border border-<?= $badgeColor ?>-subtle">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('user/transaksi/' . $t['id']) ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
