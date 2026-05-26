<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-credit-card-2-front"></i></div>
    <div>
        <div class="ph-title">Status Pembayaran</div>
        <div class="ph-subtitle">Konfirmasi pembayaran Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.status-card {
    border-radius: 1.25rem !important;
    overflow: hidden;
    max-width: 600px;
    margin: 0 auto;
}
.status-icon {
    width: 100px; height: 100px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
}
.status-icon.success { background: #d1fae5; color: #059669; }
.status-icon.pending { background: #fef3c7; color: #d97706; }
.status-icon.failed  { background: #fee2e2; color: #dc2626; }
.status-icon.expired { background: #f3f4f6; color: #6b7280; }

.confetti {
    position: relative;
    overflow: hidden;
}
.confetti::before {
    content: '🎉';
    position: absolute;
    top: 1rem; left: 1.5rem;
    font-size: 2rem;
    animation: bounce 1s ease infinite alternate;
}
.confetti::after {
    content: '🎊';
    position: absolute;
    top: 1rem; right: 1.5rem;
    font-size: 2rem;
    animation: bounce 1s ease 0.3s infinite alternate;
}
@keyframes bounce {
    from { transform: translateY(0); }
    to   { transform: translateY(-8px); }
}
</style>

<?php
$status = $transaksi['status'] ?? 'pending';

$statusConfig = [
    'success' => [
        'icon'    => 'bi-check-circle-fill',
        'class'   => 'success',
        'title'   => 'Pembayaran Berhasil!',
        'message' => 'Terima kasih! Pembayaran Anda telah dikonfirmasi. Akses paket tryout sudah aktif.',
        'color'   => '#059669',
    ],
    'pending' => [
        'icon'    => 'bi-hourglass-split',
        'class'   => 'pending',
        'title'   => 'Menunggu Pembayaran',
        'message' => 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran sesuai instruksi yang diberikan.',
        'color'   => '#d97706',
    ],
    'failed' => [
        'icon'    => 'bi-x-circle-fill',
        'class'   => 'failed',
        'title'   => 'Pembayaran Gagal',
        'message' => 'Maaf, pembayaran Anda tidak berhasil. Silakan coba lagi dengan metode pembayaran lain.',
        'color'   => '#dc2626',
    ],
    'expired' => [
        'icon'    => 'bi-clock-history',
        'class'   => 'expired',
        'title'   => 'Transaksi Kedaluwarsa',
        'message' => 'Waktu pembayaran telah habis. Silakan buat transaksi baru jika masih ingin membeli.',
        'color'   => '#6b7280',
    ],
];

$sc = $statusConfig[$status] ?? $statusConfig['pending'];
?>

<div class="card border-0 shadow-sm status-card mt-3 <?= $status === 'success' ? 'confetti' : '' ?>">
    <div class="card-body text-center py-5 px-4">

        <!-- Status Icon -->
        <div class="status-icon <?= $sc['class'] ?>">
            <i class="bi <?= $sc['icon'] ?>"></i>
        </div>

        <!-- Title -->
        <h4 class="fw-bold mb-2" style="color:<?= $sc['color'] ?>"><?= $sc['title'] ?></h4>

        <!-- Message -->
        <p class="text-muted mb-4" style="max-width:400px;margin:0 auto">
            <?= $sc['message'] ?>
        </p>

        <!-- Detail Transaksi -->
        <div class="bg-light rounded-3 p-3 mb-4 text-start" style="max-width:400px;margin:0 auto">
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Kode Transaksi</span>
                <span class="fw-semibold font-monospace"><?= esc($transaksi['kode_transaksi']) ?></span>
            </div>
            <?php if ($produk): ?>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Paket</span>
                <span class="fw-semibold text-end" style="max-width:200px"><?= esc($produk['nama']) ?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Total Bayar</span>
                <span class="fw-bold text-primary">Rp <?= number_format((float)$transaksi['harga_bayar'], 0, ',', '.') ?></span>
            </div>
            <?php if (! empty($transaksi['payment_method'])): ?>
                <?php $pm = \App\Services\MidtransService::PAYMENT_METHODS[$transaksi['payment_method']] ?? null; ?>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Metode</span>
                    <span>
                        <?php if ($pm): ?>
                            <span class="badge rounded-pill px-2 py-1" style="background:<?= $pm['color'] ?>;font-size:.75rem;color:#fff">
                                <?= esc($pm['label']) ?>
                            </span>
                        <?php else: ?>
                            <?= esc($transaksi['payment_method']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-column gap-2" style="max-width:320px;margin:0 auto">
            <?php if ($status === 'success'): ?>
                <a href="<?= base_url('user/tryout') ?>" class="btn btn-success fw-semibold py-2">
                    <i class="bi bi-play-circle me-2"></i>Mulai Tryout Sekarang
                </a>
                <a href="<?= base_url('user/transaksi/' . $transaksi['id']) ?>" class="btn btn-outline-primary fw-semibold py-2">
                    <i class="bi bi-receipt me-2"></i>Lihat Detail Transaksi
                </a>
            <?php elseif ($status === 'pending'): ?>
                <a href="<?= base_url('user/transaksi/' . $transaksi['id']) ?>" class="btn btn-warning fw-semibold py-2">
                    <i class="bi bi-credit-card me-2"></i>Lanjutkan Pembayaran
                </a>
                <a href="<?= base_url('user/transaksi') ?>" class="btn btn-outline-secondary fw-semibold py-2">
                    <i class="bi bi-list-ul me-2"></i>Riwayat Transaksi
                </a>
            <?php elseif ($status === 'failed' || $status === 'expired'): ?>
                <a href="<?= base_url('user/transaksi/pilih-metode/' . $transaksi['produk_id']) ?>" class="btn btn-primary fw-semibold py-2">
                    <i class="bi bi-arrow-repeat me-2"></i>Coba Beli Lagi
                </a>
                <a href="<?= base_url('user/produk') ?>" class="btn btn-outline-secondary fw-semibold py-2">
                    <i class="bi bi-shop me-2"></i>Kembali ke Katalog
                </a>
            <?php endif; ?>
        </div>

        <!-- Timestamp -->
        <p class="text-muted small mt-4 mb-0">
            <i class="bi bi-clock me-1"></i>
            <?= date('d M Y, H:i', strtotime($transaksi['created_at'])) ?> WIB
        </p>

    </div>
</div>

<?php if ($status === 'pending'): ?>
<!-- Auto-refresh setiap 10 detik untuk cek status terbaru -->
<script>
(function () {
    let attempts = 0;
    const maxAttempts = 30; // max 5 menit (30 x 10 detik)

    function checkStatus() {
        if (attempts >= maxAttempts) return;
        attempts++;

        fetch('<?= base_url('user/transaksi/' . $transaksi['id'] . '/cek-status') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.transaction_status && data.transaction_status !== 'pending') {
                window.location.reload();
            } else {
                setTimeout(checkStatus, 10000);
            }
        })
        .catch(() => setTimeout(checkStatus, 10000));
    }

    setTimeout(checkStatus, 10000);
}());
</script>
<?php endif; ?>

<?= $this->endSection() ?>
