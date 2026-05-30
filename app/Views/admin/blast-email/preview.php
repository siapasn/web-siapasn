<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-envelope-open"></i></div>
        <div>
            <div class="ph-title">Preview Email</div>
            <div class="ph-subtitle">Detail blast email yang sudah dikirim</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/blast-email') ?>" class="ph-action">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <!-- Meta Info -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Subject</div>
                <div class="fw-semibold"><?= esc($blast['subject']) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Tipe</div>
                <div>
                    <?php if ($blast['tipe'] === 'all'): ?>
                        <span class="badge bg-primary">Semua User</span>
                    <?php else: ?>
                        <span class="badge bg-info">User Tertentu</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Waktu Kirim</div>
                <div class="small"><?= date('d M Y H:i:s', strtotime($blast['created_at'])) ?></div>
            </div>
            <?php if ($blast['tipe'] === 'single' && ! empty($blast['target_email'])): ?>
            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Target Email</div>
                <div class="fw-medium"><?= esc($blast['target_email']) ?></div>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Berhasil</div>
                <div><span class="badge bg-success"><?= (int) $blast['total_sent'] ?></span></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Gagal</div>
                <div><span class="badge bg-danger"><?= (int) $blast['total_failed'] ?></span></div>
            </div>
        </div>

        <hr>

        <!-- Body Email -->
        <div class="small text-muted mb-2">Isi Email:</div>
        <div class="border rounded p-3 bg-light">
            <?= $blast['body'] ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
