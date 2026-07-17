<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-bar-chart-steps"></i></div>
        <div>
            <div class="ph-title">Master Passing Grade</div>
            <div class="ph-subtitle">Kelola nilai ambang batas minimum per kategori</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/passing-grade/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Passing Grade
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Table -->
<?php if (!empty($passingGrades)): ?>
<div class="card border-0 shadow-sm">
    <style>
        #tabelPassingGrade_wrapper .dataTables_length label,
        #tabelPassingGrade_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelPassingGrade_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelPassingGrade_wrapper .dataTables_info, #tabelPassingGrade_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelPassingGrade_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelPassingGrade" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th class="text-center">Nilai Minimum</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($passingGrades as $i => $pg): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <?= !empty($pg['nama_kategori'])
                                    ? esc($pg['nama_kategori'])
                                    : '<span class="text-muted fst-italic">—</span>' ?>
                            </td>
                            <td>
                                <?= !empty($pg['nama_sub_kategori'])
                                    ? esc($pg['nama_sub_kategori'])
                                    : '<span class="text-muted fst-italic">Semua</span>' ?>
                            </td>
                            <td class="text-center fw-medium">
                                <?= number_format((float) $pg['nilai_minimum'], 2) ?>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/master/passing-grade/{$pg['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/master/passing-grade/{$pg['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus passing grade ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-bar-chart-steps text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada passing grade</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/passing-grade/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($passingGrades)): ?>
<script>
$(document).ready(function () {
    $('#tabelPassingGrade').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 4] }
        ]
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
