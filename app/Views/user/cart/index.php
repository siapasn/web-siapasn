<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-cart3"></i></div>
    <div>
        <div class="ph-title">Keranjang Belanja</div>
        <div class="ph-subtitle"><?= count($produkList) ?> paket dipilih</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= base_url('user/produk') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Lanjut Belanja
    </a>
</div>

<?php if (session()->getFlashdata('info')): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?= esc(session()->getFlashdata('info')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($produkList)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-cart-x fs-1 d-block mb-3 text-secondary"></i>
            <h5 class="fw-semibold">Keranjang Kosong</h5>
            <p class="mb-4">Belum ada paket yang ditambahkan ke keranjang.</p>
            <a href="<?= base_url('user/produk') ?>" class="btn btn-primary">
                <i class="bi bi-shop me-1"></i>Lihat Katalog Paket
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <!-- Daftar Item -->
        <div class="col-lg-8">
            <?php foreach ($produkList as $p): ?>
                <?php
                $thumb = ! empty($p['thumbnail'])
                    ? base_url('uploads/produk/' . $p['thumbnail'])
                    : base_url('assets/images/thumbnail/product-default.png');
                $hargaTampil = $p['harga_promo'] ?? $p['harga'];
                ?>
                <div class="card border-0 shadow-sm mb-3" id="cart-item-<?= $p['id'] ?>"
                     data-harga="<?= (int)($p['harga_promo'] ?? $p['harga']) ?>">
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-center">
                            <!-- Thumbnail -->
                            <div style="width:80px;height:80px;flex-shrink:0;overflow:hidden;border-radius:.5rem;">
                                <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>"
                                     class="w-100 h-100" style="object-fit:cover;">
                            </div>

                            <!-- Info -->
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1"><?= esc($p['nama']) ?></h6>
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-journal-text me-1"></i>
                                    <?php
                                    $db = \Config\Database::connect();
                                    $jml = $db->table('mapping_tryout')->where('produk_id', $p['id'])->countAllResults();
                                    echo $jml . ' sesi tryout';
                                    ?>
                                </div>
                                <?php if ($p['sudah_beli']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i>Sudah Dimiliki
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Harga + Hapus -->
                            <div class="text-end flex-shrink-0">
                                <?php if ($p['harga_promo'] !== null): ?>
                                    <div class="text-decoration-line-through text-muted small">
                                        Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                    </div>
                                    <div class="fw-bold text-danger">
                                        Rp <?= number_format($p['harga_promo'], 0, ',', '.') ?>
                                    </div>
                                <?php else: ?>
                                    <div class="fw-bold text-primary">
                                        Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger mt-2 btn-remove-cart"
                                        data-produk-id="<?= $p['id'] ?>" title="Hapus dari keranjang">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ringkasan -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white border-bottom fw-semibold">
                    <i class="bi bi-receipt me-1"></i>Ringkasan Pesanan
                </div>
                <div class="card-body" id="ringkasan-body">
                    <?php foreach ($produkList as $p): ?>
                        <div class="d-flex justify-content-between small mb-2 ringkasan-item" data-produk-id="<?= $p['id'] ?>">
                            <span class="text-truncate me-2" style="max-width:160px"><?= esc($p['nama']) ?></span>
                            <span class="fw-semibold text-nowrap">
                                Rp <?= number_format($p['harga_promo'] ?? $p['harga'], 0, ',', '.') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-4">
                        <span>Total</span>
                        <span class="text-primary fs-5" id="ringkasan-total">Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>

                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Setiap paket akan diproses sebagai transaksi terpisah.
                    </div>

                    <?php
                    // Cek apakah ada item yang belum dibeli
                    $adaYangBelumBeli = false;
                    foreach ($produkList as $p) {
                        if (! $p['sudah_beli']) { $adaYangBelumBeli = true; break; }
                    }
                    ?>
                    <?php if ($adaYangBelumBeli): ?>
                        <form method="post" action="<?= base_url('user/cart/checkout') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                <i class="bi bi-bag-check me-1"></i>Lanjut ke Pembayaran
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>
                            Semua paket sudah dimiliki
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
(function () {
    const csrfName  = '<?= csrf_token() ?>';
    const csrfHash  = '<?= csrf_hash() ?>';
    const removeUrl = '<?= base_url('user/cart/remove') ?>';

    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function updateRingkasan(removedProdukId) {
        // Hapus baris item di ringkasan
        const ringkasanItem = document.querySelector('.ringkasan-item[data-produk-id="' + removedProdukId + '"]');
        if (ringkasanItem) ringkasanItem.remove();

        // Hitung ulang total dari card items yang masih ada
        let newTotal = 0;
        document.querySelectorAll('[id^="cart-item-"]').forEach(function (card) {
            const harga = parseInt(card.dataset.harga) || 0;
            newTotal += harga;
        });

        // Update total di ringkasan
        const totalEl = document.getElementById('ringkasan-total');
        if (totalEl) totalEl.textContent = formatRupiah(newTotal);
    }

    document.querySelectorAll('.btn-remove-cart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const produkId = this.dataset.produkId;
            const card     = document.getElementById('cart-item-' + produkId);

            fetch(removeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ [csrfName]: csrfHash, produk_id: produkId }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    if (card) card.remove();
                    updateRingkasan(produkId);
                    // Update badge keranjang di navbar
                    const badge = document.getElementById('cart-badge');
                    if (badge) badge.textContent = data.count;
                    if (data.count === 0) location.reload();
                }
            });
        });
    });
}());
</script>

<?= $this->endSection() ?>
