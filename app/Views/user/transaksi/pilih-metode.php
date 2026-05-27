<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-credit-card-2-front"></i></div>
    <div>
        <div class="ph-title">Pilih Metode Pembayaran</div>
        <div class="ph-subtitle">Pilih cara pembayaran yang paling nyaman untuk Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.payment-method-card {
    cursor: pointer;
    border: 2px solid #e9ecef !important;
    border-radius: .75rem !important;
    transition: border-color .15s, box-shadow .15s, transform .12s;
    user-select: none;
}
.payment-method-card:hover {
    border-color: #1a3a5c !important;
    box-shadow: 0 4px 16px rgba(26,58,92,.1) !important;
    transform: translateY(-2px);
}
.payment-method-card.selected {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26,58,92,.15) !important;
    background: #f0f5ff;
}
.payment-method-card .pm-radio {
    width: 20px; height: 20px;
    border: 2px solid #adb5bd;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: border-color .15s, background .15s;
}
.payment-method-card.selected .pm-radio {
    border-color: #1a3a5c;
    background: #1a3a5c;
}
.payment-method-card.selected .pm-radio::after {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #fff;
}
.pm-logo {
    width: 48px; height: 32px;
    object-fit: contain;
    flex-shrink: 0;
}
.pm-logo-placeholder {
    width: 48px; height: 32px;
    border-radius: .35rem;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.step-badge {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #1a3a5c;
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>

<div class="row g-4 justify-content-center">

    <!-- Kiri: Pilih Metode -->
    <div class="col-lg-7">

        <!-- Breadcrumb steps -->
        <div class="d-flex align-items-center gap-2 mb-4 text-muted small">
            <a href="<?= base_url('user/produk/' . $produk['id']) ?>" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <span>·</span>
            <span class="d-flex align-items-center gap-1">
                <span class="step-badge">1</span> Pilih Metode
            </span>
            <span>→</span>
            <span class="text-muted d-flex align-items-center gap-1">
                <span class="step-badge" style="background:#adb5bd">2</span> Bayar
            </span>
        </div>

        <form method="post" action="<?= base_url('user/transaksi/beli/' . $produk['id']) ?>" id="formPilihMetode">
            <?= csrf_field() ?>
            <input type="hidden" name="payment_method" id="selected_payment_method" value="">

            <!-- Voucher -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <label class="form-label small fw-semibold mb-2">
                        <i class="bi bi-ticket-perforated me-1 text-primary"></i>Kode Voucher (opsional)
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="voucher_code"
                               placeholder="Masukkan kode voucher" autocomplete="off">
                        <span class="input-group-text bg-white"><i class="bi bi-tag text-muted"></i></span>
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-wallet2 me-2 text-primary"></i>Metode Pembayaran
                    </h6>
                </div>
                <div class="card-body p-3">

                    <?php
                    $groups = [
                        'QRIS & E-Wallet' => ['qris', 'gopay', 'shopeepay', 'dana'],
                        'Transfer Bank'   => ['mandiri', 'bni', 'bri', 'permata'],
                    ];
                    $bgColors = [
                        'qris'      => '#e31e24',
                        'gopay'     => '#00aed6',
                        'shopeepay' => '#ee4d2d',
                        'dana'      => '#108ee9',
                        'mandiri'   => '#003d79',
                        'bni'       => '#f68b1e',
                        'bri'       => '#005baa',
                        'permata'   => '#e31e24',
                    ];
                    $initials = [
                        'qris'      => 'QR',
                        'gopay'     => 'GP',
                        'shopeepay' => 'SP',
                        'dana'      => 'DN',
                        'mandiri'   => 'MDR',
                        'bni'       => 'BNI',
                        'bri'       => 'BRI',
                        'permata'   => 'PMT',
                    ];
                    ?>

                    <?php foreach ($groups as $groupLabel => $keys): ?>
                        <p class="text-muted small fw-semibold mb-2 mt-3 text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                            <?= $groupLabel ?>
                        </p>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($keys as $key):
                                $pm = $paymentMethods[$key];
                            ?>
                                <div class="payment-method-card card border-0 p-3"
                                     data-method="<?= $key ?>"
                                     onclick="selectMethod('<?= $key ?>')">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Radio -->
                                        <div class="pm-radio"></div>

                                        <!-- Logo placeholder -->
                                        <div class="pm-logo-placeholder"
                                             style="background:<?= $bgColors[$key] ?>">
                                            <?= $initials[$key] ?>
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:.9rem"><?= $pm['label'] ?></div>
                                            <div class="text-muted" style="font-size:.78rem"><?= $pm['desc'] ?></div>
                                        </div>

                                        <!-- Arrow -->
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- Tombol Lanjut -->
            <div class="mt-4">
                <button type="submit" id="btnLanjut" class="btn btn-primary w-100 py-3 fw-bold" disabled>
                    <i class="bi bi-lock me-2"></i>Lanjut ke Pembayaran
                </button>
                <p class="text-center text-muted small mt-2">
                    <i class="bi bi-shield-check me-1 text-success"></i>
                    Pembayaran diproses secara aman oleh Midtrans
                </p>
            </div>

        </form>
    </div>

    <!-- Kanan: Ringkasan -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px">
            <?php
            $thumb = ! empty($produk['thumbnail'])
                ? base_url('uploads/produk/' . $produk['thumbnail'])
                : base_url('assets/images/thumbnail/product-default.png');
            ?>
            <div style="aspect-ratio:1/1;overflow:hidden;border-radius:.75rem .75rem 0 0">
                <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>"
                     class="w-100 h-100" style="object-fit:cover">
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-1"><?= esc($produk['nama']) ?></h6>
                <div class="text-muted small mb-3">
                    <i class="bi bi-journal-text me-1"></i>
                    <?php
                    $db  = \Config\Database::connect();
                    $jml = $db->table('mapping_tryout')->where('produk_id', $produk['id'])->countAllResults();
                    echo $jml . ' sesi tryout';
                    ?>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Harga</span>
                    <span>Rp <?= number_format($produk['harga'], 0, ',', '.') ?></span>
                </div>
                <?php if ($hargaPromo !== null): ?>
                    <div class="d-flex justify-content-between mb-1 small text-success">
                        <span>Diskon Promo</span>
                        <span>- Rp <?= number_format($produk['harga'] - $hargaPromo, 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>

                <hr>

                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-primary fs-5">
                        Rp <?= number_format($hargaPromo ?? $produk['harga'], 0, ',', '.') ?>
                    </span>
                </div>

                <!-- Metode terpilih -->
                <div id="selected-method-info" class="mt-3 d-none">
                    <div class="alert alert-primary py-2 small mb-0">
                        <i class="bi bi-check-circle me-1"></i>
                        Metode: <strong id="selected-method-label">—</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const methodLabels = <?= json_encode(array_map(fn($m) => $m['label'], $paymentMethods)) ?>;

function selectMethod(key) {
    // Update hidden input
    document.getElementById('selected_payment_method').value = key;

    // Update card styles
    document.querySelectorAll('.payment-method-card').forEach(function (card) {
        card.classList.toggle('selected', card.dataset.method === key);
    });

    // Enable button
    document.getElementById('btnLanjut').disabled = false;
    document.getElementById('btnLanjut').innerHTML =
        '<i class="bi bi-lock me-2"></i>Bayar dengan ' + (methodLabels[key] || key);

    // Update ringkasan
    const info = document.getElementById('selected-method-info');
    document.getElementById('selected-method-label').textContent = methodLabels[key] || key;
    info.classList.remove('d-none');
}

// Validasi sebelum submit
document.getElementById('formPilihMetode').addEventListener('submit', function (e) {
    if (! document.getElementById('selected_payment_method').value) {
        e.preventDefault();
        alert('Pilih metode pembayaran terlebih dahulu.');
    }
});
</script>

<?= $this->endSection() ?>
