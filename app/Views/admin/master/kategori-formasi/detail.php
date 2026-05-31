<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="<?= esc($kategori['icon'] ?? 'bi-briefcase') ?>"></i></div>
        <div>
            <div class="ph-title"><?= esc($kategori['nama']) ?></div>
            <div class="ph-subtitle">Daftar Formasi dalam Kategori</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
    <a href="<?= base_url('admin/master/kategori-formasi') ?>" class="ph-action">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Info Kategori -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:rgba(26,58,92,.08)">
                    <i class="<?= esc($kategori['icon'] ?? 'bi-folder') ?>" style="font-size:1.5rem;color:var(--sa-primary)"></i>
                </div>
            </div>
            <div class="col">
                <h5 class="mb-1 fw-bold"><?= esc($kategori['nama']) ?></h5>
                <?php if (!empty($kategori['deskripsi'])): ?>
                    <p class="text-muted mb-0 small"><?= esc($kategori['deskripsi']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <?php if ((int)$kategori['is_active'] === 1): ?>
                    <span class="badge bg-success">Aktif</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Nonaktif</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Form Tambah Formasi -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <span class="fw-semibold" style="font-size:.9rem"><i class="bi bi-plus-circle me-1"></i> Tambah Formasi Baru</span>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url("admin/master/kategori-formasi/{$kategori['id']}/formasi/store") ?>">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="nama" class="form-label">Nama Formasi <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', '')) ?>"
                           placeholder="Contoh: Pranata Komputer" required>
                </div>
                <div class="col-12 col-md-3">
                    <label for="deskripsi" class="form-label">Deskripsi <span class="text-muted small">(opsional)</span></label>
                    <input type="text" id="deskripsi" name="deskripsi" class="form-control"
                           value="<?= esc(old('deskripsi', '')) ?>"
                           placeholder="Deskripsi singkat formasi">
                </div>
                <div class="col-12 col-md-3">
                    <label for="referensi" class="form-label">Referensi <span class="text-muted small">(ID/Kode)</span></label>
                    <input type="number" id="referensi" name="referensi" class="form-control"
                           value="<?= esc(old('referensi', '')) ?>"
                           placeholder="Contoh: 1" min="0">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Formasi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold" style="font-size:.9rem">
            <i class="bi bi-list-ul me-1"></i> Daftar Formasi
            <span class="badge bg-info text-dark rounded-pill ms-1"><?= count($formasiList) ?></span>
        </span>
        <?php if (!empty($formasiList)): ?>
        <input type="text" id="searchFormasi" class="form-control form-control-sm" style="max-width:250px"
               placeholder="Cari formasi..." autocomplete="off">
        <?php endif; ?>
    </div>

    <?php if (!empty($formasiList)): ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px">No</th>
                        <th>Nama Formasi</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Referensi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formasiList as $i => $f): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-medium"><?= esc($f['nama']) ?></td>
                            <td class="text-muted small"><?= esc($f['deskripsi'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if (! empty($f['referensi'])): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><?= esc($f['referensi']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ((int)$f['is_active'] === 1): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit<?= $f['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="post"
                                      action="<?= base_url("admin/master/kategori-formasi/{$kategori['id']}/formasi/{$f['id']}/delete") ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus formasi ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?= $f['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="post" action="<?= base_url("admin/master/kategori-formasi/{$kategori['id']}/formasi/{$f['id']}/update") ?>">
                                                <?= csrf_field() ?>
                                                <div class="modal-header border-0 pb-0">
                                                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i>Edit Formasi</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label small">Nama Formasi <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama" class="form-control form-control-sm"
                                                               value="<?= esc($f['nama']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Deskripsi</label>
                                                        <input type="text" name="deskripsi" class="form-control form-control-sm"
                                                               value="<?= esc($f['deskripsi'] ?? '') ?>"
                                                               placeholder="Deskripsi singkat (opsional)">
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <label class="form-label small">Referensi</label>
                                                            <input type="number" name="referensi" class="form-control form-control-sm"
                                                                   value="<?= esc($f['referensi'] ?? '') ?>" min="0"
                                                                   placeholder="ID/Kode">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small">Status</label>
                                                            <select name="is_active" class="form-select form-select-sm">
                                                                <option value="1" <?= (int)$f['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                                <option value="0" <?= (int)$f['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-save me-1"></i>Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size:2rem"></i>
        <div class="mt-2 text-muted">Belum ada formasi dalam kategori ini</div>
        <div class="text-muted small">Gunakan form di atas untuk menambah formasi baru.</div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const input = document.getElementById('searchFormasi');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('table tbody tr').forEach(function (row) {
            const nama = row.querySelector('td:nth-child(2)');
            if (!nama) return;
            const text = nama.textContent.trim().toLowerCase();
            row.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
}());
</script>
<?= $this->endSection() ?>
