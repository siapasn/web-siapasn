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

<?php
// Cek status launching
$db2        = \Config\Database::connect();
$cfgRows2   = $db2->table('master_aplikasi')
    ->whereIn('config_key', ['launch_date', 'launch_message'])
    ->get()->getResultArray();
$cfgMap2    = array_column($cfgRows2, 'config_value', 'config_key');
$launchDate2 = $cfgMap2['launch_date'] ?? '';
$launchMsg2  = $cfgMap2['launch_message'] ?? 'Pembelian paket tryout akan segera dibuka. Pantau terus halaman ini!';
$isLaunched2 = empty($launchDate2) || strtotime($launchDate2) <= time();
?>

<div class="mb-3">
    <a href="<?= base_url('user/produk') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
    </a>
    <?php
    $shareTitle = $produk['nama'];
    $shareUrl   = base_url('user/produk/' . ($produk['slug'] ?: $produk['id']));
    $shareText  = 'Paket Tryout CPNS - ' . $produk['nama'];
    echo view('partials/share-button', compact('shareTitle', 'shareUrl', 'shareText'));
    ?>
</div>

<div class="row g-4">
    <!-- Detail Produk -->
    <div class="col-lg-8">

        <!-- Info Formasi -->
        <?php if (! empty($formasiInfo)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:rgba(13,202,240,.1);flex-shrink:0">
                        <i class="<?= esc($formasiInfo['kategori_formasi_icon'] ?? 'bi-briefcase') ?>" style="font-size:1.2rem;color:#0dcaf0"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Formasi CPNS</div>
                        <div class="fw-semibold"><?= esc($formasiInfo['formasi_nama']) ?></div>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-size:.7rem">
                            <?= esc($formasiInfo['kategori_formasi_nama']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

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
        </div><!-- /card daftar tryout -->

        <!-- ── Materi Pelajaran (hanya tampil jika sudah beli & ada materi) ── -->
        <?php if ($sudahBeli && ! empty($materi)): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-book me-2 text-primary"></i>Materi Pelajaran</h6>
                <span class="badge bg-primary"><?= count($materi) ?> materi</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php
                    $ikonTipe = [
                        'Gambar'  => ['icon' => 'bi-image',        'color' => 'text-success',  'bg' => 'bg-success'],
                        'Video'   => ['icon' => 'bi-play-circle',  'color' => 'text-danger',   'bg' => 'bg-danger'],
                        'Dokumen' => ['icon' => 'bi-file-earmark-text', 'color' => 'text-warning', 'bg' => 'bg-warning'],
                    ];
                    ?>
                    <?php foreach ($materi as $i => $m): ?>
                        <?php
                        $tipe  = $m['tipe_file'];
                        $ikon  = $ikonTipe[$tipe] ?? ['icon' => 'bi-link-45deg', 'color' => 'text-secondary', 'bg' => 'bg-secondary'];

                        // Konversi URL admin serve → URL user serve
                        // url_file tersimpan sebagai: .../admin/master/datafile/{id}/serve
                        // User mengakses via: .../user/materi/{id}/file
                        $bukaUrl = $m['url_file']; // fallback ke URL asli jika bukan dari datafile
                        if (preg_match('#/admin/master/datafile/(\d+)/serve#', $m['url_file'], $matches)) {
                            $bukaUrl = base_url('user/materi/' . $matches[1] . '/file');
                        }
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center <?= $ikon['bg'] ?> bg-opacity-10"
                                     style="width:40px;height:40px;flex-shrink:0">
                                    <i class="bi <?= $ikon['icon'] ?> <?= $ikon['color'] ?>"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= esc($m['judul']) ?></div>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle" style="font-size:.7rem">
                                            <?= esc($tipe) ?>
                                        </span>
                                    </small>
                                </div>
                            </div>
                            <a href="<?= esc($bukaUrl) ?>" target="_blank" rel="noopener noreferrer"
                               class="btn btn-sm btn-outline-primary py-1 px-3 fw-semibold" style="font-size:.8rem;white-space:nowrap">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Ulasan & Penilaian ── -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-star-half me-2 text-warning"></i>Ulasan
                    <?php if ($avgRating > 0): ?>
                        <span class="ms-2 small text-muted"><?= $avgRating ?>/5 (<?= count($ulasans) ?> ulasan)</span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body">
                <!-- Form ulasan (hanya jika sudah beli & belum pernah review) -->
                <?php if ($sudahBeli && ! $hasReviewed): ?>
                <form method="post" action="<?= base_url('user/ulasan') ?>" class="mb-4 pb-3 border-bottom">
                    <?= csrf_field() ?>
                    <input type="hidden" name="produk_id" value="<?= $produk['id'] ?>">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Beri Rating:</label>
                        <div class="rating-stars d-flex gap-1" id="ratingStars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="bi bi-star fs-4 text-muted" data-value="<?= $s ?>" style="cursor:pointer"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="komentar" class="form-control form-control-sm" rows="3"
                                  placeholder="Tulis ulasan Anda tentang paket tryout ini... (opsional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitUlasan" disabled>
                        <i class="bi bi-send me-1"></i>Kirim Ulasan
                    </button>
                </form>
                <?php elseif ($sudahBeli && $hasReviewed): ?>
                <div class="alert alert-success py-2 mb-3 small">
                    <i class="bi bi-check-circle me-1"></i>Anda sudah memberikan ulasan untuk produk ini.
                </div>
                <?php endif; ?>

                <!-- Daftar ulasan -->
                <?php if (! empty($ulasans)): ?>
                    <?php foreach ($ulasans as $ul): ?>
                    <div class="d-flex gap-3 mb-3 pb-3 <?= $ul !== end($ulasans) ? 'border-bottom' : '' ?>">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small"><?= esc($ul['user_nama']) ?></span>
                                <span class="text-muted" style="font-size:.65rem"><?= date('d M Y', strtotime($ul['created_at'])) ?></span>
                            </div>
                            <div class="mb-1">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="bi bi-star<?= $s <= (int)$ul['rating'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size:.75rem"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if (! empty($ul['komentar'])): ?>
                                <p class="mb-0 small text-muted"><?= esc($ul['komentar']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted small py-2">
                        <i class="bi bi-chat-left-text me-1"></i>Belum ada ulasan.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-8 -->
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
                    <div class="alert alert-success py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Anda sudah memiliki akses ke paket ini.
                    </div>

                    <?php if (! empty($expiredAt)): ?>
                    <?php
                        $expiredTime = strtotime($expiredAt);
                        $sisaHari = (int) ceil(($expiredTime - time()) / 86400);
                    ?>
                    <div class="alert <?= $sisaHari <= 30 ? 'alert-warning' : 'alert-info' ?> py-2 mb-0">
                        <i class="bi bi-calendar-event me-1"></i>
                        <strong>Masa aktif:</strong> sampai <?= date('d M Y', $expiredTime) ?>
                        <?php if ($sisaHari <= 30): ?>
                            <br><small class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i><?= $sisaHari ?> hari lagi</small>
                        <?php elseif ($sisaHari <= 90): ?>
                            <br><small class="text-muted">(<?= $sisaHari ?> hari lagi)</small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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

                    <?php if ($isLaunched2): ?>
                    <a href="<?= base_url('user/transaksi/pilih-metode/' . $produk['id']) ?>"
                       class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-credit-card me-1"></i>Pilih Metode & Beli
                    </a>

                    <div class="mt-3 text-muted small">
                        <i class="bi bi-shield-check me-1 text-success"></i>Pembayaran aman via Midtrans
                    </div>
                    <?php else: ?>
                    <button class="btn btn-primary w-100 fw-semibold" disabled>
                        <i class="bi bi-lock me-1"></i>Pembelian Belum Dibuka
                    </button>
                    <div class="alert alert-warning py-2 mt-3 mb-0 small">
                        <i class="bi bi-clock me-1"></i>
                        <?= esc($launchMsg2) ?>
                        <div class="fw-semibold mt-1">
                            Aktif pada: <?= date('d M Y, H:i', strtotime($launchDate2)) ?> WIB
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const stars    = document.querySelectorAll('#ratingStars i');
    const input    = document.getElementById('ratingInput');
    const submitBtn = document.getElementById('btnSubmitUlasan');

    if (!stars.length || !input) return;

    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            const val = parseInt(this.dataset.value);
            input.value = val;
            if (submitBtn) submitBtn.disabled = false;

            stars.forEach(function (s, idx) {
                if (idx < val) {
                    s.classList.remove('bi-star', 'text-muted');
                    s.classList.add('bi-star-fill', 'text-warning');
                } else {
                    s.classList.remove('bi-star-fill', 'text-warning');
                    s.classList.add('bi-star', 'text-muted');
                }
            });
        });

        star.addEventListener('mouseenter', function () {
            const val = parseInt(this.dataset.value);
            stars.forEach(function (s, idx) {
                if (idx < val) {
                    s.classList.add('text-warning');
                }
            });
        });

        star.addEventListener('mouseleave', function () {
            const currentVal = parseInt(input.value) || 0;
            stars.forEach(function (s, idx) {
                if (idx >= currentVal) {
                    s.classList.remove('text-warning');
                }
            });
        });
    });
}());
</script>
<?= $this->endSection() ?>
