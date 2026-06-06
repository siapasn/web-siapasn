<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-shop"></i></div>
    <div>
        <div class="ph-title">Katalog Paket Tryout</div>
        <div class="ph-subtitle">Pilih paket terbaik untuk persiapan ujian Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Cek status launching
$db         = \Config\Database::connect();
$cfgRows    = $db->table('master_aplikasi')
    ->whereIn('config_key', ['launch_date', 'launch_message'])
    ->get()->getResultArray();
$cfgMap     = array_column($cfgRows, 'config_value', 'config_key');
$launchDate = $cfgMap['launch_date'] ?? '';
$launchMsg  = $cfgMap['launch_message'] ?? 'Pembelian paket tryout akan segera dibuka. Pantau terus halaman ini!';
$isLaunched = empty($launchDate) || strtotime($launchDate) <= time();
?>

<?php if (! $isLaunched): ?>
<div class="alert d-flex align-items-center gap-3 mb-4"
     style="background:linear-gradient(135deg,#1a3a5c,#2d6a9f);color:#fff;border:none;border-radius:.75rem">
    <div style="font-size:2rem;line-height:1">🚀</div>
    <div class="flex-grow-1">
        <div class="fw-bold mb-1">Pembelian Segera Dibuka!</div>
        <div style="font-size:.9rem;opacity:.9"><?= esc($launchMsg) ?></div>
    </div>
    <div class="text-end flex-shrink-0">
        <div style="font-size:.75rem;opacity:.8">Aktif pada</div>
        <div class="fw-bold" style="font-size:.95rem" id="launchCountdown">
            <?= date('d M Y, H:i', strtotime($launchDate)) ?> WIB
        </div>
        <div id="countdownTimer" class="mt-1" style="font-size:.8rem;opacity:.85"></div>
    </div>
</div>
<?php endif; ?>

<style>
/* ── Produk Card ── */
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
.produk-card:hover .thumb-wrap img { transform: scale(1.04); }
.produk-card .badge-owned {
    position: absolute; top: .6rem; right: .6rem;
    font-size: .7rem;
}
.produk-card .nama-produk {
    font-size: 1rem; font-weight: 700;
    line-height: 1.3; color: #1a3a5c;
}
.produk-card .harga-normal { font-size: 1.1rem; font-weight: 800; color: #1a3a5c; }
.produk-card .harga-promo  { font-size: 1.1rem; font-weight: 800; color: #dc3545; }
.btn-cart { border-radius: .5rem; font-weight: 600; font-size: .8rem; }
.toast-cart { min-width: 260px; }

/* ── Tabs ── */
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
.katalog-tabs .nav-link:hover .badge-count {
    background: rgba(26,58,92,.15);
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

<!-- Flash messages handled by layout -->

<?php if (empty($produkByKategori)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-0">Belum ada paket tryout yang tersedia saat ini.</p>
        </div>
    </div>
<?php else: ?>

    <!-- ── Filter Formasi ── -->
    <?php if (! empty($kategoriFormasi)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-3" id="filterFormasiCard">
        <div class="card-body py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold text-muted small"><i class="bi bi-briefcase me-1"></i>Filter Formasi:</span>
                <div style="min-width:220px;max-width:280px">
                    <select id="filterKategoriFormasi" class="form-select form-select-sm">
                        <option value="">Semua Kategori Formasi</option>
                        <?php foreach ($kategoriFormasi as $kf): ?>
                            <option value="<?= $kf['id'] ?>"><?= esc($kf['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:280px;max-width:380px">
                    <select id="filterFormasi" class="form-select form-select-sm">
                        <option value="">Semua Formasi</option>
                        <?php foreach ($formasiList as $f): ?>
                            <option value="<?= $f['id'] ?>" data-kategori="<?= $f['kategori_formasi_id'] ?>"><?= esc($f['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="resetFilterFormasi" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Reset
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Tab Header ── -->
    <ul class="nav katalog-tabs mb-0 mt-2 flex-nowrap overflow-auto" id="katalogTab" role="tablist"
        style="scrollbar-width:none">
        <?php foreach ($produkByKategori as $i => $kat): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                        id="tab-<?= $kat['kat_id'] ?: 'lainnya' ?>-btn"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-<?= $kat['kat_id'] ?: 'lainnya' ?>"
                        data-kat-id="<?= $kat['kat_id'] ?>"
                        type="button" role="tab">
                    <?= esc($kat['kat_nama']) ?>
                    <span class="badge badge-count ms-1">
                        <?= count($kat['produk']) ?>
                    </span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- ── Tab Content ── -->
    <div class="katalog-tabs-content shadow-sm">
        <div class="tab-content" id="katalogTabContent">
            <?php foreach ($produkByKategori as $i => $kat): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                     id="tab-<?= $kat['kat_id'] ?: 'lainnya' ?>"
                     role="tabpanel">

                    <!-- Filter pencarian per tab — hanya tampil jika ada produk -->
                    <?php if (! empty($kat['produk'])): ?>
                    <?php
                    $totalPaket    = count($kat['produk']);
                    $adaPromo      = count(array_filter($kat['produk'], fn($p) => $p['harga_promo'] !== null));
                    ?>
                    <div class="row g-2 align-items-center mb-4">

                        <!-- Kiri: stat cards -->
                        <div class="col-12 col-lg-auto d-flex gap-2 flex-wrap flex-lg-nowrap">
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 flex-shrink-0"
                                 style="background:#f0f5ff;border:1px solid #c5d5f0">
                                <i class="bi bi-box-seam" style="color:#1a3a5c;font-size:1rem"></i>
                                <div style="line-height:1.2">
                                    <div class="fw-bold" style="font-size:.95rem;color:#1a3a5c"><?= $totalPaket ?></div>
                                    <div style="font-size:.68rem;color:#64748b;white-space:nowrap">Paket Tersedia</div>
                                </div>
                            </div>
                            <?php if ($adaPromo > 0): ?>
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 flex-shrink-0"
                                 style="background:#fff7ed;border:1px solid #fdba74">
                                <i class="bi bi-tag-fill" style="color:#ea580c;font-size:1rem"></i>
                                <div style="line-height:1.2">
                                    <div class="fw-bold" style="font-size:.95rem;color:#ea580c"><?= $adaPromo ?></div>
                                    <div style="font-size:.68rem;color:#64748b;white-space:nowrap">Sedang Promo</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Kanan: filter — sudut kanan di desktop, full width di mobile -->
                        <div class="col-12 col-lg-auto ms-lg-auto">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text"
                                       class="filter-produk"
                                       data-tab="<?= $kat['kat_id'] ?: 'lainnya' ?>"
                                       placeholder="Cari nama paket..."
                                       autocomplete="off"
                                       onkeydown="if(event.key==='Enter') doFilterTab('<?= $kat['kat_id'] ?: 'lainnya' ?>')">
                                <button type="button" class="search-btn"
                                        onclick="doFilterTab('<?= $kat['kat_id'] ?: 'lainnya' ?>')">
                                    Cari
                                </button>
                                <button type="button" class="reset-btn"
                                        onclick="resetFilterTab('<?= $kat['kat_id'] ?: 'lainnya' ?>')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                    <?php endif; ?>

                    <!-- Grid produk -->
                    <div class="row g-3 produk-grid" id="grid-<?= $kat['kat_id'] ?: 'lainnya' ?>">
                        <?php foreach ($kat['produk'] as $p): ?>
                            <?php
                            $thumb = ! empty($p['thumbnail'])
                                ? base_url('uploads/produk/' . $p['thumbnail'])
                                : base_url('assets/images/thumbnail/product-default.png');
                            ?>
                            <div class="col-12 col-md-4 col-lg-3 produk-item"
                                 data-nama="<?= strtolower(esc($p['nama'])) ?>"
                                 data-formasi-id="<?= esc($p['formasi_id'] ?? '') ?>"
                                 data-kategori-formasi-id="<?= esc($p['kategori_formasi_id'] ?? '') ?>">
                                <div class="card border-0 shadow-sm h-100 produk-card position-relative">

                                    <?php if ($p['sudah_beli']): ?>
                                        <span class="badge bg-success badge-owned">
                                            <i class="bi bi-check-circle me-1"></i>Dimiliki
                                        </span>
                                    <?php endif; ?>

                                    <div class="thumb-wrap">
                                        <img src="<?= $thumb ?>" alt="<?= esc($p['nama']) ?>">
                                    </div>

                                    <div class="card-body d-flex flex-column p-3">
                                        <h6 class="nama-produk mb-2"><?= esc($p['nama']) ?></h6>

                                        <div class="d-flex align-items-center gap-1 mb-2 text-muted" style="font-size:.78rem">
                                            <i class="bi bi-journal-text"></i>
                                            <span><?= $p['jumlah_tryout'] ?> sesi tryout</span>
                                        </div>

                                        <?php if (! empty($p['formasi_nama'])): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-size:.68rem">
                                                <i class="bi bi-briefcase me-1"></i><?= esc($p['formasi_nama']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

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

                                                        <div class="d-flex flex-column gap-2">
                                            <a href="<?= base_url('user/produk/' . ($p['slug'] ?: $p['id'])) ?>"
                                               class="btn btn-outline-primary btn-sm btn-cart">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </a>

                                            <?php if ($p['sudah_beli']): ?>
                                                <a href="<?= base_url('user/tryout/' . $p['first_tryout_id'] . '/sesi') ?>"
                                                   class="btn btn-success btn-sm btn-cart">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai Tryout
                                                </a>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="btn btn-outline-secondary btn-sm btn-cart btn-add-cart"
                                                        data-produk-id="<?= $p['id'] ?>"
                                                        data-produk-nama="<?= esc($p['nama']) ?>"
                                                        <?= ! $isLaunched ? 'disabled title="Pembelian belum dibuka"' : '' ?>>
                                                    <i class="bi bi-cart-plus me-1"></i>Keranjang
                                                </button>
                                                <button type="button"
                                                        class="btn btn-primary btn-sm btn-cart btn-beli-sekarang"
                                                        data-produk-id="<?= $p['id'] ?>"
                                                        data-produk-nama="<?= esc($p['nama']) ?>"
                                                        <?= ! $isLaunched ? 'disabled title="Pembelian belum dibuka"' : '' ?>>
                                                    <i class="bi bi-bag-check me-1"></i>Beli Sekarang
                                                </button>
                                                <?php if (! $isLaunched): ?>
                                                <div class="text-center" style="font-size:.72rem;color:#64748b">
                                                    <i class="bi bi-clock me-1"></i>Segera dibuka
                                                </div>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php
                                            $shareTitle = $p['nama'];
                                            $shareUrl   = base_url('user/produk/' . ($p['slug'] ?: $p['id']));
                                            $shareText  = 'Paket Tryout CPNS - ' . $p['nama'];
                                            $shareBtnClass = 'btn-outline-secondary btn-cart w-100';
                                            echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText', 'shareBtnClass'));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Empty state per tab -->
                    <div class="empty-filter d-none text-center py-4 text-muted">
                        <i class="bi bi-search fs-2 d-block mb-2"></i>
                        <p class="mb-2">Tidak ada paket yang cocok.</p>
                        <p class="small mb-3">Tryout untuk formasi ini belum tersedia. Kirim request ke admin agar segera dibuatkan.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-request-formasi">
                            <i class="bi bi-send me-1"></i>Request Tryout Formasi Ini
                        </button>
                    </div>

                    <!-- Empty state: belum ada produk di kategori ini -->
                    <?php if (empty($kat['produk'])): ?>
                    <div class="text-center py-5">
                        <?php if (! empty($kat['semua_sudah_beli'])): ?>
                            <!-- Semua produk sudah dibeli -->
                            <div class="mb-4" style="font-size:4rem;line-height:1">🎉</div>
                            <h5 class="fw-bold text-dark mb-2">Semua Paket Sudah Dimiliki!</h5>
                            <p class="text-muted mb-3" style="max-width:380px;margin:0 auto">
                                Anda sudah memiliki semua paket <strong><?= esc($kat['kat_nama']) ?></strong> yang tersedia.
                                Kunjungi halaman <strong>Paket Saya</strong> untuk mulai tryout.
                            </p>
                            <a href="<?= base_url('user/tryout') ?>" class="btn btn-success btn-sm px-4 rounded-pill">
                                <i class="bi bi-play-circle me-1"></i>Mulai Tryout
                            </a>
                        <?php else: ?>
                            <!-- Belum ada produk sama sekali -->
                            <div class="mb-4" style="font-size:4rem;line-height:1">🚀</div>
                            <h5 class="fw-bold text-dark mb-2">Segera Hadir!</h5>
                            <p class="text-muted mb-3" style="max-width:380px;margin:0 auto">
                                Paket tryout <strong><?= esc($kat['kat_nama']) ?></strong> sedang dalam persiapan.
                                Kami sedang menyiapkan soal-soal terbaik untuk membantu persiapan ujian Anda.
                            </p>
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background:linear-gradient(135deg,#1a3a5c,#2d6a9f);font-size:.8rem;letter-spacing:.03em">
                                <i class="bi bi-clock me-1"></i>Coming Soon
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php endif; ?>

<!-- Modal Request Formasi -->
<div class="modal fade" id="modalRequestFormasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= base_url('user/request-formasi') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="formasi_id" id="requestFormasiId" value="">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" style="color:#1a3a5c">
                        <i class="bi bi-send me-2"></i>Request Tryout Formasi
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Tryout untuk formasi <strong id="requestFormasiNama"></strong> belum tersedia.
                        Kirim request ke admin agar segera dibuatkan.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small">Pesan / Catatan (opsional):</label>
                        <textarea name="pesan" class="form-control form-control-sm" rows="3"
                                  placeholder="Misal: Saya sangat membutuhkan tryout ini untuk persiapan SKB..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notifikasi -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="toastCart" class="toast toast-cart align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastCartBody">—</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php if (! $isLaunched && ! empty($launchDate)): ?>
// Countdown timer
(function () {
    const target  = new Date('<?= date('Y-m-d\TH:i:s', strtotime($launchDate)) ?>').getTime();
    const el      = document.getElementById('countdownTimer');
    if (! el) return;

    function tick() {
        const diff = target - Date.now();
        if (diff <= 0) {
            el.textContent = 'Pembelian sudah aktif! Silakan refresh halaman.';
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = (d > 0 ? d + 'h ' : '') + String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        setTimeout(tick, 1000);
    }
    tick();
}());
<?php endif; ?>

(function () {
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
    const addUrl   = '<?= base_url('user/cart/add') ?>';
    const cartUrl  = '<?= base_url('user/cart') ?>';

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

    document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const produkId = this.dataset.produkId;
            const self = this;
            self.disabled = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            addToCart(produkId, function (data) {
                self.disabled = false;
                self.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Keranjang';
                showToast(data.message, data.status);
            });
        });
    });

    document.querySelectorAll('.btn-beli-sekarang').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const produkId = this.dataset.produkId;
            const self = this;
            self.disabled = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            addToCart(produkId, function () {
                window.location.href = cartUrl;
            }).catch(() => {
                self.disabled = false;
                self.innerHTML = '<i class="bi bi-bag-check me-1"></i>Beli Sekarang';
                showToast('Terjadi kesalahan. Coba lagi.', false);
            });
        });
    });

    // Filter pencarian per tab — dipicu oleh button, bukan realtime
    window.doFilterTab = function (tabId) {
        const input = document.querySelector('.filter-produk[data-tab="' + tabId + '"]');
        const grid  = document.getElementById('grid-' + tabId);
        if (! input || ! grid) return;

        const q     = input.value.trim().toLowerCase();
        const pane  = grid.closest('.tab-pane');
        const empty = pane ? pane.querySelector('.empty-filter') : null;

        // Ambil filter formasi global
        const formasiId = document.getElementById('filterFormasi') ? document.getElementById('filterFormasi').value : '';
        const katFormasiId = document.getElementById('filterKategoriFormasi') ? document.getElementById('filterKategoriFormasi').value : '';

        let visible = 0;
        grid.querySelectorAll('.produk-item').forEach(function (el) {
            const nama = el.dataset.nama || '';
            const elFormasiId = el.dataset.formasiId || '';
            const elKatFormasiId = el.dataset.kategoriFormasiId || '';

            let show = true;

            // Filter nama
            if (q && !nama.includes(q)) show = false;

            // Filter formasi spesifik
            if (show && formasiId && elFormasiId !== formasiId) show = false;

            // Filter kategori formasi (jika formasi spesifik tidak dipilih)
            if (show && !formasiId && katFormasiId && elKatFormasiId !== katFormasiId) show = false;

            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (empty) {
            empty.classList.toggle('d-none', visible > 0);
            grid.classList.toggle('d-none', visible === 0);
        }
    };

    window.resetFilterTab = function (tabId) {
        const input = document.querySelector('.filter-produk[data-tab="' + tabId + '"]');
        if (input) {
            input.value = '';
            input.focus();
        }
        const grid = document.getElementById('grid-' + tabId);
        if (grid) {
            grid.querySelectorAll('.produk-item').forEach(el => el.style.display = '');
            grid.classList.remove('d-none');
        }
        const pane  = grid ? grid.closest('.tab-pane') : null;
        const empty = pane ? pane.querySelector('.empty-filter') : null;
        if (empty) empty.classList.add('d-none');
    };
}());

// ── Filter Formasi Global ──
(function () {
    const katFormasiSelect = document.getElementById('filterKategoriFormasi');
    const formasiSelect    = document.getElementById('filterFormasi');
    const resetBtn         = document.getElementById('resetFilterFormasi');
    const filterCard       = document.getElementById('filterFormasiCard');

    if (!katFormasiSelect || !formasiSelect || !filterCard) return;

    // ID kategori yang memerlukan filter formasi (SKB saja)
    const kategoriWithFormasi = <?= json_encode(array_map('intval', $kategoriWithFormasiIds)) ?>;

    // Simpan semua option formasi asli sebelum Select2 mengubah DOM
    const allFormasiData = [];
    formasiSelect.querySelectorAll('option[data-kategori]').forEach(opt => {
        allFormasiData.push({ id: opt.value, text: opt.textContent, kategori: opt.getAttribute('data-kategori') });
    });

    // Show/hide filter berdasarkan tab aktif
    function toggleFilterByTab() {
        const activeBtn = document.querySelector('#katalogTab .nav-link.active');
        if (!activeBtn) {
            filterCard.style.display = 'none';
            return;
        }
        const katId = parseInt(activeBtn.dataset.katId) || 0;
        const show = kategoriWithFormasi.includes(katId);
        filterCard.style.display = show ? '' : 'none';

        // Reset filter saat pindah ke tab non-SKB
        if (!show) {
            $('#filterKategoriFormasi').val(null).trigger('change');
            $('#filterFormasi').val(null).trigger('change');
            // Reset produk visibility
            document.querySelectorAll('.produk-item').forEach(el => el.style.display = '');
            document.querySelectorAll('.produk-grid').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.empty-filter').forEach(el => el.classList.add('d-none'));
        }
    }

    // Listen tab change
    document.querySelectorAll('#katalogTab .nav-link').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            toggleFilterByTab();
        });
    });

    // Rebuild formasi options berdasarkan kategori yang dipilih
    function rebuildFormasiOptions(selectedKfId) {
        const $formasi = $('#filterFormasi');
        $formasi.empty();
        $formasi.append(new Option('Semua Formasi', '', true, true));

        allFormasiData.forEach(function (f) {
            if (!selectedKfId || f.kategori === selectedKfId) {
                $formasi.append(new Option(f.text, f.id, false, false));
            }
        });

        $formasi.trigger('change');
    }

    function applyFormasiFilter() {
        const formasiId = $('#filterFormasi').val() || '';
        const katFormasiId = $('#filterKategoriFormasi').val() || '';

        // Filter produk di tab aktif
        const activeTab = document.querySelector('.tab-pane.show.active');
        if (!activeTab) return;

        const grid = activeTab.querySelector('.produk-grid');
        if (!grid) return;

        const empty = activeTab.querySelector('.empty-filter');
        const searchInput = activeTab.querySelector('.filter-produk');
        const q = searchInput ? searchInput.value.trim().toLowerCase() : '';

        let visible = 0;
        grid.querySelectorAll('.produk-item').forEach(function (el) {
            const nama = el.dataset.nama || '';
            const elFormasiId = el.dataset.formasiId || '';
            const elKatFormasiId = el.dataset.kategoriFormasiId || '';

            let show = true;

            // Filter nama
            if (q && !nama.includes(q)) show = false;

            // Filter formasi spesifik
            if (show && formasiId && elFormasiId !== formasiId) show = false;

            // Filter kategori formasi (jika formasi spesifik tidak dipilih)
            if (show && !formasiId && katFormasiId && elKatFormasiId !== katFormasiId) show = false;

            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (empty) {
            empty.classList.toggle('d-none', visible > 0);
            grid.classList.toggle('d-none', visible === 0);
        }
    }

    // Inisialisasi Select2
    $('#filterKategoriFormasi').select2({
        theme: 'bootstrap-5',
        placeholder: 'Semua Kategori Formasi',
        allowClear: true,
        width: '100%',
    });

    $('#filterFormasi').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik untuk cari formasi...',
        allowClear: true,
        width: '100%',
    });

    // Event: kategori formasi berubah → rebuild formasi dropdown
    $('#filterKategoriFormasi').on('change', function () {
        const val = $(this).val() || '';
        rebuildFormasiOptions(val);
        applyFormasiFilter();
    });

    // Event: formasi berubah → filter produk
    $('#filterFormasi').on('change', function () {
        applyFormasiFilter();
    });

    // Reset
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            $('#filterKategoriFormasi').val(null).trigger('change');
            $('#filterFormasi').val(null).trigger('change');
            rebuildFormasiOptions('');

            // Reset semua produk
            document.querySelectorAll('.produk-item').forEach(el => el.style.display = '');
            document.querySelectorAll('.produk-grid').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.empty-filter').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.filter-produk').forEach(input => input.value = '');
        });
    }

    // Initial state
    toggleFilterByTab();

    // ── Request Formasi Button ──
    document.querySelectorAll('.btn-request-formasi').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const formasiId   = $('#filterFormasi').val() || '';
            const formasiText = $('#filterFormasi option:selected').text().trim() || 'formasi yang dipilih';

            if (!formasiId) {
                alert('Silakan pilih formasi terlebih dahulu di filter.');
                return;
            }

            document.getElementById('requestFormasiId').value = formasiId;
            document.getElementById('requestFormasiNama').textContent = formasiText;
            new bootstrap.Modal(document.getElementById('modalRequestFormasi')).show();
        });
    });
}());
</script>

<?= $this->endSection() ?>
