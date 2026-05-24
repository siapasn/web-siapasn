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
        <a href="<?= base_url('admin/master/soal/salin') ?>" class="ph-action" style="background:rgba(13,110,253,.85)">
            <i class="bi bi-copy"></i> Salin Soal
        </a>
        <a href="<?= base_url('admin/master/soal/import') ?>" class="ph-action" style="background:rgba(25,135,84,.85)">
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

<!-- Filter Kategori -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" action="<?= base_url('admin/master/soal') ?>" id="formFilter" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label for="filter_kategori_id" class="form-label mb-1 small fw-semibold">Kategori</label>
                <select id="filter_kategori_id" name="kategori_id" class="form-select form-select-sm" style="width:100%">
                    <option value="">— Semua Kategori —</option>
                    <?php foreach ($kategoris as $k):
                        $label = esc($k['nama']);
                        if (! empty($k['parent_nama'])) {
                            $label = esc($k['parent_nama']) . ' › ' . $label;
                        }
                    ?>
                        <option value="<?= $k['id'] ?>" <?= (string) $kategori_id === (string) $k['id'] ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if ($kategori_id !== ''): ?>
                    <a href="<?= base_url('admin/master/soal') ?>" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Info jumlah hasil -->
<?php if ($kategori_id !== ''): ?>
    <div class="mb-2 small text-muted px-1">
        Menampilkan <strong><?= count($soals) ?></strong> soal
        <?php
        // Cari nama kategori yang dipilih
        foreach ($kategoris as $k) {
            if ((string) $k['id'] === (string) $kategori_id) {
                $namaFilter = ! empty($k['parent_nama'])
                    ? esc($k['parent_nama']) . ' › ' . esc($k['nama'])
                    : esc($k['nama']);
                echo 'untuk kategori <strong>' . $namaFilter . '</strong>';
                break;
            }
        }
        ?>
    </div>
<?php endif; ?>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <style>
        /* DataTables spacing inside card */
        #tabelSoal_wrapper .dataTables_length label,
        #tabelSoal_wrapper .dataTables_filter label {
            margin-bottom: 0;
            font-size: .875rem;
        }
        #tabelSoal_wrapper .dataTables_filter input {
            margin-left: .4rem;
            border-radius: .375rem;
            border: 1px solid #dee2e6;
            padding: .25rem .5rem;
            font-size: .875rem;
        }
        #tabelSoal_wrapper .dataTables_info,
        #tabelSoal_wrapper .dataTables_paginate {
            font-size: .875rem;
        }
        #tabelSoal_wrapper .paginate_button {
            border-radius: .375rem !important;
        }
    </style>
    <?php if (! empty($soals)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelSoal" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="table-light border-bottom">
                        <th class="ps-4 py-3 text-muted fw-semibold" style="width:55px">#</th>
                        <th class="py-3 fw-semibold">Pertanyaan</th>
                        <th class="py-3 fw-semibold" style="width:220px">Kategori</th>
                        <th class="py-3 fw-semibold text-center" style="width:130px">Tipe / Kunci</th>
                        <th class="py-3 fw-semibold text-center pe-4" style="width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($soals as $i => $s): ?>
                        <tr class="border-bottom">
                            <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                            <td class="py-3">
                                <div class="fw-medium" style="line-height:1.4">
                                    <?php
                                    $plain = strip_tags($s['pertanyaan']);
                                    echo esc(mb_strlen($plain) > 90 ? mb_substr($plain, 0, 90) . '…' : $plain);
                                    ?>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge rounded-pill bg-light text-dark border small fw-normal px-2 py-1">
                                    <?= esc($s['nama_kategori'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <?php if (($s['tipe_soal'] ?? '') === 'POINT'): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-123 me-1"></i>POINT
                                    </span>
                                <?php elseif (! empty($s['kunci_jawaban'])): ?>
                                    <span class="badge bg-info text-dark px-2 py-1">
                                        <i class="bi bi-check2-circle me-1"></i><?= strtoupper(esc($s['kunci_jawaban'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= base_url("admin/master/soal/{$s['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit soal">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post"
                                          action="<?= base_url("admin/master/soal/{$s['id']}/delete") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus soal ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus soal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty state — tidak pakai DataTables agar tidak error -->
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Tidak ada soal ditemukan</div>
        <?php if ($kategori_id !== ''): ?>
            <div class="text-muted small mt-1">Coba pilih kategori lain atau <a href="<?= base_url('admin/master/soal') ?>">tampilkan semua</a></div>
        <?php else: ?>
            <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/soal/create') ?>">menambah soal baru</a></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Inisialisasi DataTables hanya jika ada data
<?php if (! empty($soals)): ?>
$('#tabelSoal').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        loadingRecords: 'Memuat...',
        zeroRecords: 'Tidak ada data yang cocok',
        emptyTable: 'Tidak ada data tersedia',
    },
    pageLength: 25,
    ordering: true,
    order: [],
    columnDefs: [
        { orderable: false, targets: [0, 4] },
        { searchable: false, targets: [0, 4] },
    ],
    dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
});
<?php endif; ?>

// Select2 untuk filter kategori
$('#filter_kategori_id').select2({
    theme: 'bootstrap-5',
    placeholder: '— Semua Kategori —',
    allowClear: true,
    width: '100%',
});
</script>
<?= $this->endSection() ?>
