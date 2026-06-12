<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-calendar-event"></i></div>
    <div>
        <div class="ph-title">Tryout Event</div>
        <div class="ph-subtitle">Event tryout gratis & kompetitif</div>
        <div class="ph-accent-line"></div>
    </div>
    <a href="<?= base_url('user/tryout-event/kalender') ?>" class="ms-auto btn btn-sm" style="background:var(--sa-accent);color:var(--sa-primary-dk);font-weight:600;border:none">
        <i class="bi bi-calendar3 me-1"></i>Lihat Kalender
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.event-card { border-radius: .75rem !important; overflow: hidden; transition: transform .18s, box-shadow .18s; }
.event-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.2rem rgba(0,0,0,.1) !important; }
.event-banner { aspect-ratio: 16/7; overflow: hidden; background: linear-gradient(135deg, #1a3a5c, #2d6a9f); display: flex; align-items: center; justify-content: center; }
.event-banner img { width: 100%; height: 100%; object-fit: cover; }
.event-banner .placeholder-icon { font-size: 2.5rem; color: rgba(255,255,255,.4); }
.fase-badge { font-size: .68rem; font-weight: 600; }
</style>

<!-- Event Aktif -->
<?php if (! empty($events)): ?>
<h6 class="fw-bold mb-3" style="color:#1a3a5c"><i class="bi bi-lightning-charge me-1"></i>Event Tersedia</h6>
<div class="row g-3 mb-4">
    <?php foreach ($events as $e): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 event-card">
            <div class="event-banner position-relative">
                <?php if (! empty($e['banner_url'])): ?>
                    <img src="<?= base_url($e['banner_url']) ?>" alt="<?= esc($e['nama']) ?>">
                <?php else: ?>
                    <i class="bi bi-calendar-event placeholder-icon"></i>
                <?php endif; ?>
                <!-- Fase badge -->
                <span class="position-absolute top-0 end-0 m-2 badge fase-badge
                    <?php
                    switch ($e['fase']) {
                        case 'menunggu': echo 'bg-warning text-dark'; break;
                        case 'pelaksanaan': echo 'bg-success'; break;
                        case 'selesai': echo 'bg-secondary'; break;
                        default: echo 'bg-dark'; break;
                    }
                    ?>">
                    <?php
                    switch ($e['fase']) {
                        case 'menunggu': echo 'Menunggu Pelaksanaan'; break;
                        case 'pelaksanaan': echo 'Sedang Berlangsung'; break;
                        case 'selesai': echo 'Selesai'; break;
                    }
                    ?>
                </span>
            </div>
            <div class="card-body d-flex flex-column p-3">
                <h6 class="fw-bold mb-2" style="font-size:.9rem;color:#1a3a5c"><?= esc($e['nama']) ?></h6>

                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.65rem">
                        <i class="bi bi-people me-1"></i><?= (int) $e['total_peserta'] ?> peserta
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size:.65rem">
                        <i class="bi bi-clock me-1"></i><?= (int) $e['durasi'] ?> menit
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle" style="font-size:.65rem">
                        <i class="bi bi-arrow-repeat me-1"></i><?= (int) $e['max_percobaan'] ?>x percobaan
                    </span>
                </div>

                <div class="small text-muted mb-3">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= date('d M Y H:i', strtotime($e['mulai_pelaksanaan'])) ?> — <?= date('d M Y H:i', strtotime($e['tutup_pelaksanaan'])) ?>
                </div>

                <div class="mt-auto">
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('user/tryout-event/' . ($e['slug'] ?: $e['id'])) ?>"
                           class="btn btn-primary btn-sm flex-grow-1 fw-semibold" style="border-radius:.5rem">
                            <i class="bi bi-arrow-right me-1"></i>Detail
                        </a>
                        <?php
                        $shareTitle = $e['nama'];
                        $shareUrl   = base_url('user/tryout-event/' . ($e['slug'] ?: $e['id']));
                        $shareText  = 'Event Tryout CPNS Gratis - ' . $e['nama'];
                        $shareBtnClass = 'btn-outline-secondary';
                        echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText', 'shareBtnClass'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Event Selesai (Riwayat) -->
<?php if (! empty($eventSelesai)): ?>
<h6 class="fw-bold mb-3 text-muted"><i class="bi bi-clock-history me-1"></i>Event Selesai</h6>
<div class="row g-3">
    <?php foreach ($eventSelesai as $es): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;opacity:.85">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-2" style="font-size:.85rem"><?= esc($es['nama']) ?></h6>
                <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-secondary rounded-pill" style="font-size:.65rem"><?= (int) $es['total_peserta'] ?> peserta</span>
                    <?php if ($es['user_registered']): ?>
                        <span class="badge bg-success rounded-pill" style="font-size:.65rem">Anda ikut</span>
                    <?php endif; ?>
                </div>
                <a href="<?= base_url('user/tryout-event/' . ($es['slug'] ?: $es['id']) . '/leaderboard') ?>"
                   class="btn btn-outline-secondary btn-sm w-100" style="font-size:.78rem">
                    <i class="bi bi-trophy me-1"></i>Lihat Leaderboard
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($events) && empty($eventSelesai)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-calendar-event fs-1 d-block mb-3"></i>
        <p class="mb-0">Belum ada event tryout yang tersedia saat ini.</p>
        <p class="small">Pantau terus halaman ini untuk event tryout gratis berikutnya.</p>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
