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
    <a href="<?= base_url('admin/master/soal') ?>" class="ph-action">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Download Template -->
<div class="alert alert-info d-flex align-items-center gap-3 mb-4" role="alert">
    <i class="bi bi-file-earmark-excel fs-4 text-success flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Belum punya file template?</strong>
        Download template CSV yang sudah berisi contoh soal sesuai format yang dibutuhkan.
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
<?php
$totalImported = session()->getFlashdata('total_imported');
$importErrors  = session()->getFlashdata('import_errors') ?? [];
?>

<?php if ($totalImported !== null): ?>
    <?php if ($totalImported > 0 && empty($importErrors)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>
            <strong>Import berhasil!</strong> <?= (int) $totalImported ?> soal berhasil diimpor.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php elseif ($totalImported === 0 && empty($importErrors)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Tidak ada soal yang diimpor.</strong>
            Kemungkinan penyebab: semua baris dilewati karena <code>kategori_id</code> tidak valid,
            atau format kolom tidak sesuai template. Pastikan <code>kategori_id</code> ada di database.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if (! empty($importErrors)): ?>
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Import Gagal</strong> — Ditemukan <?= count($importErrors) ?> kesalahan. Tidak ada data yang disimpan.
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($importErrors as $err): ?>
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
                            File CSV (.csv) <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="file_import" name="file_import"
                               class="form-control" accept=".csv,text/csv,application/vnd.ms-excel" required>
                        <div class="form-text">Ukuran maksimum: 5 MB. Format: .csv (download template terbaru)</div>
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
                <p class="text-muted small mb-3">Baris pertama adalah header. Data soal dimulai dari baris ke-2. File template berformat <strong>CSV</strong>.</p>

                <p class="fw-semibold small mb-1 text-primary"><i class="bi bi-table me-1"></i>Format Kolom</p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-primary">
                            <tr><th>Kolom</th><th>Field</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">A</td><td>No</td><td>Nomor urut (diabaikan saat import)</td></tr>
                            <tr><td class="fw-bold">B</td><td>kategori_id *</td><td>ID kategori soal</td></tr>
                            <tr><td class="fw-bold">C</td><td>pertanyaan *</td><td>Teks pertanyaan</td></tr>
                            <tr><td class="fw-bold">D–G</td><td>pilihan_a–d *</td><td>Pilihan A, B, C, D (wajib)</td></tr>
                            <tr><td class="fw-bold">H</td><td>pilihan_e</td><td>Pilihan E (opsional)</td></tr>
                            <tr><td class="fw-bold">I</td><td>Kunci</td><td><span class="badge bg-info text-dark">SCORE</span> Huruf: a/b/c/d/e</td></tr>
                            <tr><td class="fw-bold">J–N</td><td>nilai_a–e</td><td><span class="badge bg-warning text-dark">POINT</span> Angka 1–5, semua berbeda</td></tr>
                            <tr><td class="fw-bold">O</td><td>pembahasan</td><td>Teks pembahasan (opsional)</td></tr>
                            <tr><td class="fw-bold">P</td><td>tryout_id</td><td>ID tryout (opsional — soal langsung di-mapping)</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info py-2 small mb-2">
                    <i class="bi bi-lightbulb me-1"></i>
                    Tipe soal (POINT/SCORE) dideteksi otomatis dari <strong>kategori_id</strong>.
                    Kolom H diisi kunci jawaban untuk POINT, atau nilai_a untuk SCORE.
                </div>

                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Jika ada baris yang tidak valid, <strong>seluruh import akan dibatalkan</strong>.
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
