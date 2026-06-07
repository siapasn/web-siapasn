<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-people"></i></div>
        <div>
            <div class="ph-title">Master User</div>
            <div class="ph-subtitle">Kelola data pengguna aplikasi</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/user/create') ?>" class="ph-action">
        <i class="bi bi-plus-lg"></i> Tambah User
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/master/user') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small text-muted mb-1">Cari Nama / Email</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Ketik nama atau email..." value="<?= esc($search) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Filter Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    <option value="user"        <?= $role === 'user'        ? 'selected' : '' ?>>User</option>
                    <option value="admin"       <?= $role === 'admin'       ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="<?= base_url('admin/master/user') ?>" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <style>
        #tabelUser_wrapper .dataTables_length label,
        #tabelUser_wrapper .dataTables_filter label { margin-bottom:0; font-size:.875rem; }
        #tabelUser_wrapper .dataTables_filter input { margin-left:.4rem; border-radius:.375rem; border:1px solid #dee2e6; padding:.25rem .5rem; font-size:.875rem; }
        #tabelUser_wrapper .dataTables_info, #tabelUser_wrapper .dataTables_paginate { font-size:.875rem; }
        #tabelUser_wrapper .paginate_button { border-radius:.375rem !important; }
    </style>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelUser" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-medium"><?= esc($u['nama']) ?></div>
                                    <?php if (!empty($u['telepon'])): ?>
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
                                    <a href="<?= base_url("admin/master/user/{$u['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Tidak ada data user
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('#tabelUser').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
    },
    pageLength: 25,
    ordering: true,
    order: [],
    dom: '<"px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"lf>rt<"px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2"ip>',
    columnDefs: [
        { orderable: false, targets: [0, 5] },
        { searchable: false, targets: [0, 5] },
    ]
});

// Toggle status user via AJAX
document.querySelectorAll('.toggle-status').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        const id    = this.dataset.id;
        const value = this.checked ? 1 : 0;
        const self  = this;

        fetch('<?= base_url('admin/master/user/toggle-status') ?>', {
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
                // Rollback toggle jika gagal
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
