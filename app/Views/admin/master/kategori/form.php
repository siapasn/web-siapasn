<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($kategori); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-tags"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Kategori' : 'Tambah Kategori' ?></div>
            <div class="ph-subtitle">Master Kategori</div>
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

                <!-- Nama Kategori -->
                <div class="col-12 col-md-6">
                    <label for="nama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $kategori['nama'] ?? '')) ?>"
                           placeholder="Masukkan nama kategori" required>
                </div>

                <!-- Parent Kategori -->
                <div class="col-12 col-md-6">
                    <label for="parent_id" class="form-label">Parent Kategori</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="">— Tidak ada (kategori induk) —</option>
                        <?php
                        $selectedParent = old('parent_id', $kategori['parent_id'] ?? '');
                        foreach ($parents as $p):
                            if ($isEdit && (int) $p['id'] === (int) $kategori['id']) continue;
                        ?>
                            <option value="<?= $p['id'] ?>"
                                <?= (string) $selectedParent === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Pilih parent jika ini adalah sub-kategori. <span class="text-warning fw-semibold">Sub-kategori hanya diperbolehkan 1 tingkat.</span></div>
                </div>

                <!-- Tipe Soal (hanya muncul jika parent dipilih) -->
                <div class="col-12 col-md-6" id="tipe_soal_wrapper" style="<?= empty(old('parent_id', $kategori['parent_id'] ?? '')) ? 'display:none' : '' ?>">
                    <label for="tipe_soal" class="form-label">
                        Tipe Soal <span class="text-danger">*</span>
                    </label>
                    <select id="tipe_soal" name="tipe_soal" class="form-select">
                        <option value="">— Pilih Tipe —</option>
                        <?php $selectedTipe = old('tipe_soal', $kategori['tipe_soal'] ?? ''); ?>
                        <option value="SCORE" <?= $selectedTipe === 'SCORE' ? 'selected' : '' ?>>
                            SCORE — Pilihan ganda dengan kunci jawaban (A/B/C/D/E)
                        </option>
                        <option value="POINT" <?= $selectedTipe === 'POINT' ? 'selected' : '' ?>>
                            POINT — Setiap pilihan memiliki nilai 1–5
                        </option>
                    </select>
                    <div class="form-text">
                        <strong>SCORE</strong>: soal memiliki 1 jawaban benar.<br>
                        <strong>POINT</strong>: setiap pilihan (A–E) diberi nilai 1–5, tidak ada jawaban benar/salah.
                    </div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                </button>
                <a href="<?= base_url('admin/master/kategori') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
(function () {
    const parentSelect      = document.getElementById('parent_id');
    const tipeSoalWrapper   = document.getElementById('tipe_soal_wrapper');
    const tipeSoalSelect    = document.getElementById('tipe_soal');

    function toggleTipeSoal() {
        if (parentSelect.value) {
            tipeSoalWrapper.style.display = '';
            tipeSoalSelect.required = true;
        } else {
            tipeSoalWrapper.style.display = 'none';
            tipeSoalSelect.required = false;
            tipeSoalSelect.value = '';
        }
    }

    parentSelect.addEventListener('change', toggleTipeSoal);
    toggleTipeSoal(); // on page load
}());
</script>

<?= $this->endSection() ?>
