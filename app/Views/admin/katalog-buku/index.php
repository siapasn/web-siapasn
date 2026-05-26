<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-book"></i></div>
        <div>
            <div class="ph-title">Katalog Buku</div>
            <div class="ph-subtitle">Kelola daftar buku yang dijual</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/katalog-buku/import') ?>" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-arrow-up me-1"></i>Import CSV
        </a>
        <a href="<?= base_url('admin/katalog-buku/create') ?>" class="ph-action">
            <i class="bi bi-plus-lg"></i> Tambah Buku
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (! empty($katalogBuku)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelBuku" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th style="width:70px">Cover</th>
                        <th>Judul</th>
                        <th class="text-center" style="width:80px">Aktif</th>
                        <th class="text-center" style="width:100px">Highlight</th>
                        <th class="text-center pe-3" style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($katalogBuku as $i => $b): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <img src="<?= esc($b['url_thumbnail']) ?>"
                                     alt="" class="rounded"
                                     style="width:50px;height:50px;object-fit:cover"
                                     referrerpolicy="no-referrer"
                                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23e2e8f0%22 width=%2250%22 height=%2250%22/></svg>'">
                            </td>
                            <td class="fw-medium" style="max-width:300px">
                                <div class="text-truncate"><?= esc($b['judul']) ?></div>
                                <a href="<?= esc($b['url_shopee']) ?>" target="_blank" class="text-muted small text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Shopee
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input toggle-flag" type="checkbox"
                                           data-id="<?= $b['id'] ?>" data-field="is_active"
                                           <?= $b['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input toggle-flag" type="checkbox"
                                           data-id="<?= $b['id'] ?>" data-field="is_highlight"
                                           <?= ($b['is_highlight'] ?? 0) ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/katalog-buku/{$b['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/katalog-buku/{$b['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus buku ini?')">
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
        <i class="bi bi-book text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada buku</div>
        <div class="text-muted small mt-1">
            <a href="<?= base_url('admin/katalog-buku/create') ?>">Tambah buku baru</a> atau
            <a href="<?= base_url('admin/katalog-buku/import') ?>">import dari CSV</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (! empty($katalogBuku)): ?>
<script>
$('#tabelBuku').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
    dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
    pageLength: 25,
    columnDefs: [{ orderable: false, targets: [0, 1, 3, 4, 5] }]
});

// AJAX toggle status & highlight
document.querySelectorAll('.toggle-flag').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        const id    = this.dataset.id;
        const field = this.dataset.field;
        const value = this.checked ? 1 : 0;
        const self  = this;

        fetch('<?= base_url('admin/katalog-buku/toggle') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                id: id,
                field: field,
                value: value,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (! data.status) {
                // Revert toggle jika gagal
                self.checked = ! self.checked;
                alert(data.message || 'Gagal mengubah status.');
            }
        })
        .catch(() => {
            self.checked = ! self.checked;
            alert('Terjadi kesalahan jaringan.');
        });
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
