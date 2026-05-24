<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-file-richtext"></i></div>
        <div>
            <div class="ph-title">Web Content</div>
            <div class="ph-subtitle">Kelola konten halaman publik (Syarat, Privasi, Hero, dll.)</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/konten/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Konten
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

<!-- Info -->
<div class="alert alert-info border-0 d-flex align-items-start gap-2 mb-3" style="background:#e8f4fd;">
    <i class="bi bi-info-circle-fill mt-1" style="color:#0d6efd; flex-shrink:0;"></i>
    <div style="font-size:.875rem;">
        <strong>Konten sistem</strong> (slug: syarat-ketentuan, kebijakan-privasi, hubungi-kami, hero_tagline, dll.)
        tidak dapat dihapus, hanya bisa dinonaktifkan atau diedit isinya.
    </div>
</div>

<!-- Tipe Legend -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge rounded-pill" style="background:#1a3a5c; font-size:.78rem; padding:.4rem .8rem;">
        <i class="bi bi-file-richtext me-1"></i> halaman = konten HTML (editor)
    </span>
    <span class="badge rounded-pill bg-warning text-dark" style="font-size:.78rem; padding:.4rem .8rem;">
        <i class="bi bi-fonts me-1"></i> teks = teks biasa
    </span>
    <span class="badge rounded-pill bg-success" style="font-size:.78rem; padding:.4rem .8rem;">
        <i class="bi bi-123 me-1"></i> angka = nilai numerik (statistik)
    </span>
</div>

<!-- Table -->
<?php if (!empty($kontenList)): ?>
<div class="card border-0 shadow-sm">
    <style>
        #tabelKonten_wrapper .dataTables_length label,
        #tabelKonten_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelKonten_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelKonten_wrapper .dataTables_info, #tabelKonten_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelKonten_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelKonten" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Judul</th>
                        <th>Slug</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Status</th>
                        <th>Diperbarui</th>
                        <th class="text-center pe-3" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $protected = [
                        'syarat-ketentuan', 'kebijakan-privasi', 'hubungi-kami',
                        'kontak_email', 'kontak_whatsapp', 'kontak_alamat',
                        'hero_tagline', 'hero_deskripsi',
                        'stat_pengguna', 'stat_soal', 'stat_paket',
                    ];
                    ?>
                    <?php foreach ($kontenList as $i => $k): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-medium"><?= esc($k['judul']) ?></div>
                                <?php if (in_array($k['slug'], $protected, true)): ?>
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Konten sistem</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="text-primary" style="font-size:.8rem;"><?= esc($k['slug']) ?></code>
                            </td>
                            <td class="text-center">
                                <?php if ($k['tipe'] === 'halaman'): ?>
                                    <span class="badge rounded-pill" style="background:#1a3a5c;">halaman</span>
                                <?php elseif ($k['tipe'] === 'teks'): ?>
                                    <span class="badge rounded-pill bg-warning text-dark">teks</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-success">angka</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($k['is_active']): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $k['updated_at'] ? date('d/m/Y H:i', strtotime($k['updated_at'])) : '-' ?>
                                </small>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/konten/{$k['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (!in_array($k['slug'], $protected, true)): ?>
                                    <form method="post"
                                          action="<?= base_url("admin/konten/{$k['id']}/delete") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus konten \"<?= esc($k['judul']) ?>\"?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2 disabled" title="Konten sistem tidak dapat dihapus">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                <?php endif; ?>
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
        <i class="bi bi-file-text text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada konten</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/konten/create') ?>">menambah data baru</a></div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($kontenList)): ?>
<script>
$(document).ready(function () {
    $('#tabelKonten').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
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
