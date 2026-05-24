<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($passingGrade); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-bar-chart-steps"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Passing Grade' : 'Tambah Passing Grade' ?></div>
            <div class="ph-subtitle">Master Passing Grade</div>
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

                <!-- Kategori -->
                <div class="col-12 col-md-6">
                    <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form-select" style="width:100%" required>
                        <option value="">— Pilih Kategori —</option>
                        <?php
                        $selectedKategori = old('kategori_id', $passingGrade['kategori_id'] ?? '');
                        foreach ($kategoris as $k):
                            $label = esc($k['nama']);
                            if (! empty($k['parent_nama'])) {
                                $label = esc($k['parent_nama']) . ' › ' . $label;
                            }
                        ?>
                            <option value="<?= $k['id'] ?>"
                                <?= (string) $selectedKategori === (string) $k['id'] ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub Kategori (AJAX, opsional) -->
                <div class="col-12 col-md-6">
                    <label for="sub_kategori_id" class="form-label">
                        Sub Kategori
                        <span class="text-muted small">(opsional)</span>
                    </label>
                    <select id="sub_kategori_id" name="sub_kategori_id" class="form-select"
                            <?= empty($subKategoris) ? 'disabled' : '' ?>>
                        <option value="">— Semua Sub Kategori —</option>
                        <?php
                        $selectedSub = old('sub_kategori_id', $passingGrade['sub_kategori_id'] ?? '');
                        foreach ($subKategoris as $sk):
                        ?>
                            <option value="<?= $sk['id'] ?>"
                                <?= (string) $selectedSub === (string) $sk['id'] ? 'selected' : '' ?>>
                                <?= esc($sk['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-muted" id="sub_kategori_hint">
                        <?= ! empty($subKategoris) ? 'Kosongkan untuk berlaku di semua sub kategori.' : 'Pilih kategori dulu untuk memuat sub kategori.' ?>
                    </div>
                </div>

                <!-- Nilai Minimum -->
                <div class="col-12 col-md-6">
                    <label for="nilai_minimum" class="form-label">Nilai Minimum <span class="text-muted small">(opsional)</span></label>
                    <input type="number" id="nilai_minimum" name="nilai_minimum" class="form-control"
                           min="0" max="100" step="0.01"
                           value="<?= esc(old('nilai_minimum', $passingGrade['nilai_minimum'] ?? '')) ?>"
                           placeholder="Contoh: 65.00">
                    <div class="form-text">Nilai antara 0 sampai 100. Kosongkan jika belum ditentukan.</div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" id="btnSubmit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Passing Grade' ?>
                </button>
                <a href="<?= base_url('admin/master/passing-grade') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<!-- Modal Konfirmasi Duplikat -->
<div class="modal fade" id="modalKonfirmasiDuplikat" tabindex="-1" aria-labelledby="modalKonfirmasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-semibold" id="modalKonfirmasiLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Data Sudah Ada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Passing grade untuk kombinasi berikut sudah ada:</p>
                <table class="table table-sm table-bordered mb-3">
                    <tr>
                        <th class="text-muted" style="width:40%">Kategori</th>
                        <td id="konfirm_kategori" class="fw-semibold">—</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Sub Kategori</th>
                        <td id="konfirm_sub" class="fw-semibold">—</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nilai Saat Ini</th>
                        <td id="konfirm_nilai" class="fw-semibold text-danger">—</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nilai Baru</th>
                        <td id="konfirm_nilai_baru" class="fw-semibold text-success">—</td>
                    </tr>
                </table>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Jika dilanjutkan, nilai minimum akan diperbarui ke nilai baru.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batalkan
                </button>
                <button type="button" id="btnKonfirmasiLanjut" class="btn btn-warning text-dark fw-semibold">
                    <i class="bi bi-check-lg me-1"></i>Ya, Perbarui Nilai
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const kategoriSelect    = document.getElementById('kategori_id');
    const subKategoriSelect = document.getElementById('sub_kategori_id');
    const subHint           = document.getElementById('sub_kategori_hint');
    const baseAjaxUrl       = '<?= rtrim(base_url('admin/master/soal/sub-kategori'), '/') ?>';
    const checkDupUrl       = '<?= base_url('admin/master/passing-grade/check-duplicate') ?>';
    const preselectedSub    = '<?= old('sub_kategori_id', $passingGrade['sub_kategori_id'] ?? '') ?>';
    const isEdit            = <?= ! empty($passingGrade) ? 'true' : 'false' ?>;
    const excludeId         = '<?= $passingGrade['id'] ?? '' ?>';

    // ── Inisialisasi Select2 untuk field Kategori ─────────────────────────────
    $('#kategori_id').select2({
        theme: 'bootstrap-5',
        placeholder: '— Pilih Kategori —',
        allowClear: true,
        width: '100%',
    });

    // ── AJAX load sub-kategori ────────────────────────────────────────────────
    $('#kategori_id').on('change', function () {
        const kategoriId = this.value;

        subKategoriSelect.innerHTML = '<option value="">— Semua Sub Kategori —</option>';
        subKategoriSelect.disabled  = true;
        if (subHint) subHint.textContent = 'Memuat sub kategori...';

        if (! kategoriId) {
            if (subHint) subHint.textContent = 'Pilih kategori dulu untuk memuat sub kategori.';
            return;
        }

        fetch(baseAjaxUrl + '/' + kategoriId, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status && data.data && data.data.length > 0) {
                data.data.forEach(function (sub) {
                    const opt = document.createElement('option');
                    opt.value       = sub.id;
                    opt.textContent = sub.nama;
                    if (String(sub.id) === String(preselectedSub)) opt.selected = true;
                    subKategoriSelect.appendChild(opt);
                });
                subKategoriSelect.disabled = false;
                if (subHint) subHint.textContent = 'Kosongkan untuk berlaku di semua sub kategori.';
            } else {
                if (subHint) subHint.textContent = 'Kategori ini tidak memiliki sub kategori.';
            }
        })
        .catch(function () {
            if (subHint) subHint.textContent = 'Gagal memuat sub kategori.';
        });
    });

    // Trigger saat page load jika kategori sudah dipilih (mode edit / old input)
    if ($('#kategori_id').val()) {
        $('#kategori_id').trigger('change');
    }

    // ── Intercept form submit — cek duplikat dulu (hanya mode tambah) ─────────
    const form       = document.querySelector('form');
    const btnSubmit  = document.getElementById('btnSubmit');
    let   confirmed  = false; // flag: user sudah konfirmasi dari modal

    if (! isEdit) {
        form.addEventListener('submit', function (e) {
            if (confirmed) return; // sudah dikonfirmasi, lanjut submit normal

            e.preventDefault();

            const kategoriId    = $('#kategori_id').val();
            const subKategoriId = subKategoriSelect.value;
            const nilaiMinimum  = document.getElementById('nilai_minimum').value;

            if (! kategoriId) return; // validasi HTML5 akan menangani ini

            // Bangun URL cek duplikat
            const params = new URLSearchParams({ kategori_id: kategoriId });
            if (subKategoriId) params.append('sub_kategori_id', subKategoriId);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memeriksa...';

            fetch(checkDupUrl + '?' + params.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-save me-1"></i>Tambah Passing Grade';

                if (data.exists) {
                    // Isi detail modal
                    document.getElementById('konfirm_kategori').textContent  = data.nama_kategori;
                    document.getElementById('konfirm_sub').textContent       = data.nama_sub;
                    document.getElementById('konfirm_nilai').textContent     = data.nilai_minimum !== null ? parseFloat(data.nilai_minimum).toFixed(2) : '—';
                    document.getElementById('konfirm_nilai_baru').textContent = nilaiMinimum !== '' ? parseFloat(nilaiMinimum).toFixed(2) : '—';

                    // Tampilkan modal
                    const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiDuplikat'));
                    modal.show();
                } else {
                    // Tidak ada duplikat, langsung submit
                    confirmed = true;
                    form.submit();
                }
            })
            .catch(function () {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-save me-1"></i>Tambah Passing Grade';
                // Jika AJAX gagal, tetap izinkan submit
                confirmed = true;
                form.submit();
            });
        });
    }

    // ── Tombol "Ya, Perbarui Nilai" di modal ──────────────────────────────────
    document.getElementById('btnKonfirmasiLanjut').addEventListener('click', function () {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiDuplikat'));
        if (modal) modal.hide();
        confirmed = true;
        form.submit();
    });
}());
</script>
<?= $this->endSection() ?>
