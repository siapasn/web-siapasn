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
    border: 1px solid #e2e8f0 !important;
}
.buku-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12) !important;
    border-color: #c5d5f0 !important;
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
    font-weight: 600;
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

    <!-- Filter Pencarian -->
    <div class="d-flex justify-content-end mb-3 mt-2">
        <div class="search-box-wrapper">
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="filterBuku"
                       placeholder="Cari judul buku..."
                       autocomplete="off">
                <button type="button" class="search-btn" onclick="doFilterBuku()">
                    Cari
                </button>
                <button type="button" class="reset-btn" onclick="resetFilterBuku()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
    .search-box-wrapper {
        position: relative;
    }
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
    .search-box:focus-within input::placeholder {
        opacity: .5;
    }
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
    .search-box .search-btn:active {
        transform: scale(.97);
    }
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

    <!-- Grid Buku -->
    <div class="row g-3" id="bukuGrid">
        <?php foreach ($buku as $b): ?>
            <div class="col-6 col-md-4 col-lg-3 buku-item" data-judul="<?= strtolower(esc($b['judul'])) ?>">
                <div class="card border-0 shadow-sm h-100 buku-card">
                    <div class="thumb-wrap">
                        <img src="<?= esc($b['url_thumbnail'] ?? '') ?>"
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
                        <p class="judul-buku mb-2"><?= esc($b['judul']) ?></p>
                        <div class="mt-auto d-flex flex-column gap-2">
                            <?php if (! empty($b['url_shopee'])): ?>
                            <a href="<?= esc($b['url_shopee']) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="btn btn-sm w-100 fw-semibold"
                               style="background:linear-gradient(135deg,#ee4d2d,#ff6633);color:#fff;border:none;border-radius:.5rem">
                                <i class="bi bi-cart3 me-1"></i>Beli di Shopee
                            </a>
                            <?php endif; ?>
                            <?php
                            $shareTitle = $b['judul'];
                            $shareUrl   = ! empty($b['url_shopee']) ? $b['url_shopee'] : current_url();
                            $shareText  = 'Buku persiapan CPNS: ' . $b['judul'];
                            $shareBtnClass = 'btn-outline-secondary w-100';
                            echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText', 'shareBtnClass'));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty search state -->
    <div id="bukuEmpty" class="d-none text-center py-5 text-muted">
        <i class="bi bi-search fs-2 d-block mb-2"></i>
        <p class="mb-0">Tidak ada buku yang cocok dengan pencarian Anda.</p>
    </div>

    <!-- Pagination -->
    <nav id="bukuPagination" class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0"></ul>
    </nav>

<?php endif; ?>

<script>
(function () {
    const perPage   = 20;
    const grid      = document.getElementById('bukuGrid');
    const emptyEl   = document.getElementById('bukuEmpty');
    const pagNav    = document.querySelector('#bukuPagination ul');
    const input     = document.getElementById('filterBuku');

    if (!grid || !pagNav) return;

    let allItems    = Array.from(grid.querySelectorAll('.buku-item'));
    let filtered    = allItems;
    let currentPage = 1;

    function render() {
        const totalPages = Math.ceil(filtered.length / perPage);
        currentPage = Math.min(currentPage, Math.max(1, totalPages));

        // Hide all, show current page
        allItems.forEach(el => el.style.display = 'none');
        const start = (currentPage - 1) * perPage;
        const end   = start + perPage;
        filtered.slice(start, end).forEach(el => el.style.display = '');

        // Empty state
        if (filtered.length === 0) {
            grid.classList.add('d-none');
            emptyEl.classList.remove('d-none');
        } else {
            grid.classList.remove('d-none');
            emptyEl.classList.add('d-none');
        }

        // Pagination buttons
        pagNav.innerHTML = '';
        if (totalPages <= 1) return;

        // Prev
        const prevLi = document.createElement('li');
        prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
        prevLi.innerHTML = '<a class="page-link" href="#">&laquo;</a>';
        prevLi.addEventListener('click', function (e) { e.preventDefault(); if (currentPage > 1) { currentPage--; render(); } });
        pagNav.appendChild(prevLi);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item' + (i === currentPage ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#">' + i + '</a>';
            li.addEventListener('click', function (e) { e.preventDefault(); currentPage = i; render(); });
            pagNav.appendChild(li);
        }

        // Next
        const nextLi = document.createElement('li');
        nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
        nextLi.innerHTML = '<a class="page-link" href="#">&raquo;</a>';
        nextLi.addEventListener('click', function (e) { e.preventDefault(); if (currentPage < totalPages) { currentPage++; render(); } });
        pagNav.appendChild(nextLi);
    }

    window.doFilterBuku = function () {
        const q = input.value.trim().toLowerCase();
        filtered = allItems.filter(el => !q || el.dataset.judul.includes(q));
        currentPage = 1;
        render();
    };

    window.resetFilterBuku = function () {
        input.value = '';
        filtered = allItems;
        currentPage = 1;
        render();
        input.focus();
    };

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') doFilterBuku();
    });

    // Initial render
    render();
}());
</script>

<?= $this->endSection() ?>
