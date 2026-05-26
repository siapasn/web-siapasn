<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h5 class="text-center mb-4 fw-semibold">Buat Akun Baru</h5>

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

<form action="<?= base_url('register') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input
                type="text"
                class="form-control"
                id="nama"
                name="nama"
                value="<?= esc(old('nama')) ?>"
                placeholder="Nama lengkap Anda"
                required
                autocomplete="name"
            >
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
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
        <label for="telepon" class="form-label">Nomor Telepon <span class="text-muted small">(opsional)</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input
                type="tel"
                class="form-control"
                id="telepon"
                name="telepon"
                value="<?= esc(old('telepon')) ?>"
                placeholder="08xxxxxxxxxx"
                autocomplete="tel"
            >
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
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
        <label for="password_confirm" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input
                type="password"
                class="form-control"
                id="password_confirm"
                name="password_confirm"
                placeholder="Ulangi password"
                required
                autocomplete="new-password"
            >
        </div>
    </div>

    <div class="mb-4">
        <label for="captcha" class="form-label">Captcha: <strong><?= esc($captcha) ?></strong> <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
            <input
                type="number"
                class="form-control"
                id="captcha"
                name="captcha"
                placeholder="Masukkan jawaban"
                required
                autocomplete="off"
            >
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-person-plus me-1"></i> Daftar
    </button>
</form>

<hr class="my-4">

<p class="text-center text-muted small mb-0">
    Sudah punya akun?
    <a href="<?= base_url('login') ?>" class="text-decoration-none fw-semibold">Masuk di sini</a>
</p>

<p class="text-center mt-2 mb-0">
    <a href="<?= base_url('/') ?>" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
    </a>
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
