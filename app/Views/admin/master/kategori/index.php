<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-tags"></i></div>
        <div>
            <div class="ph-title">Master Kategori</div>
            <div class="ph-subtitle">Kelola kategori dan sub kategori soal</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/kategori/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Kategori
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Table -->
<?php if (!empty($kategoris)): ?>
<div class="card border-0 shadow-sm">
    <style>
        #tabelKategori_wrapper .dataTables_length label,
        #tabelKategori_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelKategori_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelKategori_wrapper .dataTables_info, #tabelKategori_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelKategori_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelKategori" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Kategori</th>
                        <th>Parent</th>
                        <th class="text-center">Jumlah Sub-Kategori</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategoris as $i => $k): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($k['nama']) ?></td>
                            <td class="text-muted">
                                <?= !empty($k['parent_nama']) ? esc($k['parent_nama']) : '<span class="text-muted fst-italic">—</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php $jumlah = $childCounts[(int) $k['id']] ?? 0; ?>
                                <?php if ($jumlah > 0): ?>
                                    <span class="badge bg-info text-dark rounded-pill"><?= $jumlah ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/master/kategori/{$k['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/master/kategori/{$k['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
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
        <i class="bi bi-tags text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada kategori</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/kategori/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($kategoris)): ?>
<script>
$(document).ready(function () {
    $('#tabelKategori').DataTable({
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
