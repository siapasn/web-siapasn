<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-speedometer2"></i></div>
    <div>
        <div class="ph-title">Dashboard</div>
        <div class="ph-subtitle">Selamat datang, <?= esc(session('nama')) ?></div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded p-3">
                    <i class="bi bi-box-seam fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Produk Tersedia</div>
                    <div class="fs-4 fw-bold"><?= count($produkTerbaru) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded p-3">
                    <i class="bi bi-pencil-square fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Tryout</div>
                    <div class="fs-4 fw-bold"><?= count($riwayatTryout) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded p-3">
                    <i class="bi bi-bar-chart fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Rata-rata Nilai</div>
                    <div class="fs-4 fw-bold"><?= $avgSkor ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Produk Terbaru (belum dibeli) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-shop me-2 text-primary"></i>Produk Terbaru</h6>
        <a href="<?= base_url('user/produk') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-grid me-1"></i>Lihat Semua
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($produkTerbaru)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                Anda sudah memiliki semua paket yang tersedia.
                <a href="<?= base_url('user/tryout') ?>" class="btn btn-success btn-sm ms-2">Mulai Tryout</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($produkTerbaru as $p):
                    $thumb = ! empty($p['thumbnail'])
                        ? base_url('uploads/produk/' . $p['thumbnail'])
                        : base_url('assets/images/thumbnail/product-default.png');
                ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;overflow:hidden;transition:transform .18s,box-shadow .18s">
                            <!-- Thumbnail -->
                            <div style="aspect-ratio:1/1;overflow:hidden;background:#e8f0fe">
                                <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>"
                                     class="w-100 h-100" style="object-fit:cover;object-position:center">
                            </div>

                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="fw-bold mb-2" style="color:#1a3a5c;font-size:.95rem;line-height:1.3">
                                    <?= esc($p['nama']) ?>
                                </h6>

                                <div class="d-flex align-items-center gap-1 mb-2 text-muted" style="font-size:.78rem">
                                    <i class="bi bi-journal-text"></i>
                                    <span><?= $p['jumlah_tryout'] ?> sesi tryout</span>
                                </div>

                                <!-- Harga -->
                                <div class="mb-3 mt-auto">
                                    <?php if ($p['harga_promo'] !== null): ?>
                                        <div class="text-decoration-line-through text-muted" style="font-size:.78rem">
                                            Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                        </div>
                                        <div class="fw-bold text-danger" style="font-size:1.05rem">
                                            Rp <?= number_format($p['harga_promo'], 0, ',', '.') ?>
                                        </div>
                                        <?php foreach ($p['promosi'] as $pr): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" style="font-size:.65rem">
                                                <i class="bi bi-tag me-1"></i><?= esc($pr['nama']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="fw-bold text-primary" style="font-size:1.05rem">
                                            Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <a href="<?= base_url('user/produk/' . $p['id']) ?>"
                                       class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                    <a href="<?= base_url('user/transaksi/pilih-metode/' . $p['id']) ?>"
                                       class="btn btn-primary btn-sm fw-semibold">
                                        <i class="bi bi-credit-card me-1"></i>Beli Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Riwayat Tryout -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>5 Riwayat Tryout Terakhir</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($riwayatTryout)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                Belum ada riwayat tryout.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tryout</th>
                            <th class="text-center">Skor</th>
                            <th class="text-center">Benar</th>
                            <th class="text-center">Salah</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayatTryout as $riwayat): ?>
                            <tr>
                                <td><?= esc($riwayat['tryout_nama']) ?></td>
                                <td class="text-center fw-bold"><?= $riwayat['skor_total'] ?></td>
                                <td class="text-center text-success"><?= $riwayat['jumlah_benar'] ?></td>
                                <td class="text-center text-danger"><?= $riwayat['jumlah_salah'] ?></td>
                                <td><?= date('d M Y H:i', strtotime($riwayat['created_at'])) ?></td>
                                <td>
                                    <a href="<?= base_url('user/tryout/hasil/' . $riwayat['sesi_tryout_id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
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
