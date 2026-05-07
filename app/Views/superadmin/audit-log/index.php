<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="ph-title">Audit Log</div>
            <div class="ph-subtitle">Rekam jejak aktivitas pengguna sistem</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('superadmin/audit-log') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Filter User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua User</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (string) $userId === (string) $u['id'] ? 'selected' : '' ?>>
                            <?= esc($u['nama']) ?> (<?= esc($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?= esc($dateFrom) ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?= esc($dateTo) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="<?= base_url('superadmin/audit-log') ?>" class="btn btn-outline-secondary btn-sm ms-1">
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
            <table id="tabelAuditLog" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Detail</th>
                        <th>IP Address</th>
                        <th class="pe-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= ($page - 1) * $perPage + $i + 1 ?></td>
                                <td>
                                    <?php if (! empty($log['user_nama'])): ?>
                                        <div class="fw-medium"><?= esc($log['user_nama']) ?></div>
                                        <small class="text-muted"><?= esc($log['user_email']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">User dihapus</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill px-2"><?= esc($log['aksi']) ?></span>
                                </td>
                                <td class="text-muted" style="max-width:300px">
                                    <span title="<?= esc($log['detail']) ?>">
                                        <?= esc(mb_strimwidth($log['detail'], 0, 80, '...')) ?>
                                    </span>
                                </td>
                                <td class="text-muted font-monospace small"><?= esc($log['ip_address']) ?></td>
                                <td class="pe-3 text-muted small">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-journal-x fs-4 d-block mb-1"></i>
                                Tidak ada data log
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
                Menampilkan <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> dari <?= $total ?> log
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php $totalPages = (int) ceil($total / $perPage); ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="<?= base_url('superadmin/audit-log') ?>?page=<?= $p ?>&user_id=<?= urlencode($userId) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">
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
    $('#tabelAuditLog').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        columnDefs: [
            { orderable: false, targets: [0] }
        ]
    });
});
</script>

<?= $this->endSection() ?>
