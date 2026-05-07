<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h5 class="text-center mb-2 fw-semibold">Buat Password Baru</h5>
<p class="text-center text-muted small mb-4">
    Masukkan password baru Anda di bawah ini.
</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<form action="<?= base_url('reset-password/update') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">

    <div class="mb-3">
        <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                required
                autocomplete="new-password"
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password">
                <i class="bi bi-eye" id="toggleIcon"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input
                type="password"
                class="form-control"
                id="password_confirm"
                name="password_confirm"
                placeholder="Ulangi password baru"
                required
                autocomplete="new-password"
            >
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-check-circle me-1"></i> Ubah Password
    </button>
</form>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput  = document.getElementById('password');
    const toggleIcon     = document.getElementById('toggleIcon');

    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('bi-eye', !isPassword);
            toggleIcon.classList.toggle('bi-eye-slash', isPassword);
        });
    }
</script>

<?= $this->endSection() ?>
