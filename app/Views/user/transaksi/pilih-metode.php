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

<?php
// Determine mode: single product or multi (cart)
$isCart = ! empty($produkList);
?>

<style>
/* === Payment Method Cards === */
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
.pm-logo-placeholder {
    width: 44px; height: 30px;
    border-radius: .35rem;
    display: flex; align-items: center; justify-content: center;
    font-size: .6rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.step-badge {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: #1a3a5c;
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* === Order Summary === */
.order-summary-card {
    border-radius: .75rem !important;
}
.order-item {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .75rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.order-item:last-child {
    border-bottom: none;
}
.order-item-thumb {
    width: 52px;
    height: 52px;
    border-radius: .5rem;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #eee;
}
.order-item-info {
    flex: 1;
    min-width: 0;
}
.order-item-name {
    font-size: .82rem;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.order-item-meta {
    font-size: .7rem;
    color: #6c757d;
}
.order-item-price {
    text-align: right;
    flex-shrink: 0;
    white-space: nowrap;
}
.order-item-price .price-original {
    font-size: .68rem;
    text-decoration: line-through;
    color: #999;
}
.order-item-price .price-final {
    font-size: .82rem;
    font-weight: 700;
    color: #d63031;
}
.order-item-price .price-normal {
    font-size: .82rem;
    font-weight: 600;
    color: #1a3a5c;
}

/* === Summary Totals === */
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .35rem 0;
    font-size: .82rem;
}
.summary-row.total-row {
    padding: .6rem 0;
    font-size: 1rem;
}
.summary-divider {
    border: 0;
    border-top: 1px dashed #dee2e6;
    margin: .5rem 0;
}

/* === Mobile Responsive === */
@media (max-width: 991.98px) {
    .order-summary-sticky {
        position: static !important;
    }
    /* On mobile: show summary first (order-first), then payment methods */
    .col-summary-order {
        order: -1;
    }
}
@media (max-width: 575.98px) {
    .payment-method-card {
        padding: .65rem !important;
    }
    .pm-logo-placeholder {
        width: 36px; height: 26px;
        font-size: .55rem;
    }
    .order-item-thumb {
        width: 44px;
        height: 44px;
    }
    .order-item-name {
        font-size: .78rem;
    }
    .order-item-price .price-final,
    .order-item-price .price-normal {
        font-size: .78rem;
    }
    .step-badge {
        width: 22px; height: 22px;
        font-size: .65rem;
    }
}
</style>

<div class="row g-3 g-lg-4 justify-content-center">

    <!-- Kiri: Pilih Metode -->
    <div class="col-lg-7 col-xl-7">

        <!-- Breadcrumb steps -->
        <div class="d-flex align-items-center gap-2 mb-3 mb-lg-4 text-muted small flex-wrap">
            <?php if ($isCart): ?>
                <a href="<?= base_url('user/cart') ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            <?php else: ?>
                <a href="<?= base_url('user/produk/' . ($produk['slug'] ?? $produk['id'])) ?>" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            <?php endif; ?>
            <span>·</span>
            <span class="d-flex align-items-center gap-1">
                <span class="step-badge">1</span>
                <span class="d-none d-sm-inline">Pilih Metode</span>
                <span class="d-sm-none">Metode</span>
            </span>
            <span>→</span>
            <span class="text-muted d-flex align-items-center gap-1">
                <span class="step-badge" style="background:#adb5bd">2</span> Bayar
            </span>
        </div>

        <form method="post"
              action="<?= $isCart ? base_url('user/transaksi/beli-cart') : base_url('user/transaksi/beli/' . $produk['id']) ?>"
              id="formPilihMetode">
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
                <div class="card-body p-2 p-sm-3">

                    <?php
                    $groups = [
                        'QRIS & E-Wallet' => ['gopay', 'shopeepay'],
                        'Transfer Bank'   => ['mandiri', 'bni', 'bri', 'bsi'],
                    ];
                    $bgColors = [
                        'qris'      => '#e31e24',
                        'gopay'     => '#00aed6',
                        'shopeepay' => '#ee4d2d',
                        'dana'      => '#108ee9',
                        'mandiri'   => '#003d79',
                        'bni'       => '#f68b1e',
                        'bri'       => '#005baa',
                        'bsi'       => '#c3ad04',
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
                        'bsi'       => 'BSI',
                        'permata'   => 'PMT',
                    ];
                    ?>

                    <?php foreach ($groups as $groupLabel => $keys): ?>
                        <p class="text-muted small fw-semibold mb-2 mt-3 text-uppercase" style="font-size:.68rem;letter-spacing:.05em">
                            <?= $groupLabel ?>
                        </p>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($keys as $key):
                                if (! isset($paymentMethods[$key])) continue;
                                $pm = $paymentMethods[$key];
                            ?>
                                <div class="payment-method-card card border-0 p-2 p-sm-3"
                                     data-method="<?= $key ?>"
                                     onclick="selectMethod('<?= $key ?>')">
                                    <div class="d-flex align-items-center gap-2 gap-sm-3">
                                        <div class="pm-radio"></div>
                                        <div class="pm-logo-placeholder"
                                             style="background:<?= $bgColors[$key] ?>">
                                            <?= $initials[$key] ?>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="fw-semibold" style="font-size:.85rem"><?= $pm['label'] ?></div>
                                            <div class="text-muted text-truncate" style="font-size:.72rem"><?= $pm['desc'] ?></div>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted d-none d-sm-block"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- Tombol Lanjut (desktop: di bawah metode, mobile: fixed bottom) -->
            <div class="mt-4 d-none d-lg-block">
                <button type="submit" id="btnLanjutDesktop" class="btn btn-primary w-100 py-3 fw-bold btn-lanjut" disabled>
                    <i class="bi bi-lock me-2"></i>Lanjut ke Pembayaran
                </button>
                <p class="text-center text-muted small mt-2 mb-0">
                    <i class="bi bi-shield-check me-1 text-success"></i>
                    Pembayaran diproses secara aman oleh Midtrans
                </p>
            </div>

        </form>
    </div>

    <!-- Kanan: Ringkasan Pesanan -->
    <div class="col-lg-5 col-xl-4 col-summary-order">
        <div class="card border-0 shadow-sm order-summary-card order-summary-sticky sticky-top" style="top:80px">

            <?php if ($isCart): ?>
                <!-- ===== MODE MULTI PRODUK (CART) ===== -->
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-receipt me-1 text-primary"></i> Detail Pesanan
                        </h6>
                        <span class="badge bg-primary rounded-pill"><?= count($produkList) ?> paket</span>
                    </div>
                </div>
                <div class="card-body px-3 py-2">
                    <!-- Item list -->
                    <?php foreach ($produkList as $idx => $p):
                        $thumb = ! empty($p['thumbnail'])
                            ? base_url('uploads/produk/' . $p['thumbnail'])
                            : base_url('assets/images/thumbnail/product-default.png');
                    ?>
                        <div class="order-item">
                            <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>" class="order-item-thumb">
                            <div class="order-item-info">
                                <div class="order-item-name"><?= esc($p['nama']) ?></div>
                                <div class="order-item-meta">
                                    <i class="bi bi-journal-text me-1"></i><?= $p['jumlah_tryout'] ?> sesi tryout
                                </div>
                            </div>
                            <div class="order-item-price">
                                <?php if ($p['harga_promo'] !== null): ?>
                                    <div class="price-original">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                                    <div class="price-final">Rp <?= number_format($p['harga_promo'], 0, ',', '.') ?></div>
                                <?php else: ?>
                                    <div class="price-normal">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Totals -->
                    <hr class="summary-divider">

                    <div class="summary-row">
                        <span class="text-muted">Subtotal (<?= count($produkList) ?> paket)</span>
                        <span class="fw-semibold">Rp <?= number_format($totalHarga, 0, ',', '.') ?></span>
                    </div>
                    <?php if ($totalDiskon > 0): ?>
                        <div class="summary-row text-success">
                            <span>Diskon Promo</span>
                            <span class="fw-semibold">- Rp <?= number_format($totalDiskon, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <hr class="summary-divider">

                    <div class="summary-row total-row">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary">
                            Rp <?= number_format($totalBayar, 0, ',', '.') ?>
                        </span>
                    </div>

                    <div class="alert alert-light border py-2 small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        Setiap paket akan diproses sebagai transaksi terpisah.
                    </div>

                    <!-- Metode terpilih -->
                    <div id="selected-method-info" class="mt-2 d-none">
                        <div class="alert alert-primary py-2 small mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Metode: <strong id="selected-method-label">—</strong>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- ===== MODE SINGLE PRODUK ===== -->
                <?php
                $thumb = ! empty($produk['thumbnail'])
                    ? base_url('uploads/produk/' . $produk['thumbnail'])
                    : base_url('assets/images/thumbnail/product-default.png');
                ?>
                <div class="overflow-hidden" style="border-radius:.75rem .75rem 0 0;max-height:220px">
                    <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>"
                         class="w-100" style="object-fit:cover;height:220px">
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1" style="font-size:.9rem"><?= esc($produk['nama']) ?></h6>
                    <div class="text-muted small mb-3">
                        <i class="bi bi-journal-text me-1"></i>
                        <?php
                        $db  = \Config\Database::connect();
                        $jml = $db->table('mapping_tryout')->where('produk_id', $produk['id'])->countAllResults();
                        echo $jml . ' sesi tryout';
                        ?>
                    </div>

                    <hr class="summary-divider">

                    <div class="summary-row">
                        <span class="text-muted">Harga</span>
                        <span>Rp <?= number_format($produk['harga'], 0, ',', '.') ?></span>
                    </div>
                    <?php if ($hargaPromo !== null): ?>
                        <div class="summary-row text-success">
                            <span>Diskon Promo</span>
                            <span class="fw-semibold">- Rp <?= number_format($produk['harga'] - $hargaPromo, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <hr class="summary-divider">

                    <div class="summary-row total-row">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary">
                            Rp <?= number_format($hargaPromo ?? $produk['harga'], 0, ',', '.') ?>
                        </span>
                    </div>

                    <!-- Metode terpilih -->
                    <div id="selected-method-info" class="mt-2 d-none">
                        <div class="alert alert-primary py-2 small mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Metode: <strong id="selected-method-label">—</strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Mobile: Fixed bottom button -->
<div class="d-lg-none fixed-bottom bg-white border-top shadow-lg p-3" id="mobileBottomBar">
    <button type="submit" form="formPilihMetode" id="btnLanjutMobile" class="btn btn-primary w-100 py-2 fw-bold btn-lanjut" disabled>
        <i class="bi bi-lock me-2"></i>Lanjut ke Pembayaran
    </button>
    <p class="text-center text-muted small mt-1 mb-0" style="font-size:.68rem">
        <i class="bi bi-shield-check me-1 text-success"></i>Pembayaran aman oleh Midtrans
    </p>
</div>

<!-- Spacer for mobile fixed bottom bar -->
<div class="d-lg-none" style="height:90px"></div>

<script>
const methodLabels = <?= json_encode(array_map(fn($m) => $m['label'], $paymentMethods)) ?>;

function selectMethod(key) {
    // Update hidden input
    document.getElementById('selected_payment_method').value = key;

    // Update card styles
    document.querySelectorAll('.payment-method-card').forEach(function (card) {
        card.classList.toggle('selected', card.dataset.method === key);
    });

    // Enable all submit buttons
    var label = methodLabels[key] || key;
    document.querySelectorAll('.btn-lanjut').forEach(function (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock me-2"></i>Bayar dengan ' + label;
    });

    // Update ringkasan
    var info = document.getElementById('selected-method-info');
    document.getElementById('selected-method-label').textContent = label;
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
