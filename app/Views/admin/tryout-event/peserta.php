<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-people"></i></div>
        <div>
            <div class="ph-title">Peserta Event</div>
            <div class="ph-subtitle"><?= esc($event['nama']) ?></div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/tryout-event') ?>" class="ph-action">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (! empty($peserta)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <span class="fw-semibold">Total Peserta: <span class="badge bg-primary"><?= count($peserta) ?></span></span>
        <?php
        $jumlahLulus = count(array_filter($peserta, fn($p) => $p['status_lulus'] === 'lulus'));
        $jumlahTidakLulus = count(array_filter($peserta, fn($p) => $p['status_lulus'] === 'tidak_lulus'));
        ?>
        <span class="badge bg-success ms-2"><?= $jumlahLulus ?> Lulus</span>
        <span class="badge bg-danger ms-1"><?= $jumlahTidakLulus ?> Tidak Lulus</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">Rank</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th class="text-center">Total Nilai</th>
                        <th class="text-center">Status</th>
                        <th>Mulai Ikut</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta as $i => $p): ?>
                    <?php
                        $detailPg = [];
                        if (! empty($p['detail_passing_grade'])) {
                            $decoded = json_decode($p['detail_passing_grade'], true);
                            if (is_array($decoded)) $detailPg = $decoded;
                        }
                    ?>
                        <tr>
                            <td class="ps-3 fw-semibold text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($p['nama']) ?></td>
                            <td class="small text-muted"><?= esc($p['email']) ?></td>
                            <td class="text-center fw-bold">
                                <?= $p['total_nilai'] ? (int) $p['total_nilai'] : ($p['skor_total'] ? number_format($p['skor_total'], 1) : '—') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['status_lulus'] === 'lulus'): ?>
                                    <span class="badge bg-success fw-semibold">
                                        <i class="bi bi-patch-check-fill me-1"></i>LULUS
                                    </span>
                                <?php elseif ($p['status_lulus'] === 'tidak_lulus'): ?>
                                    <span class="badge bg-danger fw-semibold">
                                        <i class="bi bi-x-circle-fill me-1"></i>TIDAK LULUS
                                    </span>
                                <?php elseif ($p['status'] === 'started'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill">Mengerjakan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Belum Mulai</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('d M Y H:i', strtotime($p['registered_at'])) ?></td>
                            <td class="text-center pe-3">
                                <?php if (! empty($p['sesi_tryout_id'])): ?>
                                    <form method="post" action="<?= base_url('admin/tryout-event/' . $event['id'] . '/peserta/' . $p['id'] . '/reset') ?>" class="d-inline" onsubmit="return confirm('Reset jawaban dan penilaian peserta ini? Data hasil akan dihapus dan user dapat tes ulang.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-people fs-2 d-block mb-2"></i>
        Belum ada peserta yang mengikuti event ini.
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
