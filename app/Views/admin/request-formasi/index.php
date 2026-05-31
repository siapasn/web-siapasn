<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-inbox"></i></div>
        <div>
            <div class="ph-title">Request Formasi Tryout</div>
            <div class="ph-subtitle">Permintaan user untuk pembuatan tryout formasi baru</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-warning"><?= $totalPending ?></div>
                <div class="text-muted small">Menunggu</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success"><?= $totalApproved ?></div>
                <div class="text-muted small">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-danger"><?= $totalRejected ?></div>
                <div class="text-muted small">Ditolak</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Request -->
<div class="card border-0 shadow-sm">
    <?php if (! empty($requests)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>User</th>
                        <th>Formasi</th>
                        <th>Kategori</th>
                        <th>Pesan</th>
                        <th class="text-center">Status</th>
                        <th>Tanggal</th>
                        <th class="text-center pe-3" style="width:180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $i => $r): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-medium"><?= esc($r['user_nama']) ?></div>
                                <div class="text-muted small"><?= esc($r['user_email']) ?></div>
                            </td>
                            <td class="fw-medium"><?= esc($r['formasi_nama']) ?></td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-size:.7rem">
                                    <?= esc($r['kategori_formasi_nama'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="small text-muted" style="max-width:200px">
                                <?= esc($r['pesan'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Disetujui</span>
                                    <?php if ($r['notified_at']): ?>
                                        <br><span class="text-muted" style="font-size:.6rem"><i class="bi bi-envelope-check"></i> Notified</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ditolak</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                            <td class="text-center pe-3">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <!-- Approve -->
                                    <button type="button" class="btn btn-sm btn-success py-0 px-2"
                                            data-bs-toggle="modal" data-bs-target="#modalApprove<?= $r['id'] ?>"
                                            title="Setujui & Kirim Notifikasi">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                    <!-- Reject -->
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                            data-bs-toggle="modal" data-bs-target="#modalReject<?= $r['id'] ?>"
                                            title="Tolak">
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    <!-- Modal Approve -->
                                    <div class="modal fade" id="modalApprove<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-sm modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url("admin/request-formasi/{$r['id']}/approve") ?>">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header border-0 pb-0">
                                                        <h6 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-1"></i>Approve Request</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p class="small text-muted mb-2">
                                                            Setujui request <strong><?= esc($r['formasi_nama']) ?></strong> dari <strong><?= esc($r['user_nama']) ?></strong>?
                                                            Email notifikasi akan dikirim ke user.
                                                        </p>
                                                        <label class="form-label small">Catatan untuk user (opsional):</label>
                                                        <textarea name="admin_note" class="form-control form-control-sm" rows="2"
                                                                  placeholder="Misal: Paket tryout sudah tersedia di katalog"></textarea>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="bi bi-send me-1"></i>Approve & Kirim Email
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Reject -->
                                    <div class="modal fade" id="modalReject<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-sm modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url("admin/request-formasi/{$r['id']}/reject") ?>">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header border-0 pb-0">
                                                        <h6 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-1"></i>Tolak Request</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <label class="form-label small">Alasan penolakan (opsional):</label>
                                                        <textarea name="admin_note" class="form-control form-control-sm" rows="2"
                                                                  placeholder="Misal: Formasi ini belum tersedia soalnya"></textarea>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-x-lg me-1"></i>Tolak
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small"><?= $r['admin_note'] ? esc($r['admin_note']) : '—' ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
        Belum ada request formasi dari user.
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
