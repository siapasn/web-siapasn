<?= $this->extend('layouts/admin') ?>

<?php $isEdit = ! empty($user); ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-shield-person"></i></div>
        <div>
            <div class="ph-title"><?= $isEdit ? 'Edit Akun' : 'Tambah Akun' ?></div>
            <div class="ph-subtitle">Manajemen Akun</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Validation Errors -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Nama -->
                <div class="col-12 col-md-6">
                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="<?= esc(old('nama', $user['nama'] ?? '')) ?>"
                           placeholder="Masukkan nama lengkap" required>
                </div>

                <!-- Email -->
                <div class="col-12 col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= esc(old('email', $user['email'] ?? '')) ?>"
                           placeholder="contoh@email.com" required>
                </div>

                <!-- Telepon -->
                <div class="col-12 col-md-6">
                    <label for="telepon" class="form-label">Nomor Telepon</label>
                    <input type="text" id="telepon" name="telepon" class="form-control"
                           value="<?= esc(old('telepon', $user['telepon'] ?? '')) ?>"
                           placeholder="08xxxxxxxxxx">
                </div>

                <!-- Role -->
                <div class="col-12 col-md-6">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <?php
                        $roles = [
                            'user'        => 'User',
                            'admin'       => 'Admin',
                            'super_admin' => 'Super Admin',
                        ];
                        $selectedRole = old('role', $user['role'] ?? '');
                        foreach ($roles as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $selectedRole === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Password -->
                <div class="col-12 col-md-6">
                    <label for="password" class="form-label">
                        Password
                        <?php if (! $isEdit): ?>
                            <span class="text-danger">*</span>
                        <?php else: ?>
                            <small class="text-muted">(kosongkan jika tidak ingin mengubah)</small>
                        <?php endif; ?>
                    </label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="<?= $isEdit ? 'Isi untuk mengubah password' : 'Min. 8 karakter' ?>"
                               <?= ! $isEdit ? 'required' : '' ?>>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <?php if (! $isEdit): ?>
                        <div class="form-text">Minimal 8 karakter.</div>
                    <?php endif; ?>
                </div>

                <!-- Status Aktif -->
                <div class="col-12 col-md-6">
                    <label class="form-label d-block">Status Akun</label>
                    <div class="form-check form-switch mt-1">
                        <?php $isActive = (int) old('is_active', $user['is_active'] ?? 1); ?>
                        <!-- Hidden field agar nilai 0 tetap terkirim saat checkbox tidak dicentang -->
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= $isActive ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>

            </div><!-- /.row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Akun' ?>
                </button>
                <a href="<?= base_url('superadmin/akun') ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
(function () {
    const toggleBtn = document.getElementById('togglePassword');
    const pwInput   = document.getElementById('password');
    const eyeIcon   = document.getElementById('eyeIcon');

    if (toggleBtn && pwInput) {
        toggleBtn.addEventListener('click', function () {
            const isHidden = pwInput.type === 'password';
            pwInput.type   = isHidden ? 'text' : 'password';
            eyeIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
}());
</script>

<?= $this->endSection() ?>
