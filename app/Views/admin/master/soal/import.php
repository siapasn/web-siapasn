<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
        <div>
            <div class="ph-title">Import Soal</div>
            <div class="ph-subtitle">Import soal dari file Excel atau CSV</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Download Template -->
<div class="alert alert-info d-flex align-items-center gap-3 mb-4" role="alert">
    <i class="bi bi-file-earmark-excel fs-4 text-success flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Belum punya file template?</strong>
        Download template Excel terbaru yang sudah berisi contoh soal CPNS (TWK, TIU, TKP) dan referensi kategori.
    </div>
    <a href="<?= base_url('admin/master/soal/template') ?>"
       class="btn btn-success btn-sm text-nowrap">
        <i class="bi bi-download me-1"></i> Download Template
    </a>
</div>

<!-- Flash error -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<!-- Hasil Import -->
<?php if (isset($total_imported)): ?>
    <?php if ($total_imported > 0 && empty($errors)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>
            <strong>Import berhasil!</strong> <?= $total_imported ?> soal berhasil diimpor.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if (! empty($errors)): ?>
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Import Gagal</strong> — Ditemukan <?= count($errors) ?> kesalahan. Tidak ada data yang disimpan.
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($errors as $err): ?>
                        <li class="list-group-item list-group-item-danger py-2">
                            <i class="bi bi-x-circle me-1"></i> <?= esc($err) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">

    <!-- Form Upload -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-upload me-1"></i> Unggah File</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/master/soal/import') ?>"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="file_import" class="form-label">
                            File Excel (.xlsx) atau CSV (.csv) <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="file_import" name="file_import"
                               class="form-control" accept=".xlsx,.csv" required>
                        <div class="form-text">Ukuran maksimum: 5 MB. Format: .xlsx atau .csv</div>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Proses Import
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Panduan Format -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-1"></i> Format File</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Baris pertama adalah header. Data dimulai dari baris ke-2. File template memiliki <strong>3 sheet</strong>.</p>

                <p class="fw-semibold small mb-1 text-primary"><i class="bi bi-table me-1"></i>Sheet 1 — POINT (TWK / TIU)</p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-primary">
                            <tr><th>Kolom</th><th>Field</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">A</td><td>nama_kategori *</td><td>Nama kategori induk, contoh: <code>CPNS</code></td></tr>
                            <tr><td class="fw-bold">B</td><td>nama_sub_kategori</td><td>Nama sub kategori, contoh: <code>TWK</code> (opsional)</td></tr>
                            <tr><td class="fw-bold">C</td><td>pertanyaan *</td><td>Teks pertanyaan</td></tr>
                            <tr><td class="fw-bold">D–G</td><td>pilihan_a–d *</td><td>Pilihan A, B, C, D (wajib)</td></tr>
                            <tr><td class="fw-bold">H</td><td>pilihan_e</td><td>Pilihan E (opsional)</td></tr>
                            <tr><td class="fw-bold">I</td><td>kunci_jawaban *</td><td>Huruf kunci: a / b / c / d / e</td></tr>
                            <tr><td class="fw-bold">J</td><td>pembahasan</td><td>Teks pembahasan (opsional)</td></tr>
                        </tbody>
                    </table>
                </div>

                <p class="fw-semibold small mb-1 text-warning"><i class="bi bi-table me-1"></i>Sheet 2 — SCORE (TKP)</p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-warning">
                            <tr><th>Kolom</th><th>Field</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">A</td><td>nama_kategori *</td><td>Nama kategori induk, contoh: <code>CPNS</code></td></tr>
                            <tr><td class="fw-bold">B</td><td>nama_sub_kategori</td><td>Nama sub kategori, contoh: <code>TKP</code> (opsional)</td></tr>
                            <tr><td class="fw-bold">C</td><td>pertanyaan *</td><td>Teks pertanyaan</td></tr>
                            <tr><td class="fw-bold">D–G</td><td>pilihan_a–d *</td><td>Pilihan A, B, C, D (wajib)</td></tr>
                            <tr><td class="fw-bold">H</td><td>pilihan_e</td><td>Pilihan E (opsional)</td></tr>
                            <tr><td class="fw-bold">I–M</td><td>nilai_a–e *</td><td>Nilai 1–5 untuk tiap pilihan. <strong>Tidak boleh ada yang sama</strong></td></tr>
                            <tr><td class="fw-bold">N</td><td>pembahasan</td><td>Teks pembahasan (opsional)</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Jika ada baris yang tidak valid, <strong>seluruh import akan dibatalkan</strong>.
                    Perbaiki semua kesalahan sebelum mengimpor ulang.
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
