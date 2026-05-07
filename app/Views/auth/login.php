<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h5 class="text-center mb-4 fw-semibold">Masuk ke Akun Anda</h5>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i>
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

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

<form action="<?= base_url('login') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                value="<?= esc(old('email')) ?>"
                placeholder="nama@email.com"
                required
                autocomplete="email"
            >
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required
                autocomplete="current-password"
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password">
                <i class="bi bi-eye" id="toggleIcon"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= base_url('reset-password') ?>" class="text-decoration-none small">Lupa password?</a>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
    </button>
</form>

<hr class="my-4">

<p class="text-center text-muted small mb-0">
    Belum punya akun?
    <a href="<?= base_url('register') ?>" class="text-decoration-none fw-semibold">Daftar sekarang</a>
</p>

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
