<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="ph-title">Master Tryout</div>
            <div class="ph-subtitle">Kelola data sesi tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/tryout/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Tryout
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
<?php if (!empty($tryouts)): ?>
<div class="card border-0 shadow-sm">
    <style>
        #tabelTryout_wrapper .dataTables_length label,
        #tabelTryout_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelTryout_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelTryout_wrapper .dataTables_info, #tabelTryout_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelTryout_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelTryout" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Tryout</th>
                        <th class="text-center">Durasi (menit)</th>
                        <th class="text-center">Jumlah Soal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tryouts as $i => $t): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($t['nama']) ?></td>
                    <td class="text-center"><?= (int) $t['durasi'] ?></td>
                            <td class="text-center">
                                <?= (int) $t['jumlah_soal_mapped'] ?>
                                <small class="text-muted d-block" style="font-size:.7rem">dari mapping</small>
                            </td>
                            <td class="text-center">
                                <?php if ($t['is_active']): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/master/tryout/{$t['id']}/preview-soal") ?>"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-info py-0 px-2"
                                   title="Preview Soal & Kunci">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url("admin/master/tryout/{$t['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/master/tryout/{$t['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus tryout ini?')">
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
        <i class="bi bi-journal-text text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada tryout</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/tryout/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($tryouts)): ?>
<script>
$(document).ready(function () {
    $('#tabelTryout').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ]
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
