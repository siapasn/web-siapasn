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
/* ── Card ── */
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
.tryout-card:hover .thumb-wrap img { transform: scale(1.04); }
.tryout-card .nama-paket {
    font-size: .95rem; font-weight: 700;
    line-height: 1.3; color: #1a3a5c;
}
.sesi-item {
    border-left: 3px solid #e9ecef;
    transition: border-color .15s;
}
.sesi-item:hover { border-left-color: #1a3a5c; background: #f8faff; }

/* ── Tabs (sama dengan halaman produk) ── */
.katalog-tabs {
    gap: .4rem;
    border-bottom: none;
    padding-bottom: .75rem;
    flex-wrap: wrap;
}
.katalog-tabs .nav-link {
    color: #64748b;
    font-weight: 600;
    border-radius: 2rem;
    padding: .45rem 1.1rem;
    font-size: .85rem;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    transition: all .2s ease;
    white-space: nowrap;
}
.katalog-tabs .nav-link:hover {
    color: #1a3a5c;
    background: #e8f0fe;
    border-color: #93b4e8;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(26,58,92,.12);
}
.katalog-tabs .nav-link.active {
    color: #fff;
    background: linear-gradient(135deg, #1a3a5c 0%, #2d6a9f 100%);
    border-color: transparent;
    box-shadow: 0 4px 14px rgba(26,58,92,.35);
    transform: translateY(-1px);
}
.katalog-tabs .nav-link .badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.3rem;
    height: 1.3rem;
    border-radius: 1rem;
    font-size: .65rem;
    font-weight: 700;
    padding: 0 .35rem;
    margin-left: .35rem;
    background: rgba(0,0,0,.08);
    color: inherit;
    transition: all .2s;
}
.katalog-tabs .nav-link.active .badge-count {
    background: rgba(255,255,255,.25);
    color: #fff;
}
.katalog-tabs-content {
    border: none;
    border-radius: .75rem;
    background: #fff;
    padding: 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}

/* ── Search Box ── */
.search-box {
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 3rem;
    padding: .35rem .5rem .35rem 1rem;
    transition: all .3s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    gap: .5rem;
    width: 320px;
    max-width: 100%;
}
.search-box:focus-within {
    border-color: #1a3a5c;
    box-shadow: 0 4px 20px rgba(26,58,92,.15), 0 0 0 4px rgba(26,58,92,.06);
    transform: translateY(-1px);
    width: 380px;
}
.search-box .search-icon {
    color: #94a3b8;
    font-size: 1rem;
    transition: color .3s, transform .3s;
    flex-shrink: 0;
}
.search-box:focus-within .search-icon {
    color: #1a3a5c;
    transform: scale(1.1);
}
.search-box input {
    border: none;
    outline: none;
    background: transparent;
    flex-grow: 1;
    font-size: .88rem;
    color: #1e293b;
    min-width: 0;
}
.search-box input::placeholder {
    color: #94a3b8;
    transition: opacity .2s;
}
.search-box:focus-within input::placeholder { opacity: .5; }
.search-box .search-btn {
    background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
    color: #fff;
    border: none;
    border-radius: 2rem;
    padding: .4rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
    flex-shrink: 0;
}
.search-box .search-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 12px rgba(26,58,92,.3);
}
.search-box .search-btn:active { transform: scale(.97); }
.search-box .reset-btn {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: .3rem;
    border-radius: 50%;
    transition: all .2s;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
}
.search-box .reset-btn:hover {
    background: #fee2e2;
    color: #dc2626;
    transform: rotate(90deg);
}
@media (max-width: 576px) {
    .search-box, .search-box:focus-within { width: 100%; }
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

    <!-- ── Tab Header ── -->
    <ul class="nav katalog-tabs mb-0 mt-2 flex-nowrap overflow-auto" id="tryoutTab" role="tablist"
        style="scrollbar-width:none">
        <?php foreach ($paketByKategori as $i => $kat): ?>
            <?php if (empty($kat['paket'])) continue; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $i === 0 || (! isset($firstTab) && ! empty($kat['paket'])) ? (isset($firstTab) ? '' : 'active') : '' ?>"
                        id="ttab-<?= $kat['kat_id'] ?: 'lainnya' ?>-btn"
                        data-bs-toggle="tab"
                        data-bs-target="#ttab-<?= $kat['kat_id'] ?: 'lainnya' ?>"
                        type="button" role="tab">
                    <?php $firstTab = true; ?>
                    <?= esc($kat['kat_nama']) ?>
                    <span class="badge-count"><?= count($kat['paket']) ?></span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- ── Tab Content ── -->
    <div class="katalog-tabs-content shadow-sm">
        <div class="tab-content" id="tryoutTabContent">
            <?php
            $firstActive = true;
            foreach ($paketByKategori as $i => $kat):
                if (empty($kat['paket'])) continue;
                $tabId = $kat['kat_id'] ?: 'lainnya';
                $totalPaket    = count($kat['paket']);
                $totalSelesai  = array_sum(array_column($kat['paket'], 'jumlah_selesai'));
                $totalTryout   = array_sum(array_column($kat['paket'], 'jumlah_tryout'));
            ?>
                <div class="tab-pane fade <?= $firstActive ? 'show active' : '' ?>"
                     id="ttab-<?= $tabId ?>" role="tabpanel">
                    <?php $firstActive = false; ?>

                    <!-- Stat + Filter -->
                    <div class="row g-2 align-items-center mb-4">

                        <!-- Kiri: stat -->
                        <div class="col-12 col-lg-auto d-flex gap-2 flex-wrap flex-lg-nowrap">
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 flex-shrink-0"
                                 style="background:#f0f5ff;border:1px solid #c5d5f0">
                                <i class="bi bi-box-seam" style="color:#1a3a5c;font-size:1rem"></i>
                                <div style="line-height:1.2">
                                    <div class="fw-bold" style="font-size:.95rem;color:#1a3a5c"><?= $totalPaket ?></div>
                                    <div style="font-size:.68rem;color:#64748b;white-space:nowrap">Total Paket</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 flex-shrink-0"
                                 style="background:#f0fdf4;border:1px solid #86efac">
                                <i class="bi bi-journal-check" style="color:#16a34a;font-size:1rem"></i>
                                <div style="line-height:1.2">
                                    <div class="fw-bold" style="font-size:.95rem;color:#16a34a"><?= $totalSelesai ?></div>
                                    <div style="font-size:.68rem;color:#64748b;white-space:nowrap">Sesi Selesai</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 flex-shrink-0"
                                 style="background:#fff7ed;border:1px solid #fdba74">
                                <i class="bi bi-list-ol" style="color:#ea580c;font-size:1rem"></i>
                                <div style="line-height:1.2">
                                    <div class="fw-bold" style="font-size:.95rem;color:#ea580c"><?= $totalTryout ?></div>
                                    <div style="font-size:.68rem;color:#64748b;white-space:nowrap">Total Sesi</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: filter -->
                        <div class="col-12 col-lg-auto ms-lg-auto">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text"
                                       class="filter-tryout"
                                       data-tab="<?= $tabId ?>"
                                       placeholder="Cari nama paket..."
                                       autocomplete="off"
                                       onkeydown="if(event.key==='Enter') doFilterTryout('<?= $tabId ?>')">
                                <button type="button" class="search-btn"
                                        onclick="doFilterTryout('<?= $tabId ?>')">
                                    Cari
                                </button>
                                <button type="button" class="reset-btn"
                                        onclick="resetFilterTryout('<?= $tabId ?>')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid paket -->
                    <div class="row g-3 tryout-grid" id="tgrid-<?= $tabId ?>">
                        <?php foreach ($kat['paket'] as $idx => $item):
                            $produk  = $item['produk'];
                            $tryouts = $item['tryouts'];
                            $thumb   = ! empty($produk['thumbnail'])
                                ? base_url('uploads/produk/' . $produk['thumbnail'])
                                : base_url('assets/images/thumbnail/product-default.png');
                            $progress  = $item['jumlah_tryout'] > 0
                                ? round(($item['jumlah_selesai'] / $item['jumlah_tryout']) * 100) : 0;
                            $modalId        = 'modalSesi' . $tabId . $idx;
                            $tryout_id_item = ! empty($tryouts) ? $tryouts[0]['id'] : 0;
                        ?>
                            <div class="col-6 col-md-4 col-lg-3 tryout-item"
                                 data-nama="<?= strtolower(esc($produk['nama'])) ?>">
                                <div class="card border-0 shadow-sm h-100 tryout-card position-relative">

                                    <?php if ($progress === 100): ?>
                                        <span class="badge bg-success position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                                            <i class="bi bi-check-circle me-1"></i>Selesai
                                        </span>
                                    <?php elseif ($item['jumlah_selesai'] > 0): ?>
                                        <span class="badge bg-warning text-dark position-absolute" style="top:.6rem;right:.6rem;font-size:.68rem;z-index:1">
                                            <i class="bi bi-play-circle me-1"></i>Berlangsung
                                        </span>
                                    <?php endif; ?>

                                    <div class="thumb-wrap">
                                        <img src="<?= $thumb ?>" alt="<?= esc($produk['nama']) ?>">
                                    </div>

                                    <div class="card-body d-flex flex-column p-3">
                                        <h6 class="nama-paket mb-2"><?= esc($produk['nama']) ?></h6>

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

                                        <?php if (! empty($produk['expired_at'])): ?>
                                        <?php
                                            $expiredTime = strtotime($produk['expired_at']);
                                            $now = time();
                                            $sisaHari = (int) ceil(($expiredTime - $now) / 86400);
                                            if ($sisaHari <= 30) {
                                                $expBadgeClass = 'bg-danger bg-opacity-10 text-danger border-danger-subtle';
                                            } elseif ($sisaHari <= 90) {
                                                $expBadgeClass = 'bg-warning bg-opacity-10 text-warning border-warning-subtle';
                                            } else {
                                                $expBadgeClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary-subtle';
                                            }
                                        ?>
                                        <div class="mb-2">
                                            <span class="badge <?= $expBadgeClass ?> border" style="font-size:.65rem">
                                                <i class="bi bi-calendar-event me-1"></i>Akses s/d <?= date('d M Y', $expiredTime) ?>
                                                <?php if ($sisaHari <= 30): ?>
                                                    <span class="ms-1">(<?= $sisaHari ?> hari lagi)</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;color:#6c757d">
                                                <span><?= $item['jumlah_selesai'] ?>/<?= $item['jumlah_tryout'] ?> selesai</span>
                                                <span><?= $progress ?>%</span>
                                            </div>
                                            <div class="progress" style="height:5px;border-radius:3px">
                                                <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-2 mt-auto">
                                            <a href="<?= base_url('user/tryout/' . $tryout_id_item . '/sesi') ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               style="border-radius:.5rem;font-weight:600;font-size:.8rem">
                                                <i class="bi bi-list-ul me-1"></i>Lihat Sesi (<?= $item['jumlah_tryout'] ?>)
                                            </a>
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
                                                    <?php foreach ($tryouts as $ti => $t): ?>
                                                        <div class="sesi-item rounded p-3 bg-white border">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                                      style="width:30px;height:30px;font-size:.78rem">
                                                                    <?= $ti + 1 ?>
                                                                </span>
                                                                <div class="flex-grow-1 min-w-0">
                                                                    <div class="fw-semibold text-truncate" style="font-size:.88rem"><?= esc($t['nama']) ?></div>
                                                                    <div class="text-muted" style="font-size:.73rem">
                                                                        <i class="bi bi-clock me-1"></i><?= (int) $t['durasi'] ?> mnt
                                                                        &bull;
                                                                        <i class="bi bi-list-ol me-1"></i><?= (int) $t['jumlah_soal'] ?> soal
                                                                    </div>
                                                                </div>
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

                    <!-- Empty filter state -->
                    <div class="empty-filter-tryout d-none text-center py-4 text-muted">
                        <i class="bi bi-search fs-2 d-block mb-2"></i>
                        <p class="mb-0">Tidak ada paket yang cocok.</p>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php endif; ?>

<script>
window.doFilterTryout = function (tabId) {
    const input = document.querySelector('.filter-tryout[data-tab="' + tabId + '"]');
    const grid  = document.getElementById('tgrid-' + tabId);
    if (! input || ! grid) return;

    const q     = input.value.trim().toLowerCase();
    const pane  = grid.closest('.tab-pane');
    const empty = pane ? pane.querySelector('.empty-filter-tryout') : null;

    let visible = 0;
    grid.querySelectorAll('.tryout-item').forEach(function (el) {
        const nama = el.dataset.nama || '';
        const show = !q || nama.includes(q);
        el.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    if (empty) {
        empty.classList.toggle('d-none', visible > 0);
        grid.classList.toggle('d-none', visible === 0);
    }
};

window.resetFilterTryout = function (tabId) {
    const input = document.querySelector('.filter-tryout[data-tab="' + tabId + '"]');
    if (input) { input.value = ''; input.focus(); }
    const grid = document.getElementById('tgrid-' + tabId);
    if (grid) {
        grid.querySelectorAll('.tryout-item').forEach(el => el.style.display = '');
        grid.classList.remove('d-none');
    }
    const pane  = grid ? grid.closest('.tab-pane') : null;
    const empty = pane ? pane.querySelector('.empty-filter-tryout') : null;
    if (empty) empty.classList.add('d-none');
};
</script>

<?= $this->endSection() ?>
