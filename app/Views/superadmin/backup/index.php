<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-database-down"></i></div>
        <div>
            <div class="ph-title">Backup &amp; Restore</div>
            <div class="ph-subtitle">Kelola backup dan pemulihan data</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- ================================================================ -->
    <!-- Kolom Kiri: Buat Backup + Restore -->
    <!-- ================================================================ -->
    <div class="col-12 col-lg-5">

        <!-- Buat Backup -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cloud-download me-2 text-primary"></i> Buat Backup
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Buat salinan lengkap database saat ini. File backup akan disimpan di server
                    dan otomatis diunduh ke komputer Anda.
                </p>
                <form method="post" action="<?= base_url('superadmin/backup/create') ?>"
                      id="formBackup" onsubmit="return konfirmasiBackup()">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary w-100" id="btnBackup">
                        <i class="bi bi-database-down me-1"></i> Buat Backup Sekarang
                    </button>
                </form>
                <div class="alert alert-info d-flex align-items-start gap-2 mt-3 mb-0 small" role="alert">
                    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        Proses backup mungkin memerlukan beberapa saat tergantung ukuran database.
                        Jangan tutup halaman ini selama proses berlangsung.
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Database -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cloud-upload me-2 text-warning"></i> Restore Database
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Peringatan:</strong> Restore akan menimpa data yang ada saat ini.
                        Pastikan Anda sudah membuat backup terbaru sebelum melanjutkan.
                    </div>
                </div>
                <form method="post" action="<?= base_url('superadmin/backup/restore') ?>"
                      enctype="multipart/form-data"
                      onsubmit="return konfirmasiRestore()">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="sql_file" class="form-label small fw-medium">
                            Pilih File SQL <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="sql_file" name="sql_file"
                               class="form-control form-control-sm"
                               accept=".sql" required>
                        <div class="form-text">Hanya file .sql yang diperbolehkan.</div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Database
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- ================================================================ -->
    <!-- Kolom Kanan: Daftar File Backup -->
    <!-- ================================================================ -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-archive me-2 text-secondary"></i> File Backup Tersimpan
                </h6>
                <span class="badge bg-secondary rounded-pill"><?= count($backupFiles) ?> file</span>
            </div>
            <div class="card-body p-0">
                <?php if (! empty($backupFiles)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama File</th>
                                    <th class="text-end">Ukuran</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backupFiles as $bf): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <i class="bi bi-file-earmark-code text-muted me-1"></i>
                                            <span class="font-monospace small"><?= esc($bf['name']) ?></span>
                                        </td>
                                        <td class="text-end text-muted small"><?= esc($bf['size']) ?></td>
                                        <td class="text-muted small"><?= esc($bf['modified']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-archive fs-2 d-block mb-2"></i>
                        Belum ada file backup tersimpan
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /.row -->

<script>
function konfirmasiBackup() {
    const btn = document.getElementById('btnBackup');
    if (! confirm('Buat backup database sekarang?\n\nFile backup akan diunduh secara otomatis.')) {
        return false;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
    return true;
}

function konfirmasiRestore() {
    const fileInput = document.getElementById('sql_file');
    if (! fileInput.files.length) {
        alert('Pilih file SQL terlebih dahulu.');
        return false;
    }
    return confirm(
        'PERINGATAN: Restore akan menimpa semua data yang ada saat ini!\n\n' +
        'File: ' + fileInput.files[0].name + '\n\n' +
        'Apakah Anda yakin ingin melanjutkan?'
    );
}
</script>

<?= $this->endSection() ?>
