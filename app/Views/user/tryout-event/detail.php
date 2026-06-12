<?= $this->extend('layouts/main') ?>

<?php
// Siapkan data SEO
$_seoSlug  = $event['slug'] ?? $event['id'];
$_seoTitle = esc($event['nama']) . ' — Tryout Event Gratis CPNS | SiapASN';
$_seoDesc  = ! empty($event['deskripsi'])
    ? substr(strip_tags($event['deskripsi']), 0, 155) . '...'
    : 'Ikuti ' . esc($event['nama']) . ' — tryout event CPNS gratis bersama ribuan peserta. Uji kemampuan dan lihat posisi ranking Anda di SiapASN Simulation Center.';
$_seoUrl   = base_url('user/tryout-event/' . $_seoSlug);
$_seoImage = ! empty($event['banner_url'])
    ? base_url($event['banner_url'])
    : base_url('assets/images/thumbnail/product-default.png');
?>

<?= $this->section('seo_title') ?><?= $_seoTitle ?><?= $this->endSection() ?>

<?= $this->section('seo_meta') ?>
<meta name="robots" content="index, follow">
<meta name="description" content="<?= esc($_seoDesc) ?>">
<meta name="keywords" content="tryout event CPNS, <?= esc($event['nama']) ?>, simulasi CAT CPNS gratis, tryout online CPNS, SiapASN">
<link rel="canonical" href="<?= $_seoUrl ?>">
<!-- Open Graph -->
<meta property="og:type"        content="event">
<meta property="og:title"       content="<?= esc($_seoTitle) ?>">
<meta property="og:description" content="<?= esc($_seoDesc) ?>">
<meta property="og:url"         content="<?= $_seoUrl ?>">
<meta property="og:image"       content="<?= $_seoImage ?>">
<meta property="og:site_name"   content="SiapASN Simulation Center">
<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= esc($_seoTitle) ?>">
<meta name="twitter:description" content="<?= esc($_seoDesc) ?>">
<meta name="twitter:image"       content="<?= $_seoImage ?>">
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-calendar-event"></i></div>
    <div>
        <div class="ph-title"><?= esc($event['nama']) ?></div>
        <div class="ph-subtitle">Detail Event Tryout</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-3">
    <a href="<?= base_url('user/tryout-event') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <?php
    $shareTitle = $event['nama'];
    $shareUrl   = base_url('user/tryout-event/' . ($event['slug'] ?: $event['id']));
    $shareText  = 'Event Tryout CPNS - ' . $event['nama'];
    echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText'));
    ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Banner -->
        <?php if (! empty($event['banner_url'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius:.75rem;overflow:hidden">
            <img src="<?= base_url($event['banner_url']) ?>" alt="<?= esc($event['nama']) ?>" class="w-100" style="max-height:300px;object-fit:cover">
        </div>
        <?php endif; ?>

        <!-- Info Event -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Event</h6>
            </div>
            <div class="card-body">
                <?php if (! empty($event['deskripsi'])): ?>
                    <p><?= esc($event['deskripsi']) ?></p>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Tryout</div>
                        <div class="fw-semibold"><?= esc($tryout['nama']) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Durasi</div>
                        <div class="fw-semibold"><?= (int) $tryout['durasi'] ?> menit</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Jumlah Soal</div>
                        <div class="fw-semibold"><?= (int) $tryout['jumlah_soal'] ?> soal</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Max Percobaan</div>
                        <div class="fw-semibold"><?= (int) $event['max_percobaan'] ?>x</div>
                    </div>
                </div>

                <hr>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="text-muted small mb-1"><i class="bi bi-play-circle me-1"></i>Pelaksanaan</div>
                        <div class="small">
                            <?= date('d M Y H:i', strtotime($event['mulai_pelaksanaan'])) ?>
                            - <?= date('d M Y H:i', strtotime($event['tutup_pelaksanaan'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard link -->
        <a href="<?= base_url("user/tryout-event/" . ($event['slug'] ?: $event['id']) . "/leaderboard") ?>"
           class="btn btn-outline-warning w-100 fw-semibold mb-4">
            <i class="bi bi-trophy me-1"></i>Lihat Leaderboard Event
        </a>
    </div>

    <!-- Sidebar Aksi -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;border-radius:.75rem">
            <div class="card-body">
                <!-- Status -->
                <div class="text-center mb-3">
                    <?php
                    switch ($fase) {
                        case 'menunggu':
                            echo '<span class="badge bg-warning text-dark px-3 py-2">Menunggu Pelaksanaan</span>';
                            break;
                        case 'pelaksanaan':
                            echo '<span class="badge bg-success px-3 py-2">Sedang Berlangsung</span>';
                            break;
                        case 'selesai':
                            echo '<span class="badge bg-secondary px-3 py-2">Event Selesai</span>';
                            break;
                    }
                    ?>
                </div>

                <div class="text-center mb-3">
                    <div class="fs-3 fw-bold" style="color:#1a3a5c"><?= $totalPeserta ?></div>
                    <div class="text-muted small">Peserta Mengikuti</div>
                </div>

                <hr>
                <?php if ($fase === 'pelaksanaan'): ?>
                    <?php if ($userPercobaan >= (int) $event['max_percobaan']): ?>
                        <div class="alert alert-warning py-2 text-center small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Anda sudah menggunakan semua percobaan (<?= $event['max_percobaan'] ?>x).
                        </div>
                    <?php elseif ($sesiAktif): ?>
                        <a href="<?= base_url('user/tryout/jawab/' . $sesiAktif['id'] . '?soal_index=1') ?>"
                           class="btn btn-warning w-100 fw-semibold">
                            <i class="bi bi-play-circle me-1"></i>Lanjutkan Tryout
                        </a>
                    <?php else: ?>
                        <form method="post" action="<?= base_url("user/tryout-event/{$event['id']}/mulai") ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success w-100 fw-semibold"
                                    onclick="return confirm('Mulai tryout sekarang? Timer akan langsung berjalan.')">
                                <i class="bi bi-play-fill me-1"></i>Mulai Tryout
                            </button>
                        </form>
                        <div class="text-muted small text-center mt-2">
                            Percobaan: <?= $userPercobaan ?>/<?= $event['max_percobaan'] ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ($fase === 'menunggu'): ?>
                    <div class="alert alert-info py-2 text-center small mb-0">
                        <i class="bi bi-clock me-1"></i>Pelaksanaan dimulai<br>
                        <strong><?= date('d M Y H:i', strtotime($event['mulai_pelaksanaan'])) ?></strong>
                    </div>
                <?php elseif ($fase === 'selesai'): ?>
                    <div class="alert alert-secondary py-2 text-center small mb-0">
                        Event sudah selesai.
                    </div>
                <?php endif; ?>

                <!-- Hasil user -->
                    <?php if ($hasilUser): ?>
                    <hr>
                    <div class="text-center mb-3">
                        <div class="text-muted small mb-1">Skor Terbaik Anda</div>
                        <div class="fs-3 fw-bold text-success"><?= (int) ($hasilUser['total_nilai'] ?: $hasilUser['skor_total']) ?></div>
                        <?php if ($hasilUser['status_lulus'] === 'lulus'): ?>
                            <span class="badge bg-success">Lulus</span>
                        <?php elseif ($hasilUser['status_lulus'] === 'tidak_lulus'): ?>
                            <span class="badge bg-danger">Belum Lulus</span>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Hasil & Pembahasan -->
                    <div class="d-flex flex-column gap-2 mb-3">
                        <a href="<?= base_url('user/tryout/hasil/' . $hasilUser['sesi_tryout_id']) ?>"
                           class="btn btn-outline-success btn-sm fw-semibold">
                            <i class="bi bi-clipboard-data me-1"></i>Lihat Hasil
                        </a>
                        <a href="<?= base_url('user/tryout/pembahasan/' . $hasilUser['sesi_tryout_id']) ?>"
                           class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="bi bi-book me-1"></i>Lihat Pembahasan
                        </a>
                    </div>

                    <!-- Riwayat semua percobaan -->
                    <?php if (count($semuaHasil) > 1): ?>
                    <div class="border-top pt-3">
                        <div class="text-muted small fw-semibold mb-2">
                            <i class="bi bi-clock-history me-1"></i>Riwayat Percobaan
                        </div>
                        <?php foreach ($semuaHasil as $idx => $h): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 <?= $idx < count($semuaHasil) - 1 ? 'border-bottom' : '' ?>">
                            <div>
                                <div class="small fw-medium">Percobaan <?= count($semuaHasil) - $idx ?></div>
                                <div class="text-muted" style="font-size:.7rem">
                                    <?= date('d M Y H:i', strtotime($h['created_at'])) ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold small" style="color:#1a3a5c">
                                    <?= (int)($h['total_nilai'] ?: $h['skor_total']) ?>
                                </span>
                                <a href="<?= base_url('user/tryout/hasil/' . $h['sesi_tryout_id']) ?>"
                                   class="btn btn-outline-secondary btn-sm" style="font-size:.7rem;padding:2px 8px"
                                   title="Lihat Hasil">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url('user/tryout/pembahasan/' . $h['sesi_tryout_id']) ?>"
                                   class="btn btn-outline-secondary btn-sm" style="font-size:.7rem;padding:2px 8px"
                                   title="Lihat Pembahasan">
                                    <i class="bi bi-book"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
