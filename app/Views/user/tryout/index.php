<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-journal-bookmark"></i></div>
    <div>
        <div class="ph-title">Paket Saya</div>
        <div class="ph-subtitle">Paket tryout yang Anda miliki</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.tryout-card {
    transition: transform .18s ease, box-shadow .18s ease;
    border-radius: 1rem !important;
    overflow: hidden;
}
.tryout-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12) !important;
}
.tryout-card .thumb-wrap {
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #e8f0fe;
}
.tryout-card .thumb-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: center;
    transition: transform .3s ease;
}
.tryout-card:hover .thumb-wrap img {
    transform: scale(1.04);
}
.tryout-card .nama-paket {
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.3;
    color: #1a3a5c;
}
.sesi-item {
    border-left: 3px solid #e9ecef;
    transition: border-color .15s;
}
.sesi-item:hover {
    border-left-color: #1a3a5c;
    background: #f8faff;
}
</style>

<?php if (empty($paketList)): ?>
    <div class="card border-0 shadow-sm rounded-3 mt-2">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-2">Anda belum memiliki paket tryout.</p>
            <a href="<?= base_url('user/produk') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i>Beli Paket Tryout
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mt-1">
        <?php foreach ($paketList as $idx => $item):
            $produk  = $item['produk'];
            $tryouts = $item['tryouts'];
            $thumb   = ! empty($produk['thumbnail'])
                ? base_url('uploads/produk/' . $produk['thumbnail'])
                : base_url('assets/images/thumbnail/product-default.png');

            $progress  = $item['jumlah_tryout'] > 0
                ? round(($item['jumlah_selesai'] / $item['jumlah_tryout']) * 100)
                : 0;
            $modalId   = 'modalSesi' . $idx;
        ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 tryout-card position-relative">

                    <!-- Badge progress -->
                    <?php if ($progress === 100): ?>
                        <span class="badge bg-success position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                            <i class="bi bi-check-circle me-1"></i>Selesai
                        </span>
                    <?php elseif ($item['jumlah_selesai'] > 0): ?>
                        <span class="badge bg-warning text-dark position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                            <i class="bi bi-play-circle me-1"></i>Berlangsung
                        </span>
                    <?php endif; ?>

                    <!-- Thumbnail -->
                    <div class="thumb-wrap">
                        <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>">
                    </div>

                    <div class="card-body d-flex flex-column p-3">

                        <!-- Nama Paket -->
                        <h6 class="nama-paket mb-2"><?= esc($produk['nama']) ?></h6>

                        <!-- Info badge -->
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.68rem">
                                <i class="bi bi-journal-text me-1"></i><?= $item['jumlah_tryout'] ?> sesi
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size:.68rem">
                                <i class="bi bi-clock me-1"></i><?= $item['total_durasi'] ?> mnt
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size:.68rem">
                                <i class="bi bi-list-ol me-1"></i><?= $item['total_soal'] ?> soal
                            </span>
                        </div>

                        <!-- Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;color:#6c757d">
                                <span><?= $item['jumlah_selesai'] ?>/<?= $item['jumlah_tryout'] ?> selesai</span>
                                <span><?= $progress ?>%</span>
                            </div>
                            <div class="progress" style="height:5px;border-radius:3px">
                                <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <!-- Lihat Sesi -->
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    style="border-radius:.5rem;font-weight:600;font-size:.8rem"
                                    data-bs-toggle="modal"
                                    data-bs-target="#<?= $modalId ?>">
                                <i class="bi bi-list-ul me-1"></i>Lihat Sesi (<?= $item['jumlah_tryout'] ?>)
                            </button>

                            <!-- Detail Produk -->
                            <a href="<?= base_url('user/produk/' . $produk['id']) ?>"
                               class="btn btn-outline-secondary btn-sm"
                               style="border-radius:.5rem;font-weight:600;font-size:.8rem">
                                <i class="bi bi-box-seam me-1"></i>Detail Produk
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal Daftar Sesi -->
            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h6 class="modal-title fw-bold" style="color:#1a3a5c"><?= esc($produk['nama']) ?></h6>
                                <p class="text-muted mb-0" style="font-size:.78rem">
                                    <?= $item['jumlah_selesai'] ?>/<?= $item['jumlah_tryout'] ?> sesi selesai
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <?php if (empty($tryouts)): ?>
                                <div class="text-center py-3 text-muted small">
                                    <i class="bi bi-journal-x me-1"></i>Belum ada sesi tryout.
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($tryouts as $i => $t): ?>
                                        <div class="sesi-item rounded p-3 bg-white border">
                                            <div class="d-flex align-items-center gap-3">
                                                <!-- Nomor -->
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                      style="width:30px;height:30px;font-size:.78rem">
                                                    <?= $i + 1 ?>
                                                </span>

                                                <!-- Info -->
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-semibold text-truncate" style="font-size:.88rem">
                                                        <?= esc($t['nama']) ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size:.73rem">
                                                        <i class="bi bi-clock me-1"></i><?= (int) $t['durasi'] ?> mnt
                                                        &bull;
                                                        <i class="bi bi-list-ol me-1"></i><?= (int) $t['jumlah_soal'] ?> soal
                                                    </div>
                                                </div>

                                                <!-- Tombol -->
                                                <div class="flex-shrink-0">
                                                    <?php if ($t['sudah_selesai']): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.68rem">
                                                            <i class="bi bi-check-circle me-1"></i>Selesai
                                                        </span>
                                                    <?php elseif ($t['sesi_aktif_id']): ?>
                                                        <a href="<?= base_url('user/tryout/sesi/' . $t['sesi_aktif_id'] . '/soal/1') ?>"
                                                           class="btn btn-warning btn-sm py-1 px-2 fw-semibold" style="font-size:.75rem">
                                                            <i class="bi bi-play-circle me-1"></i>Lanjut
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/tryout/' . $t['id'] . '/mulai') ?>"
                                                           class="btn btn-primary btn-sm py-1 px-2 fw-semibold" style="font-size:.75rem">
                                                            <i class="bi bi-play-fill me-1"></i>Mulai
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
