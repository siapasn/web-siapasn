<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-question-circle"></i></div>
        <div>
            <div class="ph-title">Master Soal</div>
            <div class="ph-subtitle">Kelola soal pilihan ganda beserta kunci jawaban</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/master/soal/import') ?>" class="ph-action" style="background: var(--bs-success, #198754);">
            <i class="bi bi-file-earmark-arrow-up"></i> Import Excel/CSV
        </a>
        <a href="<?= base_url('admin/master/soal/create') ?>" class="ph-action">
            <i class="bi bi-plus-lg"></i> Tambah Soal
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<!-- Filter Kategori & Sub Kategori -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" action="<?= base_url('admin/master/soal') ?>" id="formFilter" class="row g-2 align-items-end">

            <!-- Kategori -->
            <div class="col-12 col-md-4">
                <label for="filter_kategori_id" class="form-label mb-1 small fw-semibold">Kategori</label>
                <select id="filter_kategori_id" name="kategori_id" class="form-select form-select-sm">
                    <option value="">— Semua Kategori —</option>
                    <?php foreach ($kategoris as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= (string) $kategori_id === (string) $k['id'] ? 'selected' : '' ?>>
                            <?= esc($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sub Kategori (muncul jika kategori dipilih) -->
            <div class="col-12 col-md-4" id="wrap_sub_kategori" <?= empty($subKategoris) ? 'style="display:none"' : '' ?>>
                <label for="filter_sub_kategori_id" class="form-label mb-1 small fw-semibold">Sub Kategori</label>
                <select id="filter_sub_kategori_id" name="sub_kategori_id" class="form-select form-select-sm">
                    <option value="">— Semua Sub Kategori —</option>
                    <?php foreach ($subKategoris as $sk): ?>
                        <option value="<?= $sk['id'] ?>" <?= (string) $sub_kategori_id === (string) $sk['id'] ? 'selected' : '' ?>>
                            <?= esc($sk['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tombol -->
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if ($kategori_id !== '' || $sub_kategori_id !== ''): ?>
                    <a href="<?= base_url('admin/master/soal') ?>" class="btn btn-sm btn-outline-secondary ms-1">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelSoal" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Pertanyaan</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th class="text-center" style="width:100px">Kunci / Nilai</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($soals)): ?>
                        <?php foreach ($soals as $i => $s): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <span title="<?= esc(strip_tags($s['pertanyaan'])) ?>">
                                        <?php
                                        $plain = strip_tags($s['pertanyaan']);
                                        echo esc(mb_strlen($plain) > 80 ? mb_substr($plain, 0, 80) . '…' : $plain);
                                        ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= esc($s['nama_kategori'] ?? '—') ?></td>
                                <td class="text-muted"><?= esc($s['nama_sub_kategori'] ?? '—') ?></td>
                                <td class="text-center">
                                    <?php if (($s['tipe_soal'] ?? '') === 'SCORE'): ?>
                                        <span class="badge bg-warning text-dark" title="Nilai per pilihan">
                                            <i class="bi bi-123"></i> Nilai
                                        </span>
                                    <?php elseif (! empty($s['kunci_jawaban'])): ?>
                                        <span class="badge bg-primary rounded-pill text-uppercase">
                                            <?= esc($s['kunci_jawaban']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="<?= base_url("admin/master/soal/{$s['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post"
                                          action="<?= base_url("admin/master/soal/{$s['id']}/delete") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus soal ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada soal
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tabelSoal').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ]
    });
});

// AJAX load sub-kategori saat filter kategori berubah
(function () {
    const filterKategori    = document.getElementById('filter_kategori_id');
    const filterSubKategori = document.getElementById('filter_sub_kategori_id');
    const wrapSub           = document.getElementById('wrap_sub_kategori');
    const baseAjaxUrl       = '<?= rtrim(base_url('admin/master/soal/sub-kategori'), '/') ?>';

    filterKategori.addEventListener('change', function () {
        const kategoriId = this.value;

        // Reset sub kategori
        filterSubKategori.innerHTML = '<option value="">— Semua Sub Kategori —</option>';

        if (! kategoriId) {
            wrapSub.style.display = 'none';
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
                    filterSubKategori.appendChild(opt);
                });
                wrapSub.style.display = '';
            } else {
                wrapSub.style.display = 'none';
            }
        })
        .catch(function () {
            wrapSub.style.display = 'none';
        });
    });
}());
</script>

<?= $this->endSection() ?>
