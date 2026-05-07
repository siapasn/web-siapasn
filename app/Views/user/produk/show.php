<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-box-seam"></i></div>
    <div>
        <div class="ph-title"><?= esc($produk['nama']) ?></div>
        <div class="ph-subtitle">Detail Paket Tryout</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-3">
    <a href="<?= base_url('user/produk') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
    </a>
</div>

<div class="row g-4">
    <!-- Detail Produk -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Deskripsi Paket</h6>
            </div>
            <div class="card-body">
                <?php
                $deskripsiHtml = $produk['deskripsi'] ?? '';
                // Jika kosong atau hanya tag kosong dari Summernote
                $deskripsiPlain = trim(strip_tags($deskripsiHtml));
                if ($deskripsiPlain === '') {
                    $deskripsiHtml = '<p class="text-muted">Paket tryout CPNS lengkap untuk persiapan ujian.</p>';
                }
                ?>
                <div class="produk-deskripsi"><?= $deskripsiHtml ?></div>
            </div>
        </div>

        <!-- Daftar Tryout -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Sesi Tryout yang Termasuk</h6>
                <span class="badge bg-primary"><?= count($tryouts) ?> sesi</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tryouts)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                        Belum ada tryout yang dipetakan ke paket ini.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($tryouts as $i => $tryout): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2 fw-bold">
                                        <?= $i + 1 ?>
                                    </span>
                                    <div>
                                        <div class="fw-semibold"><?= esc($tryout['nama']) ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?= $tryout['durasi'] ?> menit
                                            &bull;
                                            <i class="bi bi-question-circle me-1"></i><?= $tryout['jumlah_soal'] ?> soal
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($sudahBeli): ?>
                                        <?php if (! empty($tryout['sudah_selesai'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check-circle me-1"></i>Selesai
                                            </span>
                                        <?php elseif (! empty($tryout['sesi_aktif_id'])): ?>
                                            <a href="<?= base_url('user/tryout/sesi/' . $tryout['sesi_aktif_id'] . '/soal/1') ?>"
                                               class="btn btn-warning btn-sm py-1 px-2 fw-semibold" style="font-size:.78rem">
                                                <i class="bi bi-play-circle me-1"></i>Lanjutkan
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('user/tryout/' . $tryout['id'] . '/mulai') ?>"
                                               class="btn btn-primary btn-sm py-1 px-2 fw-semibold" style="font-size:.78rem">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($tryout['is_active']): ?>
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panel Pembelian -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
            <!-- Thumbnail -->
            <?php
            $thumb = ! empty($produk['thumbnail'])
                ? base_url('uploads/produk/' . $produk['thumbnail'])
                : base_url('assets/images/thumbnail/product-default.png');
            ?>
            <div style="aspect-ratio:1/1;overflow:hidden;border-radius:.375rem .375rem 0 0;">
                <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>"
                     class="w-100 h-100" style="object-fit:cover;object-position:center;">
            </div>

            <div class="card-body">
                <?php if ($sudahBeli): ?>
                    <!-- Sudah beli: sembunyikan harga dan form beli -->
                    <div class="alert alert-success py-2 mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Anda sudah memiliki akses ke paket ini.
                    </div>
                <?php else: ?>
                    <!-- Belum beli: tampilkan harga dan tombol beli -->
                    <div class="mb-3">
                        <?php if (!empty($promosi)): ?>
                            <?php
                                $diskonTerbesar = 0;
                                foreach ($promosi as $pr) {
                                    if ($pr['jenis_diskon'] === 'persentase') {
                                        $d = $produk['harga'] * ($pr['nilai_diskon'] / 100);
                                    } else {
                                        $d = min((float)$pr['nilai_diskon'], $produk['harga']);
                                    }
                                    if ($d > $diskonTerbesar) $diskonTerbesar = $d;
                                }
                                $hargaPromo = max(0, $produk['harga'] - $diskonTerbesar);
                            ?>
                            <div class="text-decoration-line-through text-muted small">
                                Rp <?= number_format($produk['harga'], 0, ',', '.') ?>
                            </div>
                            <div class="fs-3 fw-bold text-danger">
                                Rp <?= number_format($hargaPromo, 0, ',', '.') ?>
                            </div>
                            <?php foreach ($promosi as $pr): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle small">
                                    <i class="bi bi-tag me-1"></i><?= esc($pr['nama']) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="fs-3 fw-bold text-primary">
                                Rp <?= number_format($produk['harga'], 0, ',', '.') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <a href="<?= base_url('user/transaksi/pilih-metode/' . $produk['id']) ?>"
                       class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-credit-card me-1"></i>Pilih Metode & Beli
                    </a>

                    <div class="mt-3 text-muted small">
                        <i class="bi bi-shield-check me-1 text-success"></i>Pembayaran aman via Midtrans
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
