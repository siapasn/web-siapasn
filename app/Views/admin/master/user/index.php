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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelUser" class="table table-hover align-middle mb-0">
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
                                <td class="ps-3 text-muted"><?= ($page - 1) * $perPage + $i + 1 ?></td>
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
                                    <?php if ($u['is_active']): ?>
                                        <span class="badge bg-success rounded-pill px-2">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-2">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="<?= base_url("admin/master/user/{$u['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ((int) session('user_id') !== (int) $u['id']): ?>
                                        <form method="post"
                                              action="<?= base_url("admin/master/user/{$u['id']}/delete") ?>"
                                              class="d-inline"
                                              onsubmit="return confirm('Nonaktifkan user ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-person-x"></i>
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
                                Tidak ada data user
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
                Menampilkan <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> dari <?= $total ?> user
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php $totalPages = (int) ceil($total / $perPage); ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="<?= base_url('admin/master/user') ?>?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function () {
    $('#tabelUser').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ]
    });
});
</script>

<?= $this->endSection() ?>
