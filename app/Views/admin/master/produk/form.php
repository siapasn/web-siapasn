<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($produk); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Produk' : 'Tambah Produk' ?></div>
            <div class="ph-subtitle">Master Produk</div>
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
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Nama Produk -->
                <div class="col-12">
                    <label for="nama" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $produk['nama'] ?? '')) ?>"
                           placeholder="Masukkan nama produk" required>
                </div>

                <!-- Deskripsi -->
                <div class="col-12">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="summernote-editor"
                              placeholder="Deskripsi produk (opsional)"><?= old('deskripsi', $produk['deskripsi'] ?? '') ?></textarea>
                </div>

                <!-- Thumbnail -->
                <div class="col-12 col-md-6">
                    <label for="thumbnail" class="form-label">Thumbnail</label>

                    <?php
                    $thumbFile = $produk['thumbnail'] ?? null;
                    $thumbUrl  = $thumbFile
                        ? base_url('uploads/produk/' . $thumbFile)
                        : base_url('assets/images/thumbnail/product-default.png');
                    ?>

                    <!-- Preview thumbnail saat ini -->
                    <div class="mb-2">
                        <img id="thumb_preview" src="<?= $thumbUrl ?>"
                             alt="Thumbnail" class="rounded border"
                             style="width:200px;height:200px;object-fit:cover;object-position:center;">
                    </div>

                    <input type="file" id="thumbnail" name="thumbnail" class="form-control"
                           accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Format: JPG, PNG, WebP. Maks 2 MB. Kosongkan jika tidak ingin mengubah.</div>

                    <?php if ($isEdit && $thumbFile): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="hapus_thumbnail"
                                   name="hapus_thumbnail" value="1">
                            <label class="form-check-label text-danger small" for="hapus_thumbnail">
                                Hapus thumbnail saat ini
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Harga + Status -->
                <div class="col-12 col-md-6 d-flex flex-column gap-3">
                    <div>
                        <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" id="harga" name="harga" class="form-control" min="0" step="0.01"
                                   value="<?= esc(old('harga', $produk['harga'] ?? '')) ?>"
                                   placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="is_active" name="is_active" value="1"
                                   <?= old('is_active', $produk['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <div class="form-text">Produk yang tidak aktif tidak akan ditampilkan di katalog.</div>
                    </div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                </button>
                <a href="<?= base_url('admin/master/produk') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    if (typeof $ === 'undefined' || typeof $.fn.summernote === 'undefined') {
        console.error('jQuery atau Summernote belum tersedia');
        return;
    }

    $('#deskripsi').summernote({
        lang: 'id-ID',
        placeholder: 'Deskripsi produk (opsional)',
        tabsize: 2,
        height: 200,
        toolbar: [
            ['style',   ['bold', 'italic', 'underline', 'clear']],
            ['fontsize',['fontsize']],
            ['color',   ['color']],
            ['para',    ['ul', 'ol', 'paragraph']],
            ['insert',  ['link', 'picture', 'hr']],
            ['view',    ['fullscreen', 'codeview']]
        ]
    });

    // Sync Summernote ke textarea sebelum submit
    $('form').on('submit', function () {
        $('#deskripsi').val($('#deskripsi').summernote('code'));
    });
});
</script>

<script>
// Preview thumbnail sebelum upload
document.getElementById('thumbnail').addEventListener('change', function () {
    const file = this.files[0];
    if (! file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('thumb_preview').src = e.target.result;
    };
    reader.readAsDataURL(file);
});

// Jika hapus thumbnail dicentang, tampilkan gambar default
const hapusCheck = document.getElementById('hapus_thumbnail');
if (hapusCheck) {
    hapusCheck.addEventListener('change', function () {
        if (this.checked) {
            document.getElementById('thumb_preview').src = '<?= base_url('assets/images/thumbnail/product-default.png') ?>';
        } else {
            document.getElementById('thumb_preview').src = '<?= $thumbFile ? base_url('uploads/produk/' . $thumbFile) : base_url('assets/images/thumbnail/product-default.png') ?>';
        }
    });
}
</script>

<?= $this->endSection() ?>
