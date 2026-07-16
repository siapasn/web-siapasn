<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-trophy"></i></div>
    <div>
        <div class="ph-title">Leaderboard</div>
        <div class="ph-subtitle"><?= esc($tryout['nama']) ?></div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.podium-card { border-radius: .75rem !important; overflow: hidden; }
.rank-row-me { background: rgba(245,166,35,.08) !important; border-left: 3px solid #f5a623 !important; }
.rank-badge-1 { background: linear-gradient(135deg, #f5a623, #ffd700); color: #fff; }
.rank-badge-2 { background: linear-gradient(135deg, #94a3b8, #cbd5e1); color: #fff; }
.rank-badge-3 { background: linear-gradient(135deg, #cd7f32, #daa520); color: #fff; }
.rank-badge { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
</style>

<div class="mb-3">
    <a href="<?= base_url('user/ranking') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <?php
    $shareTitle = 'Leaderboard - ' . $tryout['nama'];
    $shareUrl   = base_url('user/ranking/' . ($tryout['slug'] ?: $tryout['id']));
    $shareText  = 'Lihat peringkat saya di tryout CPNS: ' . $tryout['nama'];
    echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText'));
    ?>
    <?php if (! empty($ulasanContext['can_review'])): ?>
        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalUlasan">
            <i class="bi bi-star-half me-1"></i>Beri Ulasan
        </button>
    <?php elseif (! empty($ulasanContext['has_reviewed'])): ?>
        <button type="button" class="btn btn-sm btn-outline-success" disabled>
            <i class="bi bi-check-circle me-1"></i>Sudah Ulasan
        </button>
    <?php endif; ?>
</div>

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1"><i class="bi bi-people me-1"></i>Total Peserta</div>
                <div class="fs-4 fw-bold" style="color:#1a3a5c"><?= $totalPeserta ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1"><i class="bi bi-star me-1"></i>Skor Tertinggi</div>
                <div class="fs-4 fw-bold text-success"><?= $skorTertinggi ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1"><i class="bi bi-bar-chart me-1"></i>Rata-rata</div>
                <div class="fs-4 fw-bold text-primary"><?= $skorRataRata ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1"><i class="bi bi-person-check me-1"></i>Peringkat Anda</div>
                <div class="fs-4 fw-bold" style="color:#f5a623">
                    <?= $myRank ? '#' . $myRank : '—' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Posisi Saya (jika ada) -->
<?php if ($myRank && $myData): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #f5a623 !important">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rank-badge rank-badge-1" style="width:40px;height:40px;font-size:.9rem">
                #<?= $myRank ?>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold"><?= esc($myData['nama']) ?> <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Anda</span></div>
                <div class="text-muted small">
                    Skor: <strong><?= (int)($myData['best_total_nilai'] ?: $myData['best_skor_total']) ?></strong>
                    &bull; <?= (int)$myData['total_percobaan'] ?> percobaan
                    &bull; Benar: <?= (int)$myData['best_jumlah_benar'] ?>
                </div>
            </div>
            <?php if ($myData['status_lulus'] === 'lulus'): ?>
                <span class="badge bg-success">Lulus</span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Ranking -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ol me-2"></i>Daftar Peringkat</h6>
        <span class="badge bg-primary rounded-pill"><?= $totalPeserta ?> peserta</span>
    </div>

    <?php if (empty($rankings)): ?>
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-trophy fs-2 d-block mb-2"></i>
        <p class="mb-0">Belum ada peserta yang menyelesaikan tryout ini.</p>
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width:60px">Rank</th>
                        <th>Nama Peserta</th>
                        <th class="text-center">Skor</th>
                        <th class="text-center">Benar</th>
                        <th class="text-center">Percobaan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $i => $r):
                        $rank = $i + 1;
                        $isMe = (int)$r['user_id'] === $userId;
                        $skor = (int)($r['best_total_nilai'] ?: $r['best_skor_total']);
                    ?>
                        <tr class="<?= $isMe ? 'rank-row-me' : '' ?>">
                            <td class="ps-3 text-center">
                                <?php if ($rank === 1): ?>
                                    <span class="rank-badge rank-badge-1"><i class="bi bi-trophy-fill"></i></span>
                                <?php elseif ($rank === 2): ?>
                                    <span class="rank-badge rank-badge-2"><?= $rank ?></span>
                                <?php elseif ($rank === 3): ?>
                                    <span class="rank-badge rank-badge-3"><?= $rank ?></span>
                                <?php else: ?>
                                    <span class="text-muted fw-semibold"><?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-medium"><?= esc($r['nama']) ?></span>
                                <?php if ($isMe): ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold" style="color:#1a3a5c"><?= $skor ?></td>
                            <td class="text-center"><?= (int)$r['best_jumlah_benar'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill"><?= (int)$r['total_percobaan'] ?>x</span>
                            </td>
                            <td class="text-center">
                                <?php if ($r['status_lulus'] === 'lulus'): ?>
                                    <span class="badge bg-success rounded-pill" style="font-size:.68rem">Lulus</span>
                                <?php elseif ($r['status_lulus'] === 'tidak_lulus'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill" style="font-size:.68rem">Belum Lulus</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= view('partials/ulasan-modal', [
    'ulasanContext' => $ulasanContext ?? [],
    'modalId'       => 'modalUlasan',
    'ratingId'      => 'ratingInputUlasanRanking',
    'starsId'       => 'ratingStarsUlasanRanking',
    'submitId'      => 'btnSubmitUlasanRanking',
]) ?>

<?= $this->endSection() ?>
