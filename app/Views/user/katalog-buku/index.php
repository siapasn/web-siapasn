<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-book"></i></div>
    <div>
        <div class="ph-title">Katalog Buku</div>
        <div class="ph-subtitle">Temukan buku terbaik untuk persiapan ujian Anda</div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.buku-card {
    transition: transform .18s ease, box-shadow .18s ease;
    border-radius: 1rem !important;
    overflow: hidden;
}
.buku-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12) !important;
}
.buku-card .thumb-wrap {
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}
.buku-card .thumb-wrap img {
    width: 100%; height: 100%;
    object-fit: contain;
    object-position: center;
    transition: transform .3s ease;
}
.buku-card:hover .thumb-wrap img { transform: scale(1.04); }
.buku-card .thumb-wrap .img-fallback {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: .8rem;
    text-align: center;
    padding: 1rem;
}
.buku-card .thumb-wrap .img-fallback i { font-size: 2.5rem; margin-bottom: .5rem; }
.buku-card .judul-buku {
    font-size: .88rem;
    font-weight: 500;
    line-height: 1.4;
    color: #1a3a5c;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php if (empty($buku)): ?>
    <div class="card border-0 shadow-sm rounded-3 mt-2">
        <div class="card-body text-center py-5 text-muted">
            <div class="mb-3" style="font-size:3rem">📚</div>
            <p class="mb-0">Belum ada buku yang tersedia saat ini.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mt-1">
        <?php foreach ($buku as $b): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 buku-card">
                    <div class="thumb-wrap">
                        <img src="<?= esc($b['url_thumbnail']) ?>"
                             alt="<?= esc($b['judul']) ?>"
                             referrerpolicy="no-referrer"
                             crossorigin="anonymous"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="img-fallback" style="display:none">
                            <i class="bi bi-book"></i>
                            <span>Gambar tidak tersedia</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <p class="judul-buku mb-3"><?= esc($b['judul']) ?></p>
                        <div class="mt-auto">
                            <a href="<?= esc($b['url_shopee']) ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-sm w-100 fw-semibold"
                               style="background: linear-gradient(135deg, #ee4d2d, #ff6633); color: #fff; border: none; border-radius: .5rem;">
                                <i class="bi bi-cart3 me-1"></i>Beli di Shopee
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
