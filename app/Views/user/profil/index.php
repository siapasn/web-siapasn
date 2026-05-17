<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon">
        <i class="bi bi-person-circle"></i>
    </div>
    <div>
        <div class="ph-title">Profil Saya</div>
        <div class="ph-subtitle">Kelola informasi akun Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.profil-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a3a5c, #1e5080);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #fff;
    font-weight: 700;
    flex-shrink: 0;
}
.tab-nav .nav-link {
    color: #6c757d;
    font-weight: 500;
    border-radius: .5rem .5rem 0 0;
    padding: .6rem 1.25rem;
    font-size: .9rem;
}
.tab-nav .nav-link.active {
    color: #1a3a5c;
    font-weight: 700;
    border-bottom: 2px solid #1a3a5c;
    background: transparent;
}
.form-label {
    font-weight: 600;
    font-size: .85rem;
    color: #374151;
}
.field-hint {
    font-size: .75rem;
    color: #9ca3af;
    margin-top: .2rem;
}
</style>

<?php
$tabAktif     = session()->getFlashdata('tab_aktif') ?? 'profil';
$errorProfil  = session()->getFlashdata('error_profil')  ?? [];
$errorPassword = session()->getFlashdata('error_password') ?? [];
$oldInput     = session()->getFlashdata('_ci_old_input') ?? [];
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">

        <!-- Info Akun -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="profil-avatar">
                    <?= strtoupper(mb_substr($user['nama'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold fs-5" style="color:#1a3a5c"><?= esc($user['nama']) ?></div>
                    <div class="text-muted small"><?= esc($user['email']) ?></div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mt-1" style="font-size:.72rem">
                        <i class="bi bi-shield-check me-1"></i><?= ucfirst(esc($user['role'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tab Navigasi -->
        <ul class="nav tab-nav border-bottom mb-0 px-1" id="profilTab">
            <li class="nav-item">
                <button class="nav-link <?= $tabAktif === 'profil' ? 'active' : '' ?>"
                        data-tab="profil" type="button">
                    <i class="bi bi-person me-1"></i>Data Diri
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $tabAktif === 'password' ? 'active' : '' ?>"
                        data-tab="password" type="button">
                    <i class="bi bi-lock me-1"></i>Ubah Password
                </button>
            </li>
        </ul>

        <!-- Tab: Data Diri -->
        <div id="tab-profil" class="tab-pane <?= $tabAktif !== 'profil' ? 'd-none' : '' ?>">
            <div class="card border-0 shadow-sm rounded-bottom-3 rounded-top-0">
                <div class="card-body p-4">

                    <?php if (! empty($errorProfil)): ?>
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errorProfil as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('user/profil/update') ?>" method="post" novalidate>
                        <?= csrf_field() ?>

                        <!-- Email (read-only) -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control bg-light" value="<?= esc($user['email']) ?>" readonly>
                            <div class="field-hint"><i class="bi bi-info-circle me-1"></i>Email tidak dapat diubah.</div>
                        </div>

                        <!-- Nama -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="nama"
                                   name="nama"
                                   class="form-control <?= isset($errorProfil['nama']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('nama', $user['nama'])) ?>"
                                   placeholder="Masukkan nama lengkap"
                                   maxlength="100"
                                   required>
                            <?php if (isset($errorProfil['nama'])): ?>
                                <div class="invalid-feedback"><?= esc($errorProfil['nama']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- No HP -->
                        <div class="mb-4">
                            <label for="telepon" class="form-label">No HP / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text"
                                       id="telepon"
                                       name="telepon"
                                       class="form-control <?= isset($errorProfil['telepon']) ? 'is-invalid' : '' ?>"
                                       value="<?= esc(old('telepon', $user['telepon'] ?? '')) ?>"
                                       placeholder="Contoh: 08123456789"
                                       maxlength="20">
                                <?php if (isset($errorProfil['telepon'])): ?>
                                    <div class="invalid-feedback"><?= esc($errorProfil['telepon']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="field-hint">Opsional. Digunakan untuk keperluan notifikasi.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Tab: Ubah Password -->
        <div id="tab-password" class="tab-pane <?= $tabAktif !== 'password' ? 'd-none' : '' ?>">
            <div class="card border-0 shadow-sm rounded-bottom-3 rounded-top-0">
                <div class="card-body p-4">

                    <?php if (! empty($errorPassword)): ?>
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errorPassword as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('user/profil/update-password') ?>" method="post" novalidate id="formPassword">
                        <?= csrf_field() ?>

                        <!-- Password Lama -->
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       id="password_lama"
                                       name="password_lama"
                                       class="form-control <?= isset($errorPassword['password_lama']) ? 'is-invalid' : '' ?>"
                                       placeholder="Masukkan password saat ini"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password_lama">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errorPassword['password_lama'])): ?>
                                    <div class="invalid-feedback"><?= esc($errorPassword['password_lama']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       id="password_baru"
                                       name="password_baru"
                                       class="form-control <?= isset($errorPassword['password_baru']) ? 'is-invalid' : '' ?>"
                                       placeholder="Minimal 8 karakter"
                                       autocomplete="new-password"
                                       minlength="8"
                                       required>
                                <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password_baru">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errorPassword['password_baru'])): ?>
                                    <div class="invalid-feedback"><?= esc($errorPassword['password_baru']) ?></div>
                                <?php endif; ?>
                            </div>
                            <!-- Indikator kekuatan password -->
                            <div class="mt-2" id="strengthWrap" style="display:none">
                                <div class="progress" style="height:4px;border-radius:2px">
                                    <div id="strengthBar" class="progress-bar" style="width:0%;transition:width .3s,background .3s"></div>
                                </div>
                                <div id="strengthLabel" class="field-hint mt-1"></div>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-4">
                            <label for="konfirmasi" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       id="konfirmasi"
                                       name="konfirmasi"
                                       class="form-control <?= isset($errorPassword['konfirmasi']) ? 'is-invalid' : '' ?>"
                                       placeholder="Ulangi password baru"
                                       autocomplete="new-password"
                                       required>
                                <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="konfirmasi">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errorPassword['konfirmasi'])): ?>
                                    <div class="invalid-feedback"><?= esc($errorPassword['konfirmasi']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div id="matchHint" class="field-hint mt-1"></div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4" id="btnSimpanPassword">
                                <i class="bi bi-shield-lock me-1"></i>Perbarui Password
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    // ── Tab switching ──────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;

            document.querySelectorAll('[data-tab]').forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            document.querySelectorAll('.tab-pane').forEach(function (pane) {
                pane.classList.add('d-none');
            });
            document.getElementById('tab-' + target).classList.remove('d-none');
        });
    });

    // ── Toggle show/hide password ──────────────────────────────────────────────
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // ── Indikator kekuatan password ────────────────────────────────────────────
    const pwBaru      = document.getElementById('password_baru');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLbl = document.getElementById('strengthLabel');
    const strengthWrap = document.getElementById('strengthWrap');

    if (pwBaru) {
        pwBaru.addEventListener('input', function () {
            const val = this.value;
            if (!val) {
                strengthWrap.style.display = 'none';
                return;
            }
            strengthWrap.style.display = '';

            let score = 0;
            if (val.length >= 8)  score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: 20,  color: '#dc3545', label: 'Sangat lemah' },
                { pct: 40,  color: '#fd7e14', label: 'Lemah' },
                { pct: 60,  color: '#ffc107', label: 'Cukup' },
                { pct: 80,  color: '#20c997', label: 'Kuat' },
                { pct: 100, color: '#198754', label: 'Sangat kuat' },
            ];
            const lvl = levels[Math.min(score, 4)];
            strengthBar.style.width      = lvl.pct + '%';
            strengthBar.style.background = lvl.color;
            strengthLbl.textContent      = lvl.label;
            strengthLbl.style.color      = lvl.color;

            checkMatch();
        });
    }

    // ── Cek kecocokan konfirmasi password ──────────────────────────────────────
    const konfirmasi = document.getElementById('konfirmasi');
    const matchHint  = document.getElementById('matchHint');

    function checkMatch() {
        if (!konfirmasi || !pwBaru || !konfirmasi.value) {
            if (matchHint) matchHint.textContent = '';
            return;
        }
        if (konfirmasi.value === pwBaru.value) {
            matchHint.textContent = '✓ Password cocok';
            matchHint.style.color = '#198754';
            konfirmasi.classList.remove('is-invalid');
        } else {
            matchHint.textContent = '✗ Password tidak cocok';
            matchHint.style.color = '#dc3545';
        }
    }

    if (konfirmasi) {
        konfirmasi.addEventListener('input', checkMatch);
    }

    // ── Cegah submit jika password tidak cocok ─────────────────────────────────
    const formPassword = document.getElementById('formPassword');
    if (formPassword) {
        formPassword.addEventListener('submit', function (e) {
            if (pwBaru && konfirmasi && pwBaru.value !== konfirmasi.value) {
                e.preventDefault();
                konfirmasi.classList.add('is-invalid');
                if (matchHint) {
                    matchHint.textContent = '✗ Password tidak cocok';
                    matchHint.style.color = '#dc3545';
                }
                konfirmasi.focus();
            }
        });
    }
}());
</script>

<?= $this->endSection() ?>
