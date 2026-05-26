<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
        <div>
            <div class="ph-title">Import Katalog Buku</div>
            <div class="ph-subtitle">Import data buku dari file CSV</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/katalog-buku') ?>" class="ph-action">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$totalImported = session()->getFlashdata('total_imported');
$importErrors  = session()->getFlashdata('import_errors') ?? [];
?>

<?php if ($totalImported !== null && $totalImported > 0): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i>
        <strong><?= (int) $totalImported ?> buku berhasil diimport.</strong>
        <?php if (! empty($importErrors)): ?>
            Namun ada <?= count($importErrors) ?> baris yang dilewati.
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (! empty($importErrors)): ?>
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Ditemukan <?= count($importErrors) ?> kesalahan
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                <?php foreach ($importErrors as $err): ?>
                    <li class="list-group-item list-group-item-danger py-2 small">
                        <i class="bi bi-x-circle me-1"></i> <?= esc($err) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-upload me-1"></i> Unggah File CSV</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/katalog-buku/import') ?>"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="file_import" class="form-label">File CSV <span class="text-danger">*</span></label>
                        <input type="file" id="file_import" name="file_import"
                               class="form-control" accept=".csv" required>
                        <div class="form-text">Maks 5 MB. Format: .csv</div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Proses Import
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-1"></i> Format CSV</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">File CSV harus memiliki minimal 3 kolom:</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-primary">
                            <tr><th>Kolom</th><th>Field</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">A</td><td>No</td><td>Nomor urut (diabaikan)</td></tr>
                            <tr><td class="fw-bold">B</td><td>Kode *</td><td>Kode buku (wajib)</td></tr>
                            <tr><td class="fw-bold">C</td><td>Judul Buku *</td><td>Judul buku (wajib)</td></tr>
                            <tr><td class="fw-bold">D</td><td>ISBN</td><td>Nomor ISBN</td></tr>
                            <tr><td class="fw-bold">E</td><td>Pengarang</td><td>Nama pengarang</td></tr>
                            <tr><td class="fw-bold">F</td><td>Penerbit</td><td>Nama penerbit</td></tr>
                            <tr><td class="fw-bold">G</td><td>Harga</td><td>Harga (angka)</td></tr>
                            <tr><td class="fw-bold">H</td><td>URL Thumbnail</td><td>URL gambar cover</td></tr>
                            <tr><td class="fw-bold">I</td><td>URL Shopee</td><td>Link produk Shopee</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-lightbulb me-1"></i>
                    Baris pertama (header) akan di-skip otomatis. Yang wajib diisi hanya <strong>Kode</strong> dan <strong>Judul</strong>.
                    Semua buku yang diimport default <strong>tidak aktif</strong> dan <strong>tidak highlight</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
