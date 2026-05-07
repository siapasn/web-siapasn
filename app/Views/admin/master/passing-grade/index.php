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

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelPassingGrade" class="table table-hover align-middle mb-0">
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
                    <?php if (!empty($passingGrades)): ?>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data passing grade
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
    $('#tabelPassingGrade').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 4] }
        ]
    });
});
</script>

<?= $this->endSection() ?>
