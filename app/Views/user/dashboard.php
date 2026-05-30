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
                    <div class="text-muted small">Rekomendasi</div>
                    <div class="fs-4 fw-bold"><?= count($produkRekomendasi) ?></div>
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

<!-- Rekomendasi Tryout (produk highlight, belum dibeli) — max 8 -->
<?php $produkShow = array_slice($produkRekomendasi, 0, 8); ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-star-fill me-2 text-warning"></i>Rekomendasi Tryout</h6>
        <a href="<?= base_url('user/produk') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-grid me-1"></i>Lihat Semua
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($produkShow)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                Anda sudah memiliki semua paket yang direkomendasikan.
                <a href="<?= base_url('user/tryout') ?>" class="btn btn-success btn-sm ms-2">Mulai Tryout</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($produkShow as $p):
                    $thumb = ! empty($p['thumbnail'])
                        ? base_url('uploads/produk/' . $p['thumbnail'])
                        : base_url('assets/images/thumbnail/product-default.png');
                ?>
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;overflow:hidden;transition:transform .18s,box-shadow .18s;border:1px solid #e2e8f0">
                            <div style="aspect-ratio:1/1;overflow:hidden;background:#e8f0fe">
                                <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>"
                                     class="w-100 h-100" style="object-fit:cover;object-position:center">
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="mb-2" style="color:#1a3a5c;font-size:.9rem;font-weight:600;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                    <?= esc($p['nama']) ?>
                                </h6>
                                <div class="d-flex align-items-center gap-1 mb-2 text-muted" style="font-size:.75rem">
                                    <i class="bi bi-journal-text"></i>
                                    <span><?= $p['jumlah_tryout'] ?> sesi tryout</span>
                                </div>
                                <?php if (! empty($p['formasi_nama'])): ?>
                                <div class="mb-2">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-size:.65rem">
                                        <i class="bi bi-briefcase me-1"></i><?= esc($p['formasi_nama']) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <div class="mb-3 mt-auto">
                                    <?php if ($p['harga_promo'] !== null): ?>
                                        <div class="text-decoration-line-through text-muted" style="font-size:.75rem">
                                            Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                        </div>
                                        <div class="fw-bold text-danger" style="font-size:1rem">
                                            Rp <?= number_format($p['harga_promo'], 0, ',', '.') ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="fw-bold text-primary" style="font-size:1rem">
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

<!-- Rekomendasi Buku (highlight) — max 8 -->
<?php if (! empty($bukuHighlight)): ?>
<?php $bukuShow = array_slice($bukuHighlight, 0, 8); ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-book-fill me-2 text-primary"></i>Rekomendasi Buku</h6>
        <a href="<?= base_url('user/katalog-buku') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-book me-1"></i>Lihat Semua
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($bukuShow as $b): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;overflow:hidden;transition:transform .18s,box-shadow .18s;border:1px solid #e2e8f0">
                        <div style="aspect-ratio:1/1;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center">
                            <img src="<?= esc($b['url_thumbnail'] ?? '') ?>"
                                 alt="<?= esc($b['judul']) ?>"
                                 style="width:100%;height:100%;object-fit:contain"
                                 referrerpolicy="no-referrer"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div style="display:none;flex-direction:column;align-items:center;color:#94a3b8;font-size:.8rem">
                                <i class="bi bi-book" style="font-size:2rem;margin-bottom:.3rem"></i>
                                <span>Gambar</span>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <p class="mb-3" style="font-size:.85rem;font-weight:600;line-height:1.4;color:#1a3a5c;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                <?= esc($b['judul']) ?>
                            </p>
                            <div class="mt-auto">
                                <?php if (! empty($b['url_shopee'])): ?>
                                <a href="<?= esc($b['url_shopee']) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="btn btn-sm w-100 fw-semibold"
                                   style="background:linear-gradient(135deg,#ee4d2d,#ff6633);color:#fff;border:none;border-radius:.5rem">
                                    <i class="bi bi-cart3 me-1"></i>Beli di Shopee
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

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
