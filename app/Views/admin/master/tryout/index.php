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
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelTryout" class="table table-hover align-middle mb-0">
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
                    <?php if (!empty($tryouts)): ?>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data tryout
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
    $('#tabelTryout').DataTable({
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
</script>

<?= $this->endSection() ?>
