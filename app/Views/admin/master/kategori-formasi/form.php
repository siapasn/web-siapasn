<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($kategori); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-briefcase"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Kategori Formasi' : 'Tambah Kategori Formasi' ?></div>
            <div class="ph-subtitle">Kategori Formasi CPNS</div>
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

                <!-- Nama Kategori Formasi -->
                <div class="col-12 col-md-6">
                    <label for="nama" class="form-label">Nama Kategori Formasi <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $kategori['nama'] ?? '')) ?>"
                           placeholder="Contoh: Teknologi Informasi" required>
                </div>

                <!-- Icon -->
                <div class="col-12 col-md-3">
                    <label for="icon" class="form-label">Icon <span class="text-muted small">(Bootstrap Icons)</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="icon-preview">
                            <i class="<?= esc(old('icon', $kategori['icon'] ?? 'bi-folder')) ?>"></i>
                        </span>
                        <input type="text" id="icon" name="icon" class="form-control"
                               value="<?= esc(old('icon', $kategori['icon'] ?? '')) ?>"
                               placeholder="bi-laptop">
                    </div>
                    <div class="form-text">Contoh: bi-laptop, bi-building, bi-heart-pulse</div>
                </div>

                <!-- Urutan -->
                <div class="col-12 col-md-3">
                    <label for="urutan" class="form-label">Urutan</label>
                    <input type="number" id="urutan" name="urutan" class="form-control"
                           value="<?= esc(old('urutan', $kategori['urutan'] ?? '0')) ?>"
                           min="0" placeholder="0">
                    <div class="form-text">Semakin kecil, semakin atas.</div>
                </div>

                <!-- Deskripsi -->
                <div class="col-12">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi singkat tentang kategori formasi ini"><?= esc(old('deskripsi', $kategori['deskripsi'] ?? '')) ?></textarea>
                </div>

                <!-- Status -->
                <div class="col-12 col-md-4">
                    <label for="is_active" class="form-label">Status</label>
                    <select id="is_active" name="is_active" class="form-select">
                        <?php $selectedStatus = old('is_active', $kategori['is_active'] ?? '1'); ?>
                        <option value="1" <?= (string)$selectedStatus === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (string)$selectedStatus === '0' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Kategori Formasi' ?>
                </button>
                <a href="<?= base_url('admin/master/kategori-formasi') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');

    iconInput.addEventListener('input', function () {
        const val = this.value.trim();
        iconPreview.innerHTML = '<i class="' + (val || 'bi-folder') + '"></i>';
    });
}());
</script>
<?= $this->endSection() ?>
