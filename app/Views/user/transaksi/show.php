<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-file-earmark-text"></i></div>
    <div>
        <div class="ph-title">Detail Transaksi</div>
        <div class="ph-subtitle">Informasi pembayaran dan status transaksi</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-3">
    <a href="<?= base_url('user/transaksi') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <!-- Status Banner -->
        <?php
            $statusConfig = [
                'pending'  => ['color' => 'warning',   'icon' => 'bi-hourglass-split',    'label' => 'Menunggu Pembayaran'],
                'success'  => ['color' => 'success',   'icon' => 'bi-check-circle-fill',  'label' => 'Pembayaran Berhasil'],
                'failed'   => ['color' => 'danger',    'icon' => 'bi-x-circle-fill',      'label' => 'Pembayaran Gagal'],
                'expired'  => ['color' => 'secondary', 'icon' => 'bi-clock-history',      'label' => 'Transaksi Kedaluwarsa'],
            ];
            $sc = $statusConfig[$transaksi['status']] ?? $statusConfig['pending'];
        ?>
        <div class="alert alert-<?= $sc['color'] ?> d-flex align-items-center gap-2 mb-4">
            <i class="bi <?= $sc['icon'] ?> fs-5"></i>
            <strong><?= $sc['label'] ?></strong>
        </div>

        <!-- Detail Transaksi -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Informasi Transaksi</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-0" style="width: 40%">Kode Transaksi</td>
                            <td class="fw-semibold font-monospace"><?= esc($transaksi['kode_transaksi']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Paket</td>
                            <td class="fw-semibold"><?= esc($transaksi['produk_nama']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Tanggal</td>
                            <td><?= date('d M Y H:i', strtotime($transaksi['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Harga Asli</td>
                            <td>Rp <?= number_format($transaksi['harga_asli'], 0, ',', '.') ?></td>
                        </tr>
                        <?php if ($transaksi['diskon'] > 0): ?>
                        <tr>
                            <td class="text-muted ps-0">Diskon</td>
                            <td class="text-success">- Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="border-top">
                            <td class="text-muted ps-0 fw-bold">Total Dibayar</td>
                            <td class="fw-bold fs-5 text-primary">Rp <?= number_format($transaksi['harga_bayar'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Status</td>
                            <td>
                                <span class="badge bg-<?= $sc['color'] ?>-subtle text-<?= $sc['color'] ?> border border-<?= $sc['color'] ?>-subtle">
                                    <?= $sc['label'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (! empty($transaksi['payment_method'])): ?>
                        <?php $pm = \App\Services\MidtransService::PAYMENT_METHODS[$transaksi['payment_method']] ?? null; ?>
                        <tr>
                            <td class="text-muted ps-0">Metode Bayar</td>
                            <td>
                                <?php if ($pm): ?>
                                    <span class="badge rounded-pill px-2 py-1"
                                          style="background:<?= $pm['color'] ?>;font-size:.78rem">
                                        <?= esc($pm['label']) ?>
                                    </span>
                                <?php else: ?>
                                    <?= esc($transaksi['payment_method']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Lanjut Bayar (hanya jika pending dan ada snap_token) -->
        <?php if ($transaksi['status'] === 'pending' && !empty($transaksi['snap_token'])): ?>
            <div class="card border-0 shadow-sm border-warning">
                <div class="card-body text-center py-4">
                    <?php if (! empty($transaksi['payment_method'])): ?>
                        <?php
                        $pm = \App\Services\MidtransService::PAYMENT_METHODS[$transaksi['payment_method']] ?? null;
                        ?>
                        <?php if ($pm): ?>
                            <div class="mb-3">
                                <span class="badge rounded-pill px-3 py-2" style="background:<?= $pm['color'] ?>;font-size:.85rem">
                                    <?= esc($pm['label']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="text-muted mb-3">Transaksi Anda masih menunggu pembayaran. Klik tombol di bawah untuk melanjutkan.</p>
                    <button id="pay-button" class="btn btn-warning btn-lg px-5 fw-bold">
                        <i class="bi bi-credit-card me-2"></i>Bayar Sekarang
                    </button>
                </div>
            </div>

            <!-- Midtrans Snap.js — sandbox vs production -->
            <script src="<?= $isProduction
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
                    data-client-key="<?= esc($clientKey) ?>"></script>
            <script>
            (function () {
                const cekStatusUrl = '<?= base_url('user/transaksi/' . $transaksi['id'] . '/cek-status') ?>';

                function cekDanRedirect() {
                    fetch(cekStatusUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(() => window.location.reload());
                }

                document.getElementById('pay-button').addEventListener('click', function () {
                    snap.pay('<?= esc($transaksi['snap_token']) ?>', {
                        onSuccess: function () { cekDanRedirect(); },
                        onPending: function () { cekDanRedirect(); },
                        onError:   function () { alert('Pembayaran gagal. Silakan coba lagi.'); },
                        onClose:   function () { /* user tutup popup */ }
                    });
                });
            }());
            </script>
        <?php elseif ($transaksi['status'] === 'success'): ?>
            <div class="text-center">
                <a href="<?= base_url('user/dashboard') ?>" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-play-circle me-2"></i>Mulai Tryout
                </a>
            </div>
        <?php elseif (in_array($transaksi['status'], ['failed', 'expired'])): ?>
            <div class="text-center">
                <a href="<?= base_url('user/transaksi/pilih-metode/' . $transaksi['produk_id']) ?>"
                   class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-arrow-repeat me-2"></i>Coba Beli Lagi
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
