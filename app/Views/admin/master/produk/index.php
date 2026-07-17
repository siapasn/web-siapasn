<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="ph-title">Master Produk</div>
            <div class="ph-subtitle">Kelola data produk dan paket tryout</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/produk/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Produk
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <style>
        #tabelProduk_wrapper .dataTables_length label,
        #tabelProduk_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelProduk_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelProduk_wrapper .dataTables_info, #tabelProduk_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelProduk_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <?php if (! empty($produks)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelProduk" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Formasi</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center" style="width:80px">Aktif</th>
                        <th class="text-center" style="width:100px">Highlight</th>
                        <th class="text-center pe-3" style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produks as $i => $p): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($p['nama']) ?></td>
                            <td>
                                <?php if (! empty($p['kategori_nama'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-dark border border-primary-subtle">
                                        <?= esc($p['kategori_nama']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (! empty($p['formasi_nama'])): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle">
                                        <?= esc($p['formasi_nama']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">Rp <?= number_format((float) $p['harga'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input toggle-produk" type="checkbox"
                                           data-id="<?= $p['id'] ?>" data-field="is_active"
                                           <?= $p['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input toggle-produk" type="checkbox"
                                           data-id="<?= $p['id'] ?>" data-field="is_highlight"
                                           <?= ($p['is_highlight'] ?? 0) ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <a href="<?= base_url("admin/master/produk/{$p['id']}/edit") ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post"
                                      action="<?= base_url("admin/master/produk/{$p['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus produk ini?')">
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
    <?php else: ?>
    <div class="card-body text-center py-5">
        <i class="bi bi-box-seam text-muted" style="font-size:2.5rem"></i>
        <div class="mt-3 fw-semibold text-muted">Belum ada produk</div>
        <div class="text-muted small mt-1">Mulai dengan <a href="<?= base_url('admin/master/produk/create') ?>">menambah produk baru</a></div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php if (! empty($produks)): ?>
$('#tabelProduk').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
    },
    dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
    pageLength: 25,
    ordering: true,
    order: [],
    columnDefs: [
        { orderable: false, targets: [0, 5, 6, 7] },
        { searchable: false, targets: [0, 5, 6, 7] },
    ]
});

// AJAX toggle status & highlight produk
document.querySelectorAll('.toggle-produk').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        const id    = this.dataset.id;
        const field = this.dataset.field;
        const value = this.checked ? 1 : 0;
        const self  = this;

        fetch('<?= base_url('admin/master/produk/toggle') ?>', {
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
<?php endif; ?>
</script>
<?= $this->endSection() ?>
