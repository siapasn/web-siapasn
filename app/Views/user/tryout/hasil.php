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
                $totalNilai = (int) ($hasil['total_nilai'] ?? 0);
                $maxNilai   = (int) ($hasil['max_nilai']   ?? 0);
                $skor       = (float) ($hasil['skor_total'] ?? 0);

                // Fallback: hitung dari detail_kategori jika total_nilai masih 0
                if ($totalNilai === 0 && ! empty($detailKategori)) {
                    foreach ($detailKategori as $kat) {
                        $totalNilai += (int) ($kat['total_nilai'] ?? 0);
                        $maxNilai   += (int) ($kat['max_nilai']   ?? 0);
                    }
                }

                $skorClass  = $skor >= 70 ? 'text-success' : ($skor >= 50 ? 'text-warning' : 'text-danger');
                ?>

                <!-- Total Poin -->
                <div class="display-3 fw-bold <?= $skorClass ?>">
                    <?= $totalNilai ?>
                </div>
                <div class="text-muted small mb-1">Total Poin</div>

                <!-- Status Lulus — Banner besar -->
                <?php if ($statusLulus === 'lulus'): ?>
                    <div class="alert alert-success py-3 mb-3 mt-3">
                        <div class="fs-5 fw-bold"><i class="bi bi-patch-check-fill me-2"></i>LULUS</div>
                        <div class="small mt-1">Selamat! Semua nilai sub kategori memenuhi passing grade.</div>
                    </div>
                <?php elseif ($statusLulus === 'tidak_lulus'): ?>
                    <div class="alert alert-danger py-3 mb-3 mt-3">
                        <div class="fs-5 fw-bold"><i class="bi bi-x-circle-fill me-2"></i>TIDAK LULUS</div>
                        <div class="small mt-1">Ada sub kategori yang belum memenuhi passing grade.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik Benar / Salah / Kosong -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bar-chart-fill me-1 text-primary"></i> Ringkasan Jawaban
            </div>
            <div class="card-body">
                <?php
                // Cek apakah semua soal bertipe POINT (tidak ada benar/salah)
                $semuaPoint = true;
                foreach ($detailKategori as $dk) {
                    if (($dk['tipe_soal'] ?? '') !== 'POINT') { $semuaPoint = false; break; }
                }
                ?>
                <?php if ($semuaPoint): ?>
                    <!-- Tipe POINT: tidak ada benar/salah -->
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 rounded bg-primary bg-opacity-10">
                                <div class="fs-3 fw-bold text-primary"><?= (int) ($hasil['total_nilai'] ?? 0) ?></div>
                                <div class="small text-muted">Total Nilai</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-secondary bg-opacity-10">
                                <div class="fs-3 fw-bold text-secondary"><?= (int) ($hasil['jumlah_kosong'] ?? 0) ?></div>
                                <div class="small text-muted">Kosong</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tipe SCORE: ada benar/salah -->
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
                <?php endif; ?>
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
                                    Min: <?= number_format($pg['nilai_minimum'], 0) ?> poin
                                    &bull; Nilai: <?= number_format($pg['total_nilai'] ?? 0, 0) ?> poin
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
                        $katSkor       = (float) ($kat['skor']        ?? 0);
                        $totalNilaiKat = (int)   ($kat['total_nilai'] ?? 0);
                        $tipeSoal      = $kat['tipe_soal'] ?? 'POINT';

                        // Cari passing grade untuk sub kategori ini
                        $pgNilaiMin = null;
                        $pgStatus   = null;
                        foreach ($detailPassingGrade as $pg) {
                            $matchSub = ! empty($kat['sub_kategori_id'])
                                && (int)($pg['sub_kategori_id'] ?? 0) === (int)$kat['sub_kategori_id'];
                            $matchKat = empty($kat['sub_kategori_id'])
                                && (int)($pg['sub_kategori_id'] ?? 0) === (int)$kat['kategori_id'];
                            $matchKatDirect = empty($kat['sub_kategori_id'])
                                && (int)($pg['kategori_id'] ?? 0) === (int)$kat['kategori_id'];
                            if ($matchSub || $matchKat || $matchKatDirect) {
                                $pgNilaiMin = (int) $pg['nilai_minimum'];
                                $pgStatus   = $pg['lulus'];
                                break;
                            }
                        }

                        // Progress bar: poin aktual vs passing grade (jika ada), else vs max
                        $barMax   = $pgNilaiMin ?? (int)($kat['max_nilai'] ?? 0);
                        $barPct   = $barMax > 0 ? min(100, round(($totalNilaiKat / $barMax) * 100)) : 0;
                        $barClass = $pgStatus === true ? 'bg-success' : ($pgStatus === false ? 'bg-danger' : ($katSkor >= 70 ? 'bg-success' : ($katSkor >= 50 ? 'bg-warning' : 'bg-danger')));
                    ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <!-- Nama + badge tipe + badge PG -->
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold"><?= esc($kat['sub_kategori_nama'] ?? $kat['kategori_nama']) ?></span>
                                    <?php if ($tipeSoal === 'SCORE'): ?>
                                        <span class="badge bg-info text-dark" style="font-size:.65rem">SCORE</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark" style="font-size:.65rem">POINT</span>
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

                                <!-- Nilai: total_poin / passing_grade -->
                                <div class="text-end d-flex align-items-center gap-2">
                                    <span class="fw-bold" style="font-size:.95rem">
                                        <?= $totalNilaiKat ?>
                                        <?php if ($pgNilaiMin !== null): ?>
                                            <span class="text-muted fw-normal" style="font-size:.78rem">/ <?= $pgNilaiMin ?></span>
                                        <?php elseif (($kat['max_nilai'] ?? 0) > 0): ?>
                                            <span class="text-muted fw-normal" style="font-size:.78rem">/ <?= (int)$kat['max_nilai'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <!-- <span class="badge <?= $barClass ?>" style="font-size:.68rem">
                                        <?= number_format($katSkor, 1) ?>%
                                    </span> -->
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="progress mb-1" style="height:8px" role="progressbar">
                                <div class="progress-bar <?= $barClass ?>" style="width:<?= $barPct ?>%"></div>
                            </div>

                            <!-- Info detail -->
                            <div class="small text-muted">
                                <?php if ($tipeSoal === 'POINT'): ?>
                                    Nilai dipilih: <?= $totalNilaiKat ?> poin
                                    &nbsp;|&nbsp; Total soal: <?= (int) $kat['total'] ?>
                                    &nbsp;|&nbsp; Kosong: <?= (int) $kat['kosong'] ?>
                                <?php else: ?>
                                    Benar: <?= (int) $kat['benar'] ?>
                                    &nbsp;|&nbsp; Salah: <?= (int) $kat['salah'] ?>
                                    &nbsp;|&nbsp; Kosong: <?= (int) $kat['kosong'] ?>
                                    &nbsp;|&nbsp; Total: <?= (int) $kat['total'] ?>
                                <?php endif; ?>
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
