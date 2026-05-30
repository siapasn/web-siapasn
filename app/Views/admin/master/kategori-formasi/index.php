<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-briefcase"></i></div>
        <div>
            <div class="ph-title">Kategori Formasi CPNS</div>
            <div class="ph-subtitle">Kelola kategori formasi dan daftar formasi di dalamnya</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/kategori-formasi/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Kategori
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (!empty($kategoris)): ?>
<!-- Grid Kategori Formasi -->
<div class="row g-3 mb-4">
    <?php foreach ($kategoris as $k): ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:rgba(26,58,92,.08);flex-shrink:0">
                        <i class="<?= esc($k['icon'] ?? 'bi-folder') ?>" style="font-size:1.2rem;color:var(--sa-primary)"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-semibold" style="font-size:.9rem"><?= esc($k['nama']) ?></h6>
                    </div>
                    <?php if ((int)$k['is_active'] === 1): ?>
                        <span class="badge bg-success rounded-pill" style="font-size:.65rem">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill" style="font-size:.65rem">Nonaktif</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($k['deskripsi'])): ?>
                    <p class="text-muted small mb-2 flex-grow-1" style="font-size:.78rem"><?= esc($k['deskripsi']) ?></p>
                <?php else: ?>
                    <div class="flex-grow-1"></div>
                <?php endif; ?>

                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                    <span class="text-muted small">
                        <i class="bi bi-list-ul me-1"></i><?= (int)$k['jumlah_formasi'] ?> formasi
                    </span>
                    <div class="d-flex gap-1">
                        <a href="<?= base_url("admin/master/kategori-formasi/{$k['id']}/detail") ?>"
                           class="btn btn-sm btn-outline-info py-0 px-2" title="Lihat Formasi">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= base_url("admin/master/kategori-formasi/{$k['id']}/edit") ?>"
                           class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post"
                              action="<?= base_url("admin/master/kategori-formasi/{$k['id']}/delete") ?>"
                              class="d-inline"
                              onsubmit="return confirm('Hapus kategori formasi ini?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabel Kategori Formasi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3">
        <i class="bi bi-table text-muted"></i>
        <span class="fw-semibold" style="font-size:.9rem">Daftar Lengkap Kategori Formasi</span>
    </div>
    <style>
        #tabelKategoriFormasi_wrapper .dataTables_length label,
        #tabelKategoriFormasi_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelKategoriFormasi_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelKategoriFormasi_wrapper .dataTables_info, #tabelKategoriFormasi_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelKategoriFormasi_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelKategoriFormasi" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Kategori Formasi</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Formasi</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategoris as $i => $k): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="<?= esc($k['icon'] ?? 'bi-folder') ?> text-primary"></i>
                                    <span class="fw-medium"><?= esc($k['nama']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted small"><?= esc($k['deskripsi'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if ((int)$k['jumlah_formasi'] > 0): ?>
                                    <span class="badge bg-info text-dark rounded-pill"><?= (int)$k['jumlah_formasi'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int)$k['urutan'] ?></td>
                            <td class="text-center">
                                <?php if ((int)$k['is_active'] === 1): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/master/kategori-formasi/{$k['id']}/detail") ?>"
                                   class="btn btn-sm btn-outline-info py-0 px-2" title="Lihat Formasi">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url("admin/master/kategori-formasi/{$k['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/master/kategori-formasi/{$k['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus kategori formasi ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
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
        <i class="bi bi-briefcase text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada kategori formasi</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/kategori-formasi/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($kategoris)): ?>
<script>
$(document).ready(function () {
    $('#tabelKategoriFormasi').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
        pageLength: 25,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ]
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
