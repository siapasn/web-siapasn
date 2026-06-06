<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-trophy"></i></div>
    <div>
        <div class="ph-title">Leaderboard Event</div>
        <div class="ph-subtitle"><?= esc($event['nama']) ?></div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.rank-row-me { background: rgba(245,166,35,.08) !important; border-left: 3px solid #f5a623 !important; }
.rank-badge { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.rank-badge-1 { background: linear-gradient(135deg, #f5a623, #ffd700); color: #fff; }
.rank-badge-2 { background: linear-gradient(135deg, #94a3b8, #cbd5e1); color: #fff; }
.rank-badge-3 { background: linear-gradient(135deg, #cd7f32, #daa520); color: #fff; }
</style>

<div class="mb-3">
    <a href="<?= base_url('user/tryout-event/' . ($event['slug'] ?: $event['id'])) ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Event
    </a>
</div>

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small"><i class="bi bi-people me-1"></i>Total Peserta</div>
                <div class="fs-4 fw-bold" style="color:#1a3a5c"><?= $totalPeserta ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small"><i class="bi bi-bar-chart me-1"></i>Sudah Selesai</div>
                <div class="fs-4 fw-bold text-success"><?= count($rankings) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small"><i class="bi bi-star me-1"></i>Skor Tertinggi</div>
                <div class="fs-4 fw-bold text-warning">
                    <?= ! empty($rankings) ? (int)($rankings[0]['best_total_nilai'] ?: $rankings[0]['best_skor_total']) : 0 ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small"><i class="bi bi-person-check me-1"></i>Peringkat Anda</div>
                <div class="fs-4 fw-bold" style="color:#f5a623"><?= $myRank ? '#' . $myRank : '—' ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Posisi Saya -->
<?php if ($myRank && $myData): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #f5a623 !important">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rank-badge rank-badge-1" style="width:40px;height:40px;font-size:.9rem">#<?= $myRank ?></div>
            <div class="flex-grow-1">
                <div class="fw-bold"><?= esc($myData['nama']) ?> <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Anda</span></div>
                <div class="text-muted small">
                    Skor: <strong><?= (int)($myData['best_total_nilai'] ?: $myData['best_skor_total']) ?></strong>
                    &bull; Benar: <?= (int) $myData['best_jumlah_benar'] ?>
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
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ol me-2"></i>Daftar Peringkat</h6>
    </div>

    <?php if (empty($rankings)): ?>
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-trophy fs-2 d-block mb-2"></i>
        <p class="mb-0">Belum ada peserta yang menyelesaikan event ini.</p>
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width:60px">Rank</th>
                        <th>Nama</th>
                        <th class="text-center">Skor</th>
                        <th class="text-center">Benar</th>
                        <th class="text-center">Detail Passing Grade</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $i => $r):
                        $rank = $i + 1;
                        $isMe = (int) $r['user_id'] === $userId;
                        $skor = (int)($r['best_total_nilai'] ?: $r['best_skor_total']);

                        // Parse detail passing grade
                        $detailPg = [];
                        if (! empty($r['detail_passing_grade'])) {
                            $decoded = json_decode($r['detail_passing_grade'], true);
                            if (is_array($decoded)) $detailPg = $decoded;
                        }
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
                            <td class="text-center"><?= (int) $r['best_jumlah_benar'] ?></td>
                            <td class="text-center">
                                <?php if (! empty($detailPg)): ?>
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        <?php foreach ($detailPg as $pg): ?>
                                            <span class="badge <?= $pg['lulus'] ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $pg['lulus'] ? 'text-success border-success-subtle' : 'text-danger border-danger-subtle' ?> border" style="font-size:.6rem">
                                                <?= esc($pg['label']) ?>: <?= (int) $pg['total_nilai'] ?>/<?= (int) $pg['nilai_minimum'] ?>
                                                <?= $pg['lulus'] ? '✓' : '✗' ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($r['status_lulus'] === 'lulus'): ?>
                                    <span class="badge bg-success fw-semibold" style="font-size:.7rem">
                                        <i class="bi bi-patch-check-fill me-1"></i>LULUS
                                    </span>
                                <?php elseif ($r['status_lulus'] === 'tidak_lulus'): ?>
                                    <span class="badge bg-danger fw-semibold" style="font-size:.7rem">
                                        <i class="bi bi-x-circle-fill me-1"></i>TIDAK LULUS
                                    </span>
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

<?= $this->endSection() ?>
