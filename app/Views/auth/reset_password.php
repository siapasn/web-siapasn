<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<h5 class="text-center mb-2 fw-semibold">Lupa Password?</h5>
<p class="text-center text-muted small mb-4">
    Masukkan email Anda dan kami akan mengirimkan tautan untuk mereset password.
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

<form action="<?= base_url('reset-password') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <div class="mb-4">
        <label for="email" class="form-label">Alamat Email</label>
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

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-send me-1"></i> Kirim Link Reset
    </button>
</form>

<hr class="my-4">

<p class="text-center text-muted small mb-0">
    Ingat password Anda?
    <a href="<?= base_url('login') ?>" class="text-decoration-none fw-semibold">Kembali ke Login</a>
</p>

<?= $this->endSection() ?>
