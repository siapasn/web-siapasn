<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($tryout); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Tryout' : 'Tambah Tryout' ?></div>
            <div class="ph-subtitle">Master Tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Validation Errors -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Nama Tryout -->
                <div class="col-12">
                    <label for="nama" class="form-label">Nama Tryout <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $tryout['nama'] ?? '')) ?>"
                           placeholder="Masukkan nama tryout" required>
                </div>

                <!-- Durasi -->
                <div class="col-12 col-md-6">
                    <label for="durasi" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                    <input type="number" id="durasi" name="durasi" class="form-control" min="1"
                           value="<?= esc(old('durasi', $tryout['durasi'] ?? '')) ?>"
                           placeholder="Contoh: 90" required>
                    <div class="form-text">Durasi pengerjaan tryout dalam menit.</div>
                </div>

                <!-- Jumlah Soal — dihitung otomatis dari Mapping Soal -->
                <div class="col-12 col-md-6">
                    <label class="form-label">Jumlah Soal</label>
                    <div class="form-control bg-light text-muted" style="cursor:default">
                        <?php if ($isEdit): ?>
                            <?php
                            $db = \Config\Database::connect();
                            $jmlSoal = $db->table('mapping_soal')->where('tryout_id', $tryout['id'])->countAllResults();
                            ?>
                            <?= $jmlSoal ?> soal (dari Mapping Soal)
                        <?php else: ?>
                            Dihitung otomatis dari Mapping Soal
                        <?php endif; ?>
                    </div>
                    <div class="form-text">Jumlah soal dihitung dari soal yang telah di-mapping di menu <strong>Mapping Soal</strong>.</div>
                </div>

                <!-- Status Aktif -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= old('is_active', $tryout['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-text">Tryout yang tidak aktif tidak akan ditampilkan kepada peserta.</div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Tryout' ?>
                </button>
                <a href="<?= base_url('admin/master/tryout') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>
