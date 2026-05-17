<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-journal-bookmark"></i></div>
    <div>
        <div class="ph-title">Paket Saya</div>
        <div class="ph-subtitle">Paket tryout yang Anda miliki</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.tryout-card {
    transition: transform .18s ease, box-shadow .18s ease;
    border-radius: 1rem !important;
    overflow: hidden;
}
.tryout-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12) !important;
}
.tryout-card .thumb-wrap {
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #e8f0fe;
}
.tryout-card .thumb-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: center;
    transition: transform .3s ease;
}
.tryout-card:hover .thumb-wrap img {
    transform: scale(1.04);
}
.tryout-card .nama-paket {
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.3;
    color: #1a3a5c;
}
.sesi-item {
    border-left: 3px solid #e9ecef;
    transition: border-color .15s;
}
.sesi-item:hover {
    border-left-color: #1a3a5c;
    background: #f8faff;
}
</style>

<?php if (empty($paketList)): ?>
    <div class="card border-0 shadow-sm rounded-3 mt-2">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-2">Anda belum memiliki paket tryout.</p>
            <a href="<?= base_url('user/produk') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i>Beli Paket Tryout
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Filter Pencarian -->
    <div class="mb-3 mt-2">
        <div class="input-group" style="max-width:360px">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="filterTryout" class="form-control border-start-0 ps-0"
                   placeholder="Cari nama paket..." autocomplete="off">
            <button type="button" id="clearFilterTryout" class="btn btn-outline-secondary d-none" title="Hapus filter">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <div id="emptyFilterTryout" class="d-none">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-search fs-1 d-block mb-3"></i>
                <p class="mb-0">Tidak ada paket yang cocok dengan pencarian Anda.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1" id="tryoutGrid">
        <?php foreach ($paketList as $idx => $item):
            $produk  = $item['produk'];
            $tryouts = $item['tryouts'];
            $thumb   = ! empty($produk['thumbnail'])
                ? base_url('uploads/produk/' . $produk['thumbnail'])
                : base_url('assets/images/thumbnail/product-default.png');

            $progress  = $item['jumlah_tryout'] > 0
                ? round(($item['jumlah_selesai'] / $item['jumlah_tryout']) * 100)
                : 0;
            $modalId        = 'modalSesi' . $idx;
            // ID tryout pertama untuk link "Lihat Sesi" di card
            $tryout_id_item = ! empty($tryouts) ? $tryouts[0]['id'] : 0;
        ?>
            <div class="col-6 col-md-4 col-lg-3 tryout-item" data-nama="<?= strtolower(esc($produk['nama'])) ?>">
                <div class="card border-0 shadow-sm h-100 tryout-card position-relative">

                    <!-- Badge progress -->
                    <?php if ($progress === 100): ?>
                        <span class="badge bg-success position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                            <i class="bi bi-check-circle me-1"></i>Selesai
                        </span>
                    <?php elseif ($item['jumlah_selesai'] > 0): ?>
                        <span class="badge bg-warning text-dark position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                            <i class="bi bi-play-circle me-1"></i>Berlangsung
                        </span>
                    <?php endif; ?>

                    <!-- Thumbnail -->
                    <div class="thumb-wrap">
                        <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>">
                    </div>

                    <div class="card-body d-flex flex-column p-3">

                        <!-- Nama Paket -->
                        <h6 class="nama-paket mb-2"><?= esc($produk['nama']) ?></h6>

                        <!-- Info badge -->
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.68rem">
                                <i class="bi bi-journal-text me-1"></i><?= $item['jumlah_tryout'] ?> sesi
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size:.68rem">
                                <i class="bi bi-clock me-1"></i><?= $item['total_durasi'] ?> mnt
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size:.68rem">
                                <i class="bi bi-list-ol me-1"></i><?= $item['total_soal'] ?> soal
                            </span>
                        </div>

                        <!-- Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;color:#6c757d">
                                <span><?= $item['jumlah_selesai'] ?>/<?= $item['jumlah_tryout'] ?> selesai</span>
                                <span><?= $progress ?>%</span>
                            </div>
                            <div class="progress" style="height:5px;border-radius:3px">
                                <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <!-- Lihat Sesi → halaman baru -->
                            <a href="<?= base_url('user/tryout/' . $tryout_id_item . '/sesi') ?>"
                               class="btn btn-outline-primary btn-sm"
                               style="border-radius:.5rem;font-weight:600;font-size:.8rem">
                                <i class="bi bi-list-ul me-1"></i>Lihat Sesi (<?= $item['jumlah_tryout'] ?>)
                            </a>

                            <!-- Detail Produk -->
                            <a href="<?= base_url('user/produk/' . $produk['id']) ?>"
                               class="btn btn-outline-secondary btn-sm"
                               style="border-radius:.5rem;font-weight:600;font-size:.8rem">
                                <i class="bi bi-box-seam me-1"></i>Detail Produk
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal Daftar Sesi -->
            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h6 class="modal-title fw-bold" style="color:#1a3a5c"><?= esc($produk['nama']) ?></h6>
                                <p class="text-muted mb-0" style="font-size:.78rem">
                                    <?= $item['jumlah_selesai'] ?>/<?= $item['jumlah_tryout'] ?> sesi selesai
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <?php if (empty($tryouts)): ?>
                                <div class="text-center py-3 text-muted small">
                                    <i class="bi bi-journal-x me-1"></i>Belum ada sesi tryout.
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($tryouts as $i => $t): ?>
                                        <div class="sesi-item rounded p-3 bg-white border">
                                            <div class="d-flex align-items-center gap-3">
                                                <!-- Nomor -->
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                      style="width:30px;height:30px;font-size:.78rem">
                                                    <?= $i + 1 ?>
                                                </span>

                                                <!-- Info -->
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-semibold text-truncate" style="font-size:.88rem">
                                                        <?= esc($t['nama']) ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size:.73rem">
                                                        <i class="bi bi-clock me-1"></i><?= (int) $t['durasi'] ?> mnt
                                                        &bull;
                                                        <i class="bi bi-list-ol me-1"></i><?= (int) $t['jumlah_soal'] ?> soal
                                                    </div>
                                                </div>

                                                <!-- Tombol -->
                                                <div class="flex-shrink-0">
                                                    <?php if ($t['sesi_aktif_id']): ?>
                                                        <a href="<?= base_url('user/tryout/sesi/' . $t['sesi_aktif_id'] . '/soal/1') ?>"
                                                           class="btn btn-warning btn-sm py-1 px-2 fw-semibold" style="font-size:.75rem">
                                                            <i class="bi bi-play-circle me-1"></i>Lanjut
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="d-flex gap-1">
                                                            <a href="<?= base_url('user/tryout/' . $t['id'] . '/sesi') ?>"
                                                               class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size:.72rem">
                                                                <i class="bi bi-clock-history me-1"></i>Riwayat
                                                            </a>
                                                            <a href="<?= base_url('user/tryout/' . $t['id'] . '/mulai') ?>"
                                                               class="btn btn-primary btn-sm py-1 px-2 fw-semibold" style="font-size:.75rem">
                                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Riwayat sesi selesai -->
                                        <?php if (! empty($t['riwayat'])): ?>
                                            <div class="mt-2 ms-5 ps-1">
                                                <div class="text-muted mb-1" style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">
                                                    Riwayat (<?= count($t['riwayat']) ?> sesi)
                                                </div>
                                                <?php foreach ($t['riwayat'] as $ri => $riwayat): ?>
                                                    <div class="d-flex align-items-center justify-content-between py-1 px-2 rounded mb-1"
                                                         style="background:#f8f9fa;font-size:.75rem">
                                                        <div>
                                                            <span class="text-muted me-2">#<?= $ri + 1 ?></span>
                                                            <?= date('d M Y H:i', strtotime($riwayat['mulai_at'])) ?>
                                                            <?php if ($riwayat['skor_total'] !== null): ?>
                                                                &bull;
                                                                <span class="fw-semibold <?= $riwayat['skor_total'] >= 70 ? 'text-success' : ($riwayat['skor_total'] >= 50 ? 'text-warning' : 'text-danger') ?>">
                                                                    <?= number_format($riwayat['skor_total'], 1) ?>%
                                                                </span>
                                                                <?php if ($riwayat['status_lulus'] === 'lulus'): ?>
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size:.62rem">Lulus</span>
                                                                <?php elseif ($riwayat['status_lulus'] === 'tidak_lulus'): ?>
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size:.62rem">Tidak Lulus</span>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            <?php if ($riwayat['skor_total'] !== null): ?>
                                                                <a href="<?= base_url('user/tryout/hasil/' . $riwayat['sesi_id']) ?>"
                                                                   class="btn btn-outline-primary py-0 px-2" style="font-size:.7rem">
                                                                    <i class="bi bi-bar-chart me-1"></i>Hasil
                                                                </a>
                                                                <a href="<?= base_url('user/tryout/pembahasan/' . $riwayat['sesi_id']) ?>"
                                                                   class="btn btn-outline-secondary py-0 px-2" style="font-size:.7rem">
                                                                    <i class="bi bi-book me-1"></i>Bahas
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
(function () {
    const input    = document.getElementById('filterTryout');
    const clearBtn = document.getElementById('clearFilterTryout');
    const empty    = document.getElementById('emptyFilterTryout');
    const grid     = document.getElementById('tryoutGrid');

    if (!input || !grid) return;

    function doFilter() {
        const q     = input.value.trim().toLowerCase();
        const items = grid.querySelectorAll('.tryout-item');
        let visible = 0;

        items.forEach(function (el) {
            const nama = el.dataset.nama || '';
            const show = !q || nama.includes(q);
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        clearBtn.classList.toggle('d-none', !q);
        empty.classList.toggle('d-none', visible > 0);
        grid.classList.toggle('d-none', visible === 0);
    }

    input.addEventListener('input', doFilter);
    clearBtn.addEventListener('click', function () {
        input.value = '';
        doFilter();
        input.focus();
    });
}());
</script>

<?= $this->endSection() ?>
