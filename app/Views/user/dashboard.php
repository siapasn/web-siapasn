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
                    <div class="text-muted small">Paket Aktif</div>
                    <div class="fs-4 fw-bold"><?= count($paketAktif) ?></div>
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

<!-- Paket Aktif -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="bi bi-box me-2"></i>Paket Aktif Saya</h6>
    </div>
    <div class="card-body">
        <?php if (empty($paketAktif)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Anda belum memiliki paket aktif.
                <a href="<?= base_url('user/produk') ?>" class="btn btn-primary btn-sm ms-2">Beli Paket</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($paketAktif as $paket):
                    $thumb = ! empty($paket['thumbnail'])
                        ? base_url('uploads/produk/' . $paket['thumbnail'])
                        : base_url('assets/images/thumbnail/product-default.png');
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;overflow:hidden">
                            <!-- Thumbnail -->
                            <div style="aspect-ratio:16/9;overflow:hidden;background:#e8f0fe">
                                <img src="<?= $thumb ?>" alt="<?= esc($paket['produk_nama']) ?>"
                                     class="w-100 h-100" style="object-fit:cover;object-position:center">
                            </div>

                            <div class="card-body p-3">
                                <!-- Nama Produk -->
                                <h6 class="fw-bold mb-2" style="color:#1a3a5c;line-height:1.3">
                                    <?= esc($paket['produk_nama']) ?>
                                </h6>

                                <!-- Info Tryout -->
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1" style="font-size:.75rem">
                                        <i class="bi bi-journal-text me-1"></i><?= $paket['jumlah_tryout'] ?> Sesi Tryout
                                    </span>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1" style="font-size:.75rem">
                                        <i class="bi bi-clock me-1"></i><?= $paket['total_durasi'] ?> Menit
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1" style="font-size:.75rem">
                                        <i class="bi bi-question-circle me-1"></i><?= $paket['total_soal'] ?> Soal
                                    </span>
                                </div>

                                <!-- Masa Berlaku -->
                                <div class="text-muted small mb-3">
                                    <?php if ($paket['expired_at']): ?>
                                        <i class="bi bi-calendar-check me-1"></i>
                                        Berlaku hingga: <strong><?= date('d M Y', strtotime($paket['expired_at'])) ?></strong>
                                    <?php else: ?>
                                        <i class="bi bi-infinity me-1 text-success"></i>
                                        <span class="text-success fw-semibold">Akses Selamanya</span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?= base_url('user/tryout') ?>"
                                   class="btn btn-primary btn-sm w-100 fw-semibold">
                                    <i class="bi bi-play-circle me-1"></i>Mulai Tryout
                                </a>
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
