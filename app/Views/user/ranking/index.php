<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-trophy"></i></div>
    <div>
        <div class="ph-title">Perangkingan</div>
        <div class="ph-subtitle">Lihat posisi Anda dibanding peserta lain</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.ranking-tryout-card {
    transition: transform .18s ease, box-shadow .18s ease;
    border-radius: .75rem !important;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}
.ranking-tryout-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1.2rem rgba(0,0,0,.1) !important;
    color: inherit;
}
</style>

<?php if (empty($tryoutByKategori)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-trophy fs-1 d-block mb-3"></i>
            <p class="mb-2">Belum ada data perangkingan yang dapat ditampilkan.</p>
            <?php if (! empty($activeSesi)): ?>
                <p class="small mb-3">
                    Anda masih memiliki sesi tryout yang belum selesai:
                    <strong><?= esc($activeSesi['event_nama'] ?: $activeSesi['tryout_nama']) ?></strong>.
                    Selesaikan sesi ini untuk membuka perangkingan.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= base_url('user/tryout/jawab/' . $activeSesi['sesi_id'] . '?soal_index=1') ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-play-circle me-1"></i>Lanjutkan Tryout
                    </a>
                    <?php if (! empty($activeSesi['event_id'])): ?>
                        <a href="<?= base_url('user/tryout-event/' . ($activeSesi['event_slug'] ?: $activeSesi['event_id'])) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-calendar-event me-1"></i>Lihat Event
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <p class="small mb-3">Perangkingan hanya tersedia untuk tryout yang sudah Anda kerjakan. Selesaikan tryout terlebih dahulu.</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= base_url('user/tryout') ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-play-circle me-1"></i>Mulai Tryout
                </a>
                <a href="<?= base_url('user/produk') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-cart me-1"></i>Lihat Paket
                </a>
                <a href="<?= base_url('user/tryout-event') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calendar-event me-1"></i>Lihat Event
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>

    <?php foreach ($tryoutByKategori as $kat): ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3" style="color:#1a3a5c">
            <i class="bi bi-folder me-2"></i><?= esc($kat['kat_nama']) ?>
        </h6>
        <div class="row g-3">
            <?php foreach ($kat['tryouts'] as $t): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= base_url('user/ranking/' . ($t['slug'] ?: $t['id'])) ?>" class="card border-0 shadow-sm h-100 ranking-tryout-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:rgba(245,166,35,.12)">
                                <i class="bi bi-trophy" style="font-size:1.2rem;color:#f5a623"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-semibold mb-1" style="font-size:.9rem;line-height:1.3"><?= esc($t['nama']) ?></h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.68rem">
                                        <i class="bi bi-people me-1"></i><?= (int)$t['total_peserta'] ?> peserta
                                    </span>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size:.68rem">
                                        <i class="bi bi-clock me-1"></i><?= (int)$t['durasi'] ?> menit
                                    </span>
                                    <?php if ((int)$t['skor_tertinggi'] > 0): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size:.68rem">
                                        <i class="bi bi-star me-1"></i>Top: <?= (int)$t['skor_tertinggi'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>

<?= $this->endSection() ?>
