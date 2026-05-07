<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon">
        <i class="bi bi-shop"></i>
    </div>
    <div>
        <div class="ph-title">Katalog Paket Tryout</div>
        <div class="ph-subtitle">Pilih paket terbaik untuk persiapan ujian Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.produk-card {
    transition: transform .18s ease, box-shadow .18s ease;
    border-radius: 1rem !important;
    overflow: hidden;
}
.produk-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12) !important;
}
.produk-card .thumb-wrap {
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #e8f0fe;
}
.produk-card .thumb-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: center;
    transition: transform .3s ease;
}
.produk-card:hover .thumb-wrap img {
    transform: scale(1.04);
}
.produk-card .badge-owned {
    position: absolute; top: .6rem; right: .6rem;
    font-size: .7rem;
}
.produk-card .nama-produk {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.3;
    color: #1a3a5c;
}
.produk-card .harga-normal {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1a3a5c;
}
.produk-card .harga-promo {
    font-size: 1.1rem;
    font-weight: 800;
    color: #dc3545;
}
.btn-cart {
    border-radius: .5rem;
    font-weight: 600;
    font-size: .8rem;
}
.toast-cart {
    min-width: 260px;
}
</style>

<!-- Flash / info dari cart -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mt-0 mb-3">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($produk)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-0">Belum ada paket tryout yang tersedia saat ini.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mt-1">
        <?php foreach ($produk as $p): ?>
            <?php
            $thumb = ! empty($p['thumbnail'])
                ? base_url('uploads/produk/' . $p['thumbnail'])
                : base_url('assets/images/thumbnail/product-default.png');
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 produk-card position-relative">

                    <!-- Badge Dimiliki -->
                    <?php if ($p['sudah_beli']): ?>
                        <span class="badge bg-success badge-owned">
                            <i class="bi bi-check-circle me-1"></i>Dimiliki
                        </span>
                    <?php endif; ?>

                    <!-- Thumbnail -->
                    <div class="thumb-wrap">
                        <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>">
                    </div>

                    <div class="card-body d-flex flex-column p-3">

                        <!-- Nama Produk -->
                        <h6 class="nama-produk mb-2"><?= esc($p['nama']) ?></h6>

                        <!-- Info Tryout -->
                        <div class="d-flex align-items-center gap-1 mb-2 text-muted" style="font-size:.78rem">
                            <i class="bi bi-journal-text"></i>
                            <span><?= $p['jumlah_tryout'] ?> sesi tryout</span>
                        </div>

                        <!-- Harga -->
                        <div class="mb-3 mt-auto">
                            <?php if ($p['harga_promo'] !== null): ?>
                                <div class="text-decoration-line-through text-muted" style="font-size:.78rem">
                                    Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                </div>
                                <div class="harga-promo">
                                    Rp <?= number_format($p['harga_promo'], 0, ',', '.') ?>
                                </div>
                                <?php foreach ($p['promosi'] as $pr): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"
                                          style="font-size:.68rem">
                                        <i class="bi bi-tag me-1"></i><?= esc($pr['nama']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="harga-normal">
                                    Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex flex-column gap-2">
                            <a href="<?= base_url('user/produk/' . $p['id']) ?>"
                               class="btn btn-outline-primary btn-sm btn-cart">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>

                            <?php if ($p['sudah_beli']): ?>
                                <a href="<?= base_url('user/dashboard') ?>"
                                   class="btn btn-success btn-sm btn-cart">
                                    <i class="bi bi-play-circle me-1"></i>Mulai Tryout
                                </a>
                            <?php else: ?>
                                <!-- Tambah ke keranjang -->
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm btn-cart btn-add-cart"
                                        data-produk-id="<?= $p['id'] ?>"
                                        data-produk-nama="<?= esc($p['nama']) ?>">
                                    <i class="bi bi-cart-plus me-1"></i>Keranjang
                                </button>
                                <!-- Beli sekarang → tambah ke cart lalu redirect -->
                                <button type="button"
                                        class="btn btn-primary btn-sm btn-cart btn-beli-sekarang"
                                        data-produk-id="<?= $p['id'] ?>"
                                        data-produk-nama="<?= esc($p['nama']) ?>">
                                    <i class="bi bi-bag-check me-1"></i>Beli Sekarang
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Toast Notifikasi -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="toastCart" class="toast toast-cart align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastCartBody">—</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
(function () {
    const csrfName  = '<?= csrf_token() ?>';
    const csrfHash  = '<?= csrf_hash() ?>';
    const addUrl    = '<?= base_url('user/cart/add') ?>';
    const cartUrl   = '<?= base_url('user/cart') ?>';

    function showToast(msg, success) {
        const el   = document.getElementById('toastCart');
        const body = document.getElementById('toastCartBody');
        el.classList.remove('bg-success', 'bg-danger', 'bg-warning');
        el.classList.add(success ? 'bg-success' : 'bg-warning');
        body.textContent = msg;
        bootstrap.Toast.getOrCreateInstance(el, { delay: 2800 }).show();
    }

    function updateBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (! badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    }

    function addToCart(produkId, callback) {
        return fetch(addUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ [csrfName]: csrfHash, produk_id: produkId }),
        }).then(r => r.json()).then(data => {
            updateBadge(data.count);
            if (callback) callback(data);
            return data;
        });
    }

    // Tombol "Keranjang" — tambah saja, tampilkan toast
    document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const produkId = this.dataset.produkId;
            const self     = this;
            self.disabled  = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            addToCart(produkId, function (data) {
                self.disabled  = false;
                self.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Keranjang';
                showToast(data.message, data.status);
            });
        });
    });

    // Tombol "Beli Sekarang" — tambah ke cart lalu redirect ke halaman cart
    document.querySelectorAll('.btn-beli-sekarang').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const produkId = this.dataset.produkId;
            const self     = this;
            self.disabled  = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            addToCart(produkId, function () {
                window.location.href = cartUrl;
            }).catch(() => {
                self.disabled  = false;
                self.innerHTML = '<i class="bi bi-bag-check me-1"></i>Beli Sekarang';
                showToast('Terjadi kesalahan. Coba lagi.', false);
            });
        });
    });
}());
</script>

<?= $this->endSection() ?>
