<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($promosi); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-megaphone"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Promosi' : 'Tambah Promosi' ?></div>
            <div class="ph-subtitle">Promosi</div>
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

                <!-- Nama Promosi -->
                <div class="col-12">
                    <label for="nama" class="form-label">Nama Promosi <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $promosi['nama'] ?? '')) ?>"
                           placeholder="Masukkan nama promosi" required>
                </div>

                <!-- Produk -->
                <div class="col-12 col-md-6">
                    <label for="produk_id" class="form-label">Produk <span class="text-danger">*</span></label>
                    <select id="produk_id" name="produk_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produks as $prod): ?>
                            <option value="<?= $prod['id'] ?>"
                                <?= old('produk_id', $promosi['produk_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                                <?= esc($prod['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jenis Diskon -->
                <div class="col-12 col-md-6">
                    <label for="jenis_diskon" class="form-label">Jenis Diskon <span class="text-danger">*</span></label>
                    <select id="jenis_diskon" name="jenis_diskon" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="persentase"
                            <?= old('jenis_diskon', $promosi['jenis_diskon'] ?? '') === 'persentase' ? 'selected' : '' ?>>
                            Persentase (%)
                        </option>
                        <option value="nominal"
                            <?= old('jenis_diskon', $promosi['jenis_diskon'] ?? '') === 'nominal' ? 'selected' : '' ?>>
                            Nominal (Rp)
                        </option>
                    </select>
                </div>

                <!-- Nilai Diskon -->
                <div class="col-12 col-md-6">
                    <label for="nilai_diskon" class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
                    <input type="number" id="nilai_diskon" name="nilai_diskon" class="form-control"
                           min="0.01" step="0.01"
                           value="<?= esc(old('nilai_diskon', $promosi['nilai_diskon'] ?? '')) ?>"
                           placeholder="Contoh: 10 (untuk 10% atau Rp 10.000)" required>
                    <div class="form-text">Untuk persentase masukkan angka 1–100. Untuk nominal masukkan nilai rupiah.</div>
                </div>

                <!-- Mulai At -->
                <div class="col-12 col-md-6">
                    <label for="mulai_at" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <?php
                        $mulaiVal = old('mulai_at', isset($promosi['mulai_at'])
                            ? date('Y-m-d\TH:i', strtotime($promosi['mulai_at']))
                            : '');
                    ?>
                    <input type="datetime-local" id="mulai_at" name="mulai_at" class="form-control"
                           value="<?= esc($mulaiVal) ?>" required>
                </div>

                <!-- Berakhir At -->
                <div class="col-12 col-md-6">
                    <label for="berakhir_at" class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <?php
                        $berakhirVal = old('berakhir_at', isset($promosi['berakhir_at'])
                            ? date('Y-m-d\TH:i', strtotime($promosi['berakhir_at']))
                            : '');
                    ?>
                    <input type="datetime-local" id="berakhir_at" name="berakhir_at" class="form-control"
                           value="<?= esc($berakhirVal) ?>" required>
                </div>

                <!-- Deskripsi -->
                <div class="col-12">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi promosi (opsional)"><?= esc(old('deskripsi', $promosi['deskripsi'] ?? '')) ?></textarea>
                </div>

                <!-- Status Aktif -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= old('is_active', $promosi['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-text">Promosi yang tidak aktif tidak akan diterapkan pada transaksi baru.</div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Promosi' ?>
                </button>
                <a href="<?= base_url('admin/promosi') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>
