<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tryout — <?= esc($user['nama'] ?? 'Peserta') ?> | SiapASN</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <!-- OG Meta untuk preview saat share -->
    <meta property="og:title" content="Hasil Tryout <?= esc($tryout['nama'] ?? '') ?> — <?= esc($user['nama'] ?? '') ?>">
    <meta property="og:description" content="Total Nilai: <?= (int) $hasil['total_nilai'] ?> | Status: <?= $hasil['status_lulus'] === 'lulus' ? 'LULUS ✓' : 'Belum Lulus' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }
        .share-card {
            max-width: 520px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }
        .share-header {
            background: linear-gradient(135deg, #0f2744, #1a3a5c);
            padding: 1.5rem 2rem;
            color: #fff;
            text-align: center;
        }
        .share-header h1 { font-size: 1.1rem; font-weight: 700; margin: 0; }
        .share-header .sub { font-size: .8rem; color: rgba(255,255,255,.7); margin-top: .25rem; }
        .share-body { padding: 1.5rem 2rem; }
        .score-big { font-size: 3rem; font-weight: 800; color: #1a3a5c; text-align: center; }
        .score-label { text-align: center; color: #6c757d; font-size: .85rem; }
        .status-badge { text-align: center; margin: 1rem 0; }
        .kat-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid #f0f0f0; }
        .kat-row:last-child { border-bottom: none; }
        .kat-name { font-size: .85rem; font-weight: 600; color: #333; }
        .kat-value { font-size: .85rem; font-weight: 700; }
        .share-footer { background: #f8fafc; padding: 1rem 2rem; text-align: center; border-top: 1px solid #e9ecef; }
        .share-footer .brand { font-weight: 700; color: #1a3a5c; font-size: .9rem; }
        .share-footer .brand span { color: #f5a623; }
        .share-actions { max-width: 520px; margin: 0 auto 2rem; }
    </style>
</head>
<body>

<!-- Card yang akan di-capture sebagai gambar -->
<div id="shareCard" class="share-card">
    <div class="share-header">
        <h1><i class="bi bi-trophy-fill me-2" style="color:#f5a623"></i>Hasil Tryout <?= $tipeTryout ?></h1>
        <div class="sub"><?= esc($tryout['nama'] ?? 'Tryout') ?></div>
    </div>
    <div class="share-body">
        <!-- Nama peserta -->
        <div class="text-center mb-3">
            <div class="fw-semibold" style="color:#1a3a5c"><?= esc($user['nama'] ?? 'Peserta') ?></div>
            <div class="text-muted small"><?= date('d M Y', strtotime($hasil['created_at'])) ?></div>
        </div>

        <!-- Skor -->
        <div class="score-big"><?= (int) $hasil['total_nilai'] ?></div>
        <div class="score-label">Total Poin<?php if ((int) $hasil['max_nilai'] > 0): ?> / <?= (int) $hasil['max_nilai'] ?><?php endif; ?></div>

        <!-- Status -->
        <div class="status-badge">
            <?php if ($hasil['status_lulus'] === 'lulus'): ?>
                <span class="badge bg-success px-3 py-2 fs-6"><i class="bi bi-patch-check-fill me-1"></i>LULUS</span>
            <?php elseif ($hasil['status_lulus'] === 'tidak_lulus'): ?>
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="bi bi-x-circle-fill me-1"></i>TIDAK LULUS</span>
            <?php else: ?>
                <span class="badge bg-secondary px-3 py-2">Selesai</span>
            <?php endif; ?>
        </div>

        <!-- Detail per kategori -->
        <?php if (! empty($detailKategori)): ?>
        <div class="mt-3 pt-3 border-top">
            <?php foreach ($detailKategori as $kat):
                $totalNilaiKat = (int) ($kat['total_nilai'] ?? 0);
                $pgStatus = null;
                foreach ($detailPg as $pg) {
                    if ((int)($pg['sub_kategori_id'] ?? 0) === (int)($kat['kategori_id'] ?? 0) ||
                        (int)($pg['kategori_id'] ?? 0) === (int)($kat['kategori_id'] ?? 0)) {
                        $pgStatus = $pg['lulus'] ?? null;
                        break;
                    }
                }
            ?>
                <div class="kat-row">
                    <div class="kat-name">
                        <?= esc($kat['sub_kategori_nama'] ?? $kat['kategori_nama'] ?? 'Kategori') ?>
                        <?php if ($pgStatus === true): ?>
                            <i class="bi bi-check-circle-fill text-success ms-1" style="font-size:.75rem"></i>
                        <?php elseif ($pgStatus === false): ?>
                            <i class="bi bi-x-circle-fill text-danger ms-1" style="font-size:.75rem"></i>
                        <?php endif; ?>
                    </div>
                    <div class="kat-value" style="color:<?= $pgStatus === true ? '#198754' : ($pgStatus === false ? '#dc3545' : '#1a3a5c') ?>">
                        <?= $totalNilaiKat ?> poin
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="share-footer">
        <div class="brand"><i class="bi bi-mortarboard-fill me-1" style="color:#f5a623"></i>SiapASN <span>Simulation Center</span></div>
        <div class="text-muted" style="font-size:.7rem">Platform Tryout CPNS & PPPK Terpercaya</div>
    </div>
</div>

<!-- Tombol Share (tidak di-capture) -->
<div class="share-actions text-center">
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <button class="btn btn-success btn-sm" id="btnDownload">
            <i class="bi bi-download me-1"></i>Download Gambar
        </button>
        <a href="https://wa.me/?text=<?= urlencode('Hasil Tryout ' . ($tryout['nama'] ?? '') . ': Total Nilai ' . $hasil['total_nilai'] . ' — ' . ($hasil['status_lulus'] === 'lulus' ? 'LULUS ✓' : 'Lihat hasilnya') . ' ' . current_url()) ?>"
           target="_blank" class="btn btn-sm" style="background:#25D366;color:#fff">
            <i class="bi bi-whatsapp me-1"></i>WhatsApp
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Hasil Tryout ' . ($tryout['nama'] ?? '') . ': Total Nilai ' . $hasil['total_nilai'] . ' 🎯 ' . ($hasil['status_lulus'] === 'lulus' ? 'LULUS ✓' : '') . ' #CPNS #SiapASN') ?>&url=<?= urlencode(current_url()) ?>"
           target="_blank" class="btn btn-sm" style="background:#1DA1F2;color:#fff">
            <i class="bi bi-twitter-x me-1"></i>Twitter
        </a>
        <button class="btn btn-outline-secondary btn-sm" id="btnCopyLink">
            <i class="bi bi-link-45deg me-1"></i>Copy Link
        </button>
    </div>
    <div id="copyFeedback" class="text-success small mt-2" style="display:none">
        <i class="bi bi-check-circle me-1"></i>Link berhasil disalin!
    </div>
</div>

<script>
// Download sebagai gambar
document.getElementById('btnDownload').addEventListener('click', function () {
    const card = document.getElementById('shareCard');
    html2canvas(card, { scale: 2, useCORS: true }).then(function (canvas) {
        const link = document.createElement('a');
        link.download = 'hasil-tryout-siapasn.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
});

// Copy link
document.getElementById('btnCopyLink').addEventListener('click', function () {
    navigator.clipboard.writeText(window.location.href).then(function () {
        document.getElementById('copyFeedback').style.display = '';
        setTimeout(function () {
            document.getElementById('copyFeedback').style.display = 'none';
        }, 3000);
    });
});
</script>

</body>
</html>
