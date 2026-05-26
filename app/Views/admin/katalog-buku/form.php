<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($buku); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-book"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Buku' : 'Tambah Buku' ?></div>
            <div class="ph-subtitle">Katalog Buku</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" id="kode" name="kode" class="form-control"
                           value="<?= esc(old('kode', $buku['kode'] ?? '')) ?>"
                           placeholder="PB0014011" required>
                </div>

                <div class="col-12 col-md-8">
                    <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" id="judul" name="judul" class="form-control"
                           value="<?= esc(old('judul', $buku['judul'] ?? '')) ?>"
                           placeholder="Masukkan judul buku" required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" id="isbn" name="isbn" class="form-control"
                           value="<?= esc(old('isbn', $buku['isbn'] ?? '')) ?>"
                           placeholder="978-xxx-xxx-xxx-x">
                </div>

                <div class="col-12 col-md-4">
                    <label for="pengarang" class="form-label">Pengarang</label>
                    <input type="text" id="pengarang" name="pengarang" class="form-control"
                           value="<?= esc(old('pengarang', $buku['pengarang'] ?? '')) ?>"
                           placeholder="Nama pengarang">
                </div>

                <div class="col-12 col-md-4">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" class="form-control"
                           value="<?= esc(old('penerbit', $buku['penerbit'] ?? '')) ?>"
                           placeholder="Nama penerbit">
                </div>

                <div class="col-12 col-md-4">
                    <label for="harga" class="form-label">Harga (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="harga" name="harga" class="form-control" min="0" step="1"
                               value="<?= esc(old('harga', $buku['harga'] ?? '')) ?>"
                               placeholder="0">
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <label for="url_thumbnail" class="form-label">URL Thumbnail</label>
                    <input type="text" id="url_thumbnail" name="url_thumbnail" class="form-control"
                           value="<?= esc(old('url_thumbnail', $buku['url_thumbnail'] ?? '')) ?>"
                           placeholder="https://... atau /file/123">
                    <div class="form-text">URL gambar cover buku. Bisa dari Data File (/file/ID) atau URL eksternal.</div>
                </div>

                <div class="col-12">
                    <label for="url_shopee" class="form-label">URL Link Produk Shopee</label>
                    <input type="text" id="url_shopee" name="url_shopee" class="form-control"
                           value="<?= esc(old('url_shopee', $buku['url_shopee'] ?? '')) ?>"
                           placeholder="https://shopee.co.id/...">
                </div>

                <div class="col-12 col-md-4">
                    <label for="urutan" class="form-label">Urutan</label>
                    <input type="number" id="urutan" name="urutan" class="form-control" min="0"
                           value="<?= esc(old('urutan', $buku['urutan'] ?? 0)) ?>">
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= old('is_active', $buku['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_highlight" name="is_highlight" value="1"
                               <?= old('is_highlight', $buku['is_highlight'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_highlight">
                            <i class="bi bi-star-fill text-warning me-1"></i>Highlight (tampil di Dashboard User)
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Buku' ?>
                </button>
                <a href="<?= base_url('admin/katalog-buku') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
