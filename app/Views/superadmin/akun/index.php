<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-shield-person"></i></div>
        <div>
            <div class="ph-title">Manajemen Akun</div>
            <div class="ph-subtitle">Kelola akun administrator sistem</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('superadmin/akun/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah Akun
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

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('superadmin/akun') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Cari Nama / Email</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Ketik nama atau email..." value="<?= esc($search) ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Filter Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    <option value="user"        <?= $role === 'user'        ? 'selected' : '' ?>>User</option>
                    <option value="admin"       <?= $role === 'admin'       ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="<?= base_url('superadmin/akun') ?>" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <style>
        #tabelAkun_wrapper .dataTables_length label,
        #tabelAkun_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelAkun_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelAkun_wrapper .dataTables_info, #tabelAkun_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelAkun_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelAkun" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($users)): ?>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= ($page - 1) * $perPage + $i + 1 ?></td>
                                <td>
                                    <div class="fw-medium"><?= esc($u['nama']) ?></div>
                                    <?php if (! empty($u['telepon'])): ?>
                                        <small class="text-muted"><?= esc($u['telepon']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= esc($u['email']) ?></td>
                                <td>
                                    <?php
                                    $roleMap = [
                                        'user'        => ['bg-secondary', 'User'],
                                        'admin'       => ['bg-primary',   'Admin'],
                                        'super_admin' => ['bg-danger',    'Super Admin'],
                                    ];
                                    $ri = $roleMap[$u['role']] ?? ['bg-secondary', esc($u['role'])];
                                    ?>
                                    <span class="badge <?= $ri[0] ?> rounded-pill px-2"><?= $ri[1] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php $canToggle = (int) session('user_id') !== (int) $u['id']; ?>
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input toggle-status" type="checkbox"
                                               role="switch"
                                               id="toggle-<?= $u['id'] ?>"
                                               data-id="<?= $u['id'] ?>"
                                               <?= $u['is_active'] ? 'checked' : '' ?>
                                               <?= ! $canToggle ? 'disabled title="Tidak dapat mengubah status akun sendiri"' : '' ?>
                                               style="cursor:<?= $canToggle ? 'pointer' : 'not-allowed' ?>">
                                    </div>
                                </td>
                                <td class="text-center pe-3">
                                    <!-- Edit -->
                                    <a href="<?= base_url("superadmin/akun/{$u['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary py-0 px-2"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Hapus -->
                                    <?php if ((int) session('user_id') !== (int) $u['id']): ?>
                                        <form method="post"
                                              action="<?= base_url("superadmin/akun/{$u['id']}/delete") ?>"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus akun ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Tidak ada data akun
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total > $perPage): ?>
        <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-2 px-3">
            <small class="text-muted">
                Menampilkan <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> dari <?= $total ?> akun
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php $totalPages = (int) ceil($total / $perPage); ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="<?= base_url('superadmin/akun') ?>?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    $('#tabelAkun').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ]
    });
});

// Toggle status akun via AJAX
document.querySelectorAll('.toggle-status').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        const id    = this.dataset.id;
        const value = this.checked ? 1 : 0;
        const self  = this;

        fetch('<?= base_url('superadmin/akun/toggle-status') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                id: id,
                value: value,
            }),
        })
        .then(r => r.json())
        .then(function (data) {
            if (! data.status) {
                self.checked = ! self.checked;
                alert(data.message || 'Gagal mengubah status.');
            }
        })
        .catch(function () {
            self.checked = ! self.checked;
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    });
});
</script>
<?= $this->endSection() ?>
