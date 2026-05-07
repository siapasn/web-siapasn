<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-trophy-fill"></i></div>
    <div>
        <div class="ph-title">Hasil Tryout</div>
        <div class="ph-subtitle"><?= esc($tryout['nama'] ?? 'Tryout') ?></div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$statusLulus        = $hasil['status_lulus'] ?? null;
$detailPassingGrade = [];
if (! empty($hasil['detail_passing_grade'])) {
    $decoded = json_decode($hasil['detail_passing_grade'], true);
    if (is_array($decoded)) $detailPassingGrade = $decoded;
}
?>

<div class="row g-4">

    <!-- Kolom kiri: skor & statistik -->
    <div class="col-lg-4">

        <!-- Skor Total + Status Lulus -->
        <div class="card border-0 shadow-sm mb-4 text-center">
            <div class="card-body py-4">
                <?php
                $skor      = (float) ($hasil['skor_total'] ?? 0);
                $skorClass = $skor >= 70 ? 'text-success' : ($skor >= 50 ? 'text-warning' : 'text-danger');
                ?>
                <div class="display-3 fw-bold <?= $skorClass ?>">
                    <?= number_format($skor, 1) ?>
                </div>
                <div class="text-muted mt-1 mb-3">Skor Total</div>

                <!-- Status Lulus -->
                <?php if ($statusLulus === 'lulus'): ?>
                    <div class="alert alert-success py-2 mb-3">
                        <i class="bi bi-patch-check-fill me-1"></i>
                        <strong>LULUS</strong> — Memenuhi semua passing grade
                    </div>
                <?php elseif ($statusLulus === 'tidak_lulus'): ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        <strong>TIDAK LULUS</strong> — Belum memenuhi passing grade
                    </div>
                <?php endif; ?>

                <!-- Peringkat -->
                <?php if (! empty($hasil['peringkat'])): ?>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-award-fill me-1"></i>
                        Peringkat #<?= (int) $hasil['peringkat'] ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik Benar / Salah / Kosong -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bar-chart-fill me-1 text-primary"></i> Ringkasan Jawaban
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="p-3 rounded bg-success bg-opacity-10">
                            <div class="fs-3 fw-bold text-success"><?= (int) ($hasil['jumlah_benar'] ?? 0) ?></div>
                            <div class="small text-muted">Benar</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded bg-danger bg-opacity-10">
                            <div class="fs-3 fw-bold text-danger"><?= (int) ($hasil['jumlah_salah'] ?? 0) ?></div>
                            <div class="small text-muted">Salah</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded bg-secondary bg-opacity-10">
                            <div class="fs-3 fw-bold text-secondary"><?= (int) ($hasil['jumlah_kosong'] ?? 0) ?></div>
                            <div class="small text-muted">Kosong</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Passing Grade Detail -->
        <?php if (! empty($detailPassingGrade)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-clipboard-check me-1 text-primary"></i> Passing Grade
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($detailPassingGrade as $pg): ?>
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                            <div>
                                <div class="fw-semibold small"><?= esc($pg['label']) ?></div>
                                <div class="text-muted" style="font-size:.72rem">
                                    Min: <?= number_format($pg['nilai_minimum'], 1) ?>%
                                    &bull; Nilai: <?= number_format($pg['skor_aktual'], 1) ?>%
                                </div>
                            </div>
                            <?php if ($pg['lulus']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem">
                                    <i class="bi bi-check-circle me-1"></i>Lulus
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.7rem">
                                    <i class="bi bi-x-circle me-1"></i>Tidak Lulus
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tombol Aksi -->
        <div class="d-grid gap-2">
            <a href="<?= base_url('user/tryout/pembahasan/' . $sesi['id']) ?>"
               class="btn btn-primary">
                <i class="bi bi-book-fill me-1"></i> Lihat Pembahasan
            </a>
            <a href="<?= base_url('user/tryout') ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Paket Saya
            </a>
        </div>

    </div>

    <!-- Kolom kanan: detail per sub kategori -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-list-check me-1 text-primary"></i> Nilai per Sub Kategori
            </div>
            <div class="card-body">

                <?php if (! empty($detailKategori)): ?>
                    <?php foreach ($detailKategori as $kat):
                        $katSkor     = (float) ($kat['skor'] ?? 0);
                        $barClass    = $katSkor >= 70 ? 'bg-success' : ($katSkor >= 50 ? 'bg-warning' : 'bg-danger');
                        $badgeClass  = $katSkor >= 70 ? 'bg-success' : ($katSkor >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                        $tipeSoal    = $kat['tipe_soal'] ?? 'POINT';

                        // Cek apakah sub kategori ini lulus passing grade
                        $pgStatus = null;
                        foreach ($detailPassingGrade as $pg) {
                            if (! empty($kat['sub_kategori_id']) && (int)($pg['sub_kategori_id'] ?? 0) === (int)$kat['sub_kategori_id']) {
                                $pgStatus = $pg['lulus'];
                                break;
                            }
                        }
                    ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold"><?= esc($kat['sub_kategori_nama'] ?? $kat['kategori_nama']) ?></span>
                                    <?php if ($tipeSoal === 'SCORE'): ?>
                                        <span class="badge bg-warning text-dark" style="font-size:.65rem">SCORE</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary bg-opacity-75" style="font-size:.65rem">POINT</span>
                                    <?php endif; ?>
                                    <?php if ($pgStatus === true): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.65rem">
                                            <i class="bi bi-check-circle me-1"></i>Lulus PG
                                        </span>
                                    <?php elseif ($pgStatus === false): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.65rem">
                                            <i class="bi bi-x-circle me-1"></i>Tidak Lulus PG
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= number_format($katSkor, 1) ?>%
                                </span>
                            </div>
                            <div class="progress mb-1" style="height:10px" role="progressbar">
                                <div class="progress-bar <?= $barClass ?>" style="width:<?= $katSkor ?>%"></div>
                            </div>
                            <div class="small text-muted">
                                <?php if ($tipeSoal === 'SCORE'): ?>
                                    Nilai: <?= (int) ($kat['total_nilai'] ?? 0) ?>/<?= (int) ($kat['max_nilai'] ?? 0) ?>
                                    &nbsp;|&nbsp;
                                <?php endif; ?>
                                Benar: <?= (int) $kat['benar'] ?>
                                &nbsp;|&nbsp; Salah: <?= (int) $kat['salah'] ?>
                                &nbsp;|&nbsp; Kosong: <?= (int) $kat['kosong'] ?>
                                &nbsp;|&nbsp; Total: <?= (int) $kat['total'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Data kategori tidak tersedia.
                    </p>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
