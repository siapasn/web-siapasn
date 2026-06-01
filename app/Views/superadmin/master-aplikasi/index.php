<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="ph-icon"><i class="bi bi-gear-wide-connected"></i></div>
        <div>
            <div class="ph-title">Master Aplikasi</div>
            <div class="ph-subtitle">Konfigurasi pengaturan sistem aplikasi</div>
            <div class="ph-accent-line"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Kumpulkan semua error validasi dari session
$errors = session()->getFlashdata('errors') ?? [];
?>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Terdapat kesalahan pada input:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $field => $msg): ?>
                <li><?= esc($msg) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<form action="<?= base_url('superadmin/master-aplikasi/save') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-0" id="configTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-umum" data-bs-toggle="tab"
                    data-bs-target="#panel-umum" type="button" role="tab"
                    aria-controls="panel-umum" aria-selected="true">
                <i class="bi bi-info-circle me-1"></i> Informasi Umum
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-midtrans" data-bs-toggle="tab"
                    data-bs-target="#panel-midtrans" type="button" role="tab"
                    aria-controls="panel-midtrans" aria-selected="false">
                <i class="bi bi-credit-card me-1"></i> Midtrans
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-email" data-bs-toggle="tab"
                    data-bs-target="#panel-email" type="button" role="tab"
                    aria-controls="panel-email" aria-selected="false">
                <i class="bi bi-envelope me-1"></i> Email SMTP
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sesi" data-bs-toggle="tab"
                    data-bs-target="#panel-sesi" type="button" role="tab"
                    aria-controls="panel-sesi" aria-selected="false">
                <i class="bi bi-shield-lock me-1"></i> Kebijakan Sesi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-redis" data-bs-toggle="tab"
                    data-bs-target="#panel-redis" type="button" role="tab"
                    aria-controls="panel-redis" aria-selected="false">
                <i class="bi bi-database me-1"></i> Redis
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-launching" data-bs-toggle="tab"
                    data-bs-target="#panel-launching" type="button" role="tab"
                    aria-controls="panel-launching" aria-selected="false">
                <i class="bi bi-rocket-takeoff me-1"></i> Launching
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4 mb-4" id="configTabContent">

        <!-- ============================================================ -->
        <!-- Tab 1: Informasi Umum -->
        <!-- ============================================================ -->
        <div class="tab-pane fade show active" id="panel-umum" role="tabpanel" aria-labelledby="tab-umum">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-info-circle me-1"></i> Informasi Umum Aplikasi
            </h6>

            <!-- Nama Aplikasi -->
            <div class="mb-3">
                <label for="app_name" class="form-label">
                    Nama Aplikasi <span class="text-danger">*</span>
                </label>
                <input type="text"
                       id="app_name"
                       name="app_name"
                       class="form-control <?= isset($errors['app_name']) ? 'is-invalid' : '' ?>"
                       value="<?= esc(old('app_name', $configs['app_name'] ?? '')) ?>"
                       placeholder="Contoh: SiapASN Simulation Center"
                       required
                       minlength="3">
                <?php if (isset($errors['app_name'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['app_name']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Deskripsi Aplikasi -->
            <div class="mb-3">
                <label for="app_description" class="form-label">Deskripsi Aplikasi</label>
                <textarea id="app_description"
                          name="app_description"
                          class="form-control <?= isset($errors['app_description']) ? 'is-invalid' : '' ?>"
                          rows="3"
                          placeholder="Deskripsi singkat tentang aplikasi..."><?= esc(old('app_description', $configs['app_description'] ?? '')) ?></textarea>
                <?php if (isset($errors['app_description'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['app_description']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Logo Aplikasi -->
            <div class="mb-3">
                <label for="app_logo" class="form-label">Logo Aplikasi</label>
                <?php if (! empty($configs['app_logo'])): ?>
                    <div class="mb-2">
                        <img src="<?= base_url(esc($configs['app_logo'])) ?>"
                             alt="Logo Aplikasi"
                             class="img-thumbnail"
                             style="max-height: 80px;">
                        <small class="text-muted d-block mt-1">Logo saat ini. Upload file baru untuk mengganti.</small>
                    </div>
                <?php endif; ?>
                <input type="file"
                       id="app_logo"
                       name="app_logo"
                       class="form-control <?= isset($errors['app_logo']) ? 'is-invalid' : '' ?>"
                       accept="image/png,image/jpeg,image/gif,image/svg+xml">
                <div class="form-text">Format: PNG, JPG, GIF, SVG. Maks. 2 MB. Kosongkan jika tidak ingin mengubah logo.</div>
                <?php if (isset($errors['app_logo'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['app_logo']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Tab 2: Midtrans -->
        <!-- ============================================================ -->
        <div class="tab-pane fade" id="panel-midtrans" role="tabpanel" aria-labelledby="tab-midtrans">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-credit-card me-1"></i> Konfigurasi Midtrans
            </h6>

            <!-- Midtrans Server Key -->
            <div class="mb-3">
                <label for="midtrans_server_key" class="form-label">
                    Server Key <span class="text-danger">*</span>
                </label>
                <input type="text"
                       id="midtrans_server_key"
                       name="midtrans_server_key"
                       class="form-control font-monospace <?= isset($errors['midtrans_server_key']) ? 'is-invalid' : '' ?>"
                       value="<?= esc(old('midtrans_server_key', $configs['midtrans_server_key'] ?? '')) ?>"
                       placeholder="SB-Mid-server-xxxxxxxxxxxx"
                       required>
                <div class="form-text">Dapat ditemukan di Dashboard Midtrans → Settings → Access Keys.</div>
                <?php if (isset($errors['midtrans_server_key'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['midtrans_server_key']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Midtrans Client Key -->
            <div class="mb-3">
                <label for="midtrans_client_key" class="form-label">
                    Client Key <span class="text-danger">*</span>
                </label>
                <input type="text"
                       id="midtrans_client_key"
                       name="midtrans_client_key"
                       class="form-control font-monospace <?= isset($errors['midtrans_client_key']) ? 'is-invalid' : '' ?>"
                       value="<?= esc(old('midtrans_client_key', $configs['midtrans_client_key'] ?? '')) ?>"
                       placeholder="SB-Mid-client-xxxxxxxxxxxx"
                       required>
                <?php if (isset($errors['midtrans_client_key'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['midtrans_client_key']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Midtrans Environment -->
            <div class="mb-3">
                <label for="midtrans_environment" class="form-label">
                    Environment <span class="text-danger">*</span>
                </label>
                <select id="midtrans_environment"
                        name="midtrans_environment"
                        class="form-select <?= isset($errors['midtrans_environment']) ? 'is-invalid' : '' ?>"
                        required>
                    <?php
                    $currentEnv = old('midtrans_environment', $configs['midtrans_environment'] ?? 'sandbox');
                    ?>
                    <option value="sandbox"    <?= $currentEnv === 'sandbox'    ? 'selected' : '' ?>>Sandbox (Testing)</option>
                    <option value="production" <?= $currentEnv === 'production' ? 'selected' : '' ?>>Production (Live)</option>
                </select>
                <div class="form-text">Gunakan <strong>Sandbox</strong> untuk pengujian dan <strong>Production</strong> untuk transaksi nyata.</div>
                <?php if (isset($errors['midtrans_environment'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['midtrans_environment']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Midtrans URL -->
            <div class="mb-3">
                <label for="midtrans_url" class="form-label">Midtrans API URL</label>
                <input type="url"
                       id="midtrans_url"
                       name="midtrans_url"
                       class="form-control font-monospace <?= isset($errors['midtrans_url']) ? 'is-invalid' : '' ?>"
                       value="<?= esc(old('midtrans_url', $configs['midtrans_url'] ?? 'https://api.sandbox.midtrans.com/v2/charge')) ?>"
                       placeholder="https://api.sandbox.midtrans.com/v2/charge">
                <div class="form-text">
                    Sandbox: <code>https://api.sandbox.midtrans.com/v2/charge</code><br>
                    Production: <code>https://api.midtrans.com/v2/charge</code>
                </div>
                <?php if (isset($errors['midtrans_url'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['midtrans_url']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Midtrans Merchant ID -->
            <div class="mb-3">
                <label for="midtrans_merchant_id" class="form-label">Merchant ID</label>
                <input type="text"
                       id="midtrans_merchant_id"
                       name="midtrans_merchant_id"
                       class="form-control font-monospace <?= isset($errors['midtrans_merchant_id']) ? 'is-invalid' : '' ?>"
                       value="<?= esc(old('midtrans_merchant_id', $configs['midtrans_merchant_id'] ?? '')) ?>"
                       placeholder="G406304292">
                <div class="form-text">Merchant ID dari dashboard Midtrans.</div>
                <?php if (isset($errors['midtrans_merchant_id'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['midtrans_merchant_id']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Tab 3: Email SMTP -->
        <!-- ============================================================ -->
        <div class="tab-pane fade" id="panel-email" role="tabpanel" aria-labelledby="tab-email">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-envelope me-1"></i> Konfigurasi Email SMTP
            </h6>

            <div class="row g-3">
                <!-- Email Host -->
                <div class="col-12 col-md-8">
                    <label for="email_host" class="form-label">
                        SMTP Host <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="email_host"
                           name="email_host"
                           class="form-control <?= isset($errors['email_host']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('email_host', $configs['email_host'] ?? '')) ?>"
                           placeholder="smtp.gmail.com"
                           required>
                    <?php if (isset($errors['email_host'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_host']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Port -->
                <div class="col-12 col-md-4">
                    <label for="email_port" class="form-label">
                        Port <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           id="email_port"
                           name="email_port"
                           class="form-control <?= isset($errors['email_port']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('email_port', $configs['email_port'] ?? '587')) ?>"
                           placeholder="587"
                           min="1"
                           max="65535"
                           required>
                    <?php if (isset($errors['email_port'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_port']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Username -->
                <div class="col-12 col-md-6">
                    <label for="email_username" class="form-label">
                        Username <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="email_username"
                           name="email_username"
                           class="form-control <?= isset($errors['email_username']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('email_username', $configs['email_username'] ?? '')) ?>"
                           placeholder="noreply@example.com"
                           required>
                    <?php if (isset($errors['email_username'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_username']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Password -->
                <div class="col-12 col-md-6">
                    <label for="email_password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password"
                               id="email_password"
                               name="email_password"
                               class="form-control <?= isset($errors['email_password']) ? 'is-invalid' : '' ?>"
                               placeholder="Kosongkan jika tidak ingin mengubah"
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary" type="button" id="toggleEmailPassword"
                                title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                        <?php if (isset($errors['email_password'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['email_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-text">Kosongkan jika tidak ingin mengubah password yang tersimpan.</div>
                </div>

                <!-- Email Encryption -->
                <div class="col-12 col-md-4">
                    <label for="email_encryption" class="form-label">
                        Enkripsi <span class="text-danger">*</span>
                    </label>
                    <select id="email_encryption"
                            name="email_encryption"
                            class="form-select <?= isset($errors['email_encryption']) ? 'is-invalid' : '' ?>"
                            required>
                        <?php
                        $currentEnc = old('email_encryption', $configs['email_encryption'] ?? 'tls');
                        ?>
                        <option value="tls"  <?= $currentEnc === 'tls'  ? 'selected' : '' ?>>TLS (Port 587)</option>
                        <option value="ssl"  <?= $currentEnc === 'ssl'  ? 'selected' : '' ?>>SSL (Port 465)</option>
                        <option value="none" <?= $currentEnc === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                    </select>
                    <?php if (isset($errors['email_encryption'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_encryption']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email From -->
                <div class="col-12 col-md-4">
                    <label for="email_from" class="form-label">
                        Alamat Pengirim <span class="text-danger">*</span>
                    </label>
                    <input type="email"
                           id="email_from"
                           name="email_from"
                           class="form-control <?= isset($errors['email_from']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('email_from', $configs['email_from'] ?? '')) ?>"
                           placeholder="noreply@example.com"
                           required>
                    <?php if (isset($errors['email_from'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_from']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email From Name -->
                <div class="col-12 col-md-4">
                    <label for="email_from_name" class="form-label">
                        Nama Pengirim <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="email_from_name"
                           name="email_from_name"
                           class="form-control <?= isset($errors['email_from_name']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('email_from_name', $configs['email_from_name'] ?? '')) ?>"
                           placeholder="SiapASN Simulation Center"
                           required>
                    <?php if (isset($errors['email_from_name'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email_from_name']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Tab 4: Kebijakan Sesi -->
        <!-- ============================================================ -->
        <div class="tab-pane fade" id="panel-sesi" role="tabpanel" aria-labelledby="tab-sesi">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-shield-lock me-1"></i> Kebijakan Sesi
            </h6>

            <!-- Session Timeout -->
            <div class="mb-3" style="max-width: 400px;">
                <label for="session_timeout" class="form-label">
                    Session Timeout (menit) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number"
                           id="session_timeout"
                           name="session_timeout"
                           class="form-control <?= isset($errors['session_timeout']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('session_timeout', $configs['session_timeout'] ?? '60')) ?>"
                           min="5"
                           max="1440"
                           required>
                    <span class="input-group-text">menit</span>
                    <?php if (isset($errors['session_timeout'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['session_timeout']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-text">
                    Durasi tidak aktif sebelum sesi pengguna berakhir otomatis.
                    Minimal <strong>5 menit</strong>, maksimal <strong>1440 menit</strong> (24 jam).
                </div>
            </div>

            <div class="alert alert-info d-flex align-items-start gap-2 mt-3" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Catatan:</strong> Perubahan session timeout akan berlaku pada sesi login berikutnya.
                    Sesi yang sedang aktif tidak akan terpengaruh secara langsung.
                </div>
            </div>

            <hr class="my-4">

            <!-- Masa Aktif Produk -->
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-clock-history me-1"></i> Masa Aktif Produk
            </h6>

            <div class="mb-3" style="max-width: 400px;">
                <label for="produk_expired_days" class="form-label">
                    Masa Aktif Produk (hari) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number"
                           id="produk_expired_days"
                           name="produk_expired_days"
                           class="form-control <?= isset($errors['produk_expired_days']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('produk_expired_days', $configs['produk_expired_days'] ?? '365')) ?>"
                           min="1"
                           max="3650"
                           required>
                    <span class="input-group-text">hari</span>
                    <?php if (isset($errors['produk_expired_days'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['produk_expired_days']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-text">
                    Durasi akses produk setelah pembelian berhasil. Default: <strong>365 hari</strong> (1 tahun).
                    Setelah masa aktif habis, user tidak bisa memulai tryout baru tetapi masih bisa melihat riwayat dan pembahasan.
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Tab 5: Redis -->
        <!-- ============================================================ -->
        <div class="tab-pane fade" id="panel-redis" role="tabpanel" aria-labelledby="tab-redis">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-database me-1"></i> Konfigurasi Redis (Keranjang Belanja)
            </h6>

            <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Environment Production:</strong> menggunakan <code>Unix Socket</code> (lebih cepat).<br>
                    <strong>Environment lainnya (development/staging):</strong> menggunakan <code>TCP Host:Port</code>.
                </div>
            </div>

            <div class="row g-3">
                <!-- Unix Socket (Production) -->
                <div class="col-12">
                    <label for="redis_socket" class="form-label">
                        Unix Socket Path
                        <span class="badge bg-danger ms-1" style="font-size:.65rem">Production only</span>
                    </label>
                    <input type="text"
                           id="redis_socket"
                           name="redis_socket"
                           class="form-control font-monospace <?= isset($errors['redis_socket']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('redis_socket', $configs['redis_socket'] ?? '')) ?>"
                           placeholder="/home/user/tmp/redis.sock">
                    <div class="form-text">Path ke Unix socket Redis. Digunakan saat <code>CI_ENVIRONMENT = production</code>.</div>
                    <?php if (isset($errors['redis_socket'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['redis_socket']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Host -->
                <div class="col-12 col-md-8">
                    <label for="redis_host" class="form-label">
                        Host
                        <span class="badge bg-secondary ms-1" style="font-size:.65rem">Non-production</span>
                    </label>
                    <input type="text"
                           id="redis_host"
                           name="redis_host"
                           class="form-control font-monospace <?= isset($errors['redis_host']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('redis_host', $configs['redis_host'] ?? '127.0.0.1')) ?>"
                           placeholder="127.0.0.1">
                    <?php if (isset($errors['redis_host'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['redis_host']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Port -->
                <div class="col-12 col-md-4">
                    <label for="redis_port" class="form-label">Port</label>
                    <input type="number"
                           id="redis_port"
                           name="redis_port"
                           class="form-control <?= isset($errors['redis_port']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('redis_port', $configs['redis_port'] ?? '6379')) ?>"
                           placeholder="6379" min="1" max="65535">
                    <?php if (isset($errors['redis_port'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['redis_port']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="col-12 col-md-6">
                    <label for="redis_password" class="form-label">Password</label>
                    <input type="password"
                           id="redis_password"
                           name="redis_password"
                           class="form-control <?= isset($errors['redis_password']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('redis_password', $configs['redis_password'] ?? '')) ?>"
                           placeholder="Kosongkan jika tidak ada password"
                           autocomplete="new-password">
                    <div class="form-text">Kosongkan jika Redis tidak menggunakan password.</div>
                    <?php if (isset($errors['redis_password'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['redis_password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- DB Index -->
                <div class="col-12 col-md-6">
                    <label for="redis_db" class="form-label">Database Index</label>
                    <input type="number"
                           id="redis_db"
                           name="redis_db"
                           class="form-control <?= isset($errors['redis_db']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('redis_db', $configs['redis_db'] ?? '0')) ?>"
                           placeholder="0" min="0" max="15">
                    <div class="form-text">Nomor database Redis (0–15). Default: 0.</div>
                    <?php if (isset($errors['redis_db'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['redis_db']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Tab 6: Launching -->
        <!-- ============================================================ -->
        <div class="tab-pane fade" id="panel-launching" role="tabpanel" aria-labelledby="tab-launching">
            <h6 class="fw-semibold mb-3 text-primary">
                <i class="bi bi-rocket-takeoff me-1"></i> Pengaturan Launching Pembelian
            </h6>

            <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                <div>
                    Atur tanggal dan waktu mulai pembelian produk tryout berbayar.
                    Sebelum waktu ini, tombol <strong>Keranjang</strong> dan <strong>Beli Sekarang</strong> akan dinonaktifkan
                    dan user akan melihat informasi bahwa pembelian segera dibuka.
                    Kosongkan untuk menonaktifkan fitur ini (pembelian selalu aktif).
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="launch_date" class="form-label">
                        Tanggal & Waktu Launching
                    </label>
                    <input type="datetime-local"
                           id="launch_date"
                           name="launch_date"
                           class="form-control"
                           value="<?= esc(old('launch_date', isset($configs['launch_date']) && $configs['launch_date']
                               ? date('Y-m-d\TH:i', strtotime($configs['launch_date']))
                               : '')) ?>">
                    <div class="form-text">
                        Pembelian akan aktif mulai tanggal dan waktu ini.
                        <strong>Kosongkan</strong> jika pembelian sudah aktif atau tidak ingin membatasi.
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Status Saat Ini</label>
                    <div class="p-3 rounded border">
                        <?php
                        $launchDate = $configs['launch_date'] ?? '';
                        if (empty($launchDate)) {
                            echo '<span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Pembelian Aktif</span>';
                            echo '<div class="text-muted small mt-1">Tidak ada pembatasan waktu launching.</div>';
                        } elseif (strtotime($launchDate) > time()) {
                            echo '<span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock me-1"></i>Belum Launching</span>';
                            echo '<div class="text-muted small mt-1">Pembelian akan aktif pada: <strong>' . date('d M Y, H:i', strtotime($launchDate)) . ' WIB</strong></div>';
                        } else {
                            echo '<span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Sudah Launching</span>';
                            echo '<div class="text-muted small mt-1">Pembelian aktif sejak: <strong>' . date('d M Y, H:i', strtotime($launchDate)) . ' WIB</strong></div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="col-12">
                    <label for="launch_message" class="form-label">Pesan untuk User (sebelum launching)</label>
                    <textarea id="launch_message"
                              name="launch_message"
                              class="form-control"
                              rows="3"
                              placeholder="Contoh: Pembelian paket tryout akan segera dibuka. Pantau terus halaman ini!"><?= esc(old('launch_message', $configs['launch_message'] ?? 'Pembelian paket tryout akan segera dibuka. Pantau terus halaman ini!')) ?></textarea>
                    <div class="form-text">Pesan ini ditampilkan di halaman katalog produk sebelum waktu launching tiba.</div>
                </div>
            </div>
        </div>

    </div><!-- /.tab-content -->

    <!-- Submit Button -->    <div class="d-flex justify-content-end gap-2">
        <a href="<?= base_url('superadmin/master-aplikasi') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Konfigurasi
        </button>
    </div>

</form>

<script>
(function () {
    'use strict';

    // Toggle password visibility
    const toggleBtn = document.getElementById('toggleEmailPassword');
    const passwordInput = document.getElementById('email_password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    // Auto-switch to the tab that contains validation errors
    <?php if (! empty($errors)): ?>
    (function () {
        const errorFields = <?= json_encode(array_keys($errors)) ?>;
        const tabMap = {
            'app_name':             'tab-umum',
            'app_description':      'tab-umum',
            'app_logo':             'tab-umum',
            'midtrans_server_key':  'tab-midtrans',
            'midtrans_client_key':  'tab-midtrans',
            'midtrans_environment': 'tab-midtrans',
            'midtrans_url':         'tab-midtrans',
            'midtrans_merchant_id': 'tab-midtrans',
            'email_host':           'tab-email',
            'email_port':           'tab-email',
            'email_username':       'tab-email',
            'email_password':       'tab-email',
            'email_encryption':     'tab-email',
            'email_from':           'tab-email',
            'email_from_name':      'tab-email',
            'session_timeout':      'tab-sesi',
            'produk_expired_days':  'tab-sesi',
            'redis_socket':         'tab-redis',
            'redis_host':           'tab-redis',
            'redis_port':           'tab-redis',
            'redis_password':       'tab-redis',
            'redis_db':             'tab-redis',
        };

        for (const field of errorFields) {
            const tabId = tabMap[field];
            if (tabId) {
                const tabEl = document.getElementById(tabId);
                if (tabEl) {
                    const tab = new bootstrap.Tab(tabEl);
                    tab.show();
                    break;
                }
            }
        }
    }());
    <?php endif; ?>

}());
</script>

<?= $this->endSection() ?>
