<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($voucher); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-ticket-perforated"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Voucher' : 'Tambah Voucher' ?></div>
            <div class="ph-subtitle">Voucher</div>
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

                <!-- Kode Voucher -->
                <div class="col-12 col-md-6">
                    <label for="kode" class="form-label">Kode Voucher <span class="text-danger">*</span></label>
                    <input type="text" id="kode" name="kode" class="form-control font-monospace text-uppercase"
                           value="<?= esc(old('kode', $voucher['kode'] ?? '')) ?>"
                           placeholder="Contoh: DISKON50" required
                           style="text-transform:uppercase">
                    <div class="form-text">Kode harus unik. Huruf kapital direkomendasikan.</div>
                </div>

                <!-- Jenis Diskon -->
                <div class="col-12 col-md-6">
                    <label for="jenis_diskon" class="form-label">Jenis Diskon <span class="text-danger">*</span></label>
                    <select id="jenis_diskon" name="jenis_diskon" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="persentase"
                            <?= old('jenis_diskon', $voucher['jenis_diskon'] ?? '') === 'persentase' ? 'selected' : '' ?>>
                            Persentase (%)
                        </option>
                        <option value="nominal"
                            <?= old('jenis_diskon', $voucher['jenis_diskon'] ?? '') === 'nominal' ? 'selected' : '' ?>>
                            Nominal (Rp)
                        </option>
                    </select>
                </div>

                <!-- Nilai Diskon -->
                <div class="col-12 col-md-6">
                    <label for="nilai_diskon" class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
                    <input type="number" id="nilai_diskon" name="nilai_diskon" class="form-control"
                           min="0.01" step="0.01"
                           value="<?= esc(old('nilai_diskon', $voucher['nilai_diskon'] ?? '')) ?>"
                           placeholder="Contoh: 10 (untuk 10% atau Rp 10.000)" required>
                </div>

                <!-- Batas Penggunaan -->
                <div class="col-12 col-md-6">
                    <label for="batas_penggunaan" class="form-label">Batas Penggunaan</label>
                    <input type="number" id="batas_penggunaan" name="batas_penggunaan" class="form-control"
                           min="1" step="1"
                           value="<?= esc(old('batas_penggunaan', $voucher['batas_penggunaan'] ?? '')) ?>"
                           placeholder="Kosongkan untuk tidak terbatas">
                    <div class="form-text">Kosongkan jika tidak ada batas penggunaan.</div>
                </div>

                <!-- Expired At -->
                <div class="col-12 col-md-6">
                    <label for="expired_at" class="form-label">Tanggal Kedaluwarsa</label>
                    <?php
                        $expiredVal = old('expired_at', isset($voucher['expired_at']) && $voucher['expired_at']
                            ? date('Y-m-d\TH:i', strtotime($voucher['expired_at']))
                            : '');
                    ?>
                    <input type="datetime-local" id="expired_at" name="expired_at" class="form-control"
                           value="<?= esc($expiredVal) ?>">
                    <div class="form-text">Kosongkan jika voucher tidak memiliki tanggal kedaluwarsa.</div>
                </div>

                <!-- Status Aktif -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= old('is_active', $voucher['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-text">Voucher yang tidak aktif tidak dapat digunakan.</div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Voucher' ?>
                </button>
                <a href="<?= base_url('admin/voucher') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>
