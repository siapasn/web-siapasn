<?= $this->extend('layouts/admin') ?>

<?php $isEdit = !empty($event); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-calendar-event"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Event' : 'Buat Event Baru' ?></div>
            <div class="ph-subtitle">Tryout Event / Nasional</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-12">
                    <label for="nama" class="form-label">Nama Event <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $event['nama'] ?? '')) ?>"
                           placeholder="Contoh: Tryout Nasional SKD Vol. 1" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="tryout_id" class="form-label">Tryout <span class="text-danger">*</span></label>
                    <select id="tryout_id" name="tryout_id" class="form-select" required>
                        <option value="">-- Pilih Tryout --</option>
                        <?php foreach ($tryouts as $t): ?>
                            <option value="<?= $t['id'] ?>"
                                <?= (string) old('tryout_id', $event['tryout_id'] ?? '') === (string) $t['id'] ? 'selected' : '' ?>>
                                <?= esc($t['nama']) ?> (<?= $t['durasi'] ?> menit)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="max_percobaan" class="form-label">Max Percobaan <span class="text-danger">*</span></label>
                    <input type="number" id="max_percobaan" name="max_percobaan" class="form-control"
                           value="<?= esc(old('max_percobaan', $event['max_percobaan'] ?? '1')) ?>"
                           min="1" max="9" required>
                    <div class="form-text">Jumlah maksimal percobaan per peserta.</div>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                               <?= old('is_active', $event['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label for="mulai_pendaftaran" class="form-label">Mulai Pendaftaran <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="mulai_pendaftaran" name="mulai_pendaftaran" class="form-control"
                           value="<?= esc(old('mulai_pendaftaran', isset($event['mulai_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($event['mulai_pendaftaran'])) : '')) ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="tutup_pendaftaran" class="form-label">Tutup Pendaftaran <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="tutup_pendaftaran" name="tutup_pendaftaran" class="form-control"
                           value="<?= esc(old('tutup_pendaftaran', isset($event['tutup_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($event['tutup_pendaftaran'])) : '')) ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="mulai_pelaksanaan" class="form-label">Mulai Pelaksanaan <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="mulai_pelaksanaan" name="mulai_pelaksanaan" class="form-control"
                           value="<?= esc(old('mulai_pelaksanaan', isset($event['mulai_pelaksanaan']) ? date('Y-m-d\TH:i', strtotime($event['mulai_pelaksanaan'])) : '')) ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="tutup_pelaksanaan" class="form-label">Tutup Pelaksanaan <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="tutup_pelaksanaan" name="tutup_pelaksanaan" class="form-control"
                           value="<?= esc(old('tutup_pelaksanaan', isset($event['tutup_pelaksanaan']) ? date('Y-m-d\TH:i', strtotime($event['tutup_pelaksanaan'])) : '')) ?>" required>
                </div>

                <div class="col-12">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi event (opsional)"><?= esc(old('deskripsi', $event['deskripsi'] ?? '')) ?></textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="banner" class="form-label">Banner</label>
                    <?php if ($isEdit && ! empty($event['banner_url'])): ?>
                        <div class="mb-2">
                            <img src="<?= base_url($event['banner_url']) ?>" class="rounded border" style="max-height:120px">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="hapus_banner" name="hapus_banner" value="1">
                            <label class="form-check-label text-danger small" for="hapus_banner">Hapus banner</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="banner" name="banner" class="form-control" accept="image/*">
                    <div class="form-text">Format: JPG, PNG, WebP. Maks 2 MB.</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Buat Event' ?>
                </button>
                <a href="<?= base_url('admin/tryout-event') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
