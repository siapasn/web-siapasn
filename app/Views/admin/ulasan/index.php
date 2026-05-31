<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-star-half"></i></div>
        <div>
            <div class="ph-title">Ulasan Produk</div>
            <div class="ph-subtitle">Kelola ulasan dan penilaian dari user</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (! empty($ulasans)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>User</th>
                        <th>Produk</th>
                        <th class="text-center">Rating</th>
                        <th>Komentar</th>
                        <th class="text-center">Visible</th>
                        <th>Tanggal</th>
                        <th class="text-center pe-3" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ulasans as $i => $u): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-medium small"><?= esc($u['user_nama']) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= esc($u['user_email']) ?></div>
                            </td>
                            <td class="small"><?= esc($u['produk_nama']) ?></td>
                            <td class="text-center">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="bi bi-star<?= $s <= (int)$u['rating'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size:.8rem"></i>
                                <?php endfor; ?>
                            </td>
                            <td class="small text-muted" style="max-width:250px"><?= esc($u['komentar'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if ((int)$u['is_visible']): ?>
                                    <span class="badge bg-success rounded-pill">Tampil</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-center pe-3">
                                <form method="post" action="<?= base_url("admin/ulasan/{$u['id']}/toggle") ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-<?= (int)$u['is_visible'] ? 'secondary' : 'success' ?> py-0 px-2"
                                            title="<?= (int)$u['is_visible'] ? 'Sembunyikan' : 'Tampilkan' ?>">
                                        <i class="bi bi-eye<?= (int)$u['is_visible'] ? '-slash' : '' ?>"></i>
                                    </button>
                                </form>
                                <form method="post" action="<?= base_url("admin/ulasan/{$u['id']}/delete") ?>" class="d-inline"
                                      onsubmit="return confirm('Hapus ulasan ini?')">
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
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-star fs-2 d-block mb-2"></i>
        Belum ada ulasan dari user.
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
