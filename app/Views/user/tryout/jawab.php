<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center justify-content-between w-100">
    <div>
        <div class="ph-title"><?= esc($tryout['nama']) ?></div>
        <div class="ph-subtitle">Soal <?= $soalIndex ?> dari <?= $totalSoal ?></div>
        <div class="ph-accent-line"></div>
    </div>
    <!-- Timer -->
    <div id="timer-box"
         class="d-flex align-items-center gap-2 px-4 py-2 rounded-pill fw-bold"
         style="background:#d70e0e;border:2px solid rgba(220,53,69,.4);color:#fff;font-size:1.1rem;min-width:110px;justify-content:center"
         data-selesai-at="<?= $selesaiAt ?>"
         data-sesi-id="<?= (int) $sesi['id'] ?>">
        <i class="bi bi-clock"></i>
        <span id="timer-display">--:--</span>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php
$sesiId       = (int) $sesi['id'];
$jawabanPilih = $jawabanUser ? $jawabanUser['jawaban'] : null;
$csrfName     = csrf_token();
$csrfHash     = csrf_hash();
?>

<style>
/* ── Layout ── */
.jawab-wrap {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1rem;
    align-items: start;
}
@media (max-width: 768px) {
    .jawab-wrap { grid-template-columns: 1fr; }
    .nav-panel  { order: 2; }
    .soal-panel { order: 1; }
}

/* ── Navigasi soal ── */
.nav-panel {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #dee2e6 transparent;
}
.nav-panel::-webkit-scrollbar { width: 4px; }
.nav-panel::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 2px; }

/* ── Navigasi soal ── */
.nav-panel .nav-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 5px;
}
.nav-btn {
    aspect-ratio: 1;
    border-radius: .45rem;
    font-size: .78rem;
    font-weight: 600;
    border: 2px solid #dee2e6;
    background: #fff;
    color: #495057;
    cursor: pointer;
    transition: all .15s;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
}
.nav-btn:hover   { border-color: #1a3a5c; color: #1a3a5c; }
.nav-btn.aktif   { background: #1a3a5c; border-color: #1a3a5c; color: #fff; }
.nav-btn.dijawab { background: #198754; border-color: #198754; color: #fff; }

/* Mobile: navigasi lebih compact */
@media (max-width: 768px) {
    .nav-panel .nav-grid {
        grid-template-columns: repeat(8, 1fr);
        gap: 3px;
    }
    .nav-btn {
        aspect-ratio: 1;
        font-size: .65rem;
        border-radius: .3rem;
        border-width: 1.5px;
    }
    .nav-panel .card-header {
        padding: .4rem .75rem !important;
    }
}
@media (max-width: 480px) {
    .nav-panel .nav-grid {
        grid-template-columns: repeat(10, 1fr);
        gap: 2px;
    }
    .nav-btn {
        font-size: .6rem;
        border-radius: .25rem;
    }
}

/* ── Pilihan jawaban ── */
.pilihan-item {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .85rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: .65rem;
    cursor: pointer;
    transition: border-color .15s, background .15s, transform .1s;
    user-select: none;
    margin-bottom: .6rem;
}
.pilihan-item:hover {
    border-color: #1a3a5c;
    background: #f0f5ff;
    transform: translateX(3px);
}
.pilihan-item.selected {
    border-color: #1a3a5c;
    background: #e8f0fe;
}
.pilihan-item input[type="radio"] {
    display: none; /* sembunyikan radio asli */
}
.pilihan-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 2px solid #adb5bd;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem;
    flex-shrink: 0;
    transition: all .15s;
    color: #6c757d;
}
.pilihan-item.selected .pilihan-circle {
    background: #1a3a5c;
    border-color: #1a3a5c;
    color: #fff;
}
.pilihan-teks {
    flex-grow: 1;
    font-size: .95rem;
    line-height: 1.5;
    color: #212529;
}

/* ── Pertanyaan ── */
.pertanyaan-box {
    background: #fff;
    border-radius: .75rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    margin-bottom: 1.25rem;
    font-size: 1rem;
    line-height: 1.7;
    color: #212529;
}

/* ── Progress bar soal ── */
.soal-progress {
    height: 4px;
    border-radius: 2px;
    background: #e9ecef;
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.soal-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #1a3a5c, #2d6a9f);
    border-radius: 2px;
    transition: width .3s ease;
}

/* ── Save indicator ── */
#save-indicator {
    font-size: .78rem;
    color: #198754;
    opacity: 0;
    transition: opacity .3s;
}
#save-indicator.show { opacity: 1; }
</style>

<div class="jawab-wrap">

    <!-- ── Panel Navigasi ── -->
    <div class="nav-panel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2">
                <small class="fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                    Navigasi Soal
                </small>
            </div>
            <div class="card-body p-2">
                <div class="nav-grid">
                    <?php foreach ($soalList as $idx => $s):
                        $nomor   = $idx + 1;
                        $dijawab = isset($soalDijawab[$s['soal_id']]) && $soalDijawab[$s['soal_id']] !== null;
                        $aktif   = ($nomor === $soalIndex);
                        $cls     = $aktif ? 'aktif' : ($dijawab ? 'dijawab' : '');
                    ?>
                        <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . $nomor) ?>"
                           class="nav-btn <?= $cls ?> nav-soal-btn" title="Soal <?= $nomor ?>">
                            <?= $nomor ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3 d-flex flex-column gap-1" style="font-size:.72rem">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:14px;height:14px;border-radius:3px;background:#198754;display:inline-block"></span>
                        <span class="text-muted">Dijawab</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:14px;height:14px;border-radius:3px;background:#1a3a5c;display:inline-block"></span>
                        <span class="text-muted">Soal ini</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:14px;height:14px;border-radius:3px;background:#fff;border:2px solid #dee2e6;display:inline-block"></span>
                        <span class="text-muted">Belum</span>
                    </div>
                </div>

                <!-- Ringkasan -->
                <?php
                $totalDijawab = count(array_filter($soalDijawab, fn($v) => $v !== null));
                ?>
                <div class="mt-3 pt-2 border-top text-center" style="font-size:.75rem">
                    <div class="fw-bold text-success"><?= $totalDijawab ?> / <?= $totalSoal ?></div>
                    <div class="text-muted">soal dijawab</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Panel Soal ── -->
    <div class="soal-panel">

        <!-- Progress bar -->
        <div class="soal-progress">
            <div class="soal-progress-bar" style="width:<?= round(($soalIndex / $totalSoal) * 100) ?>%"></div>
        </div>

        <!-- Pertanyaan -->
        <div class="pertanyaan-box shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge px-3 py-2 fw-semibold" style="background:#1a3a5c;font-size:.8rem">
                    Soal <?= $soalIndex ?>
                </span>
                <span id="save-indicator">
                    <i class="bi bi-check-circle-fill me-1"></i>Tersimpan
                </span>
            </div>
            <div style="font-size:1.1rem;line-height:1.75">
                <?= $soalSaatIni['pertanyaan'] ?>
            </div>
        </div>

        <!-- Pilihan Jawaban -->
        <form id="form-jawab">
            <input type="hidden" id="sesi_id"   value="<?= $sesiId ?>">
            <input type="hidden" id="soal_id"   value="<?= (int) $soalSaatIni['soal_id'] ?>">
            <input type="hidden" id="csrf_name" value="<?= $csrfName ?>">
            <input type="hidden" id="csrf_hash" value="<?= $csrfHash ?>">

            <?php
            $pilihan = [
                'a' => $soalSaatIni['pilihan_a'],
                'b' => $soalSaatIni['pilihan_b'],
                'c' => $soalSaatIni['pilihan_c'],
                'd' => $soalSaatIni['pilihan_d'],
                'e' => $soalSaatIni['pilihan_e'],
            ];
            ?>

            <?php foreach ($pilihan as $key => $teks): ?>
                <?php if (! empty($teks)): ?>
                    <label class="pilihan-item <?= ($jawabanPilih === $key) ? 'selected' : '' ?>"
                           for="pilihan_<?= $key ?>">
                        <input class="pilihan-radio"
                               type="radio"
                               name="jawaban"
                               id="pilihan_<?= $key ?>"
                               value="<?= $key ?>"
                               <?= ($jawabanPilih === $key) ? 'checked' : '' ?>>
                        <div class="pilihan-circle"><?= strtoupper($key) ?></div>
                        <div class="pilihan-teks"><?= $teks ?></div>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </form>

        <!-- Navigasi prev/next + Selesai -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="d-flex gap-2">
                <?php if ($soalIndex > 1): ?>
                    <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . ($soalIndex - 1)) ?>"
                       class="btn btn-outline-secondary nav-soal-btn">
                        <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                    </a>
                <?php endif; ?>
                <?php if ($soalIndex < $totalSoal): ?>
                    <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . ($soalIndex + 1)) ?>"
                       class="btn btn-primary nav-soal-btn">
                        Selanjutnya<i class="bi bi-chevron-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>

            <form id="form-selesai" method="post"
                  action="<?= base_url('user/tryout/selesai/' . $sesiId) ?>">
                <?= csrf_field() ?>
                <button type="button" class="btn btn-danger fw-semibold"
                        onclick="konfirmasiSelesai()">
                    <i class="bi bi-flag-fill me-1"></i>Selesai
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Modal Konfirmasi Selesai -->
<div class="modal fade" id="modalSelesai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-flag-fill me-2 text-danger"></i>Akhiri Tryout?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong><?= $totalDijawab ?></strong> dari <strong><?= $totalSoal ?></strong> soal telah dijawab.
                </div>
                <p class="text-muted mb-0">Setelah dikonfirmasi, Anda akan diarahkan ke halaman hasil.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Lanjut Kerjakan
                </button>
                <button type="button" class="btn btn-danger fw-semibold" onclick="submitSelesai()">
                    <i class="bi bi-flag-fill me-1"></i>Ya, Selesaikan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Auto-save jawaban ─────────────────────────────────────────────────────────
(function () {
    const saveIndicator = document.getElementById('save-indicator');
    let   pendingNav    = null;   // URL tujuan navigasi yang sedang menunggu save
    let   isSaving      = false;

    function showSaved() {
        saveIndicator.classList.add('show');
        setTimeout(() => saveIndicator.classList.remove('show'), 2000);
    }

    function getJawabanTerpilih() {
        const radio = document.querySelector('.pilihan-radio:checked');
        return radio ? radio.value : null;
    }

    /**
     * Simpan jawaban ke server.
     * @param {string|null} jawaban  - nilai pilihan (a/b/c/d/e) atau null
     * @param {Function}    callback - dipanggil setelah selesai (berhasil/gagal)
     */
    function simpanJawaban(jawaban, callback) {
        const fd = new FormData();
        fd.append('sesi_id', document.getElementById('sesi_id').value);
        fd.append('soal_id', document.getElementById('soal_id').value);
        if (jawaban !== null) fd.append('jawaban', jawaban);
        fd.append(document.getElementById('csrf_name').value,
                  document.getElementById('csrf_hash').value);

        isSaving = true;
        fetch('<?= base_url('user/tryout/simpan-jawaban') ?>', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            isSaving = false;
            if (d.status) showSaved();
            if (callback) callback();
        })
        .catch(() => {
            isSaving = false;
            if (callback) callback(); // tetap navigasi meski gagal
        });
    }

    // Klik seluruh baris pilihan — simpan langsung
    document.querySelectorAll('.pilihan-item').forEach(function (item) {
        item.addEventListener('click', function () {
            document.querySelectorAll('.pilihan-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                simpanJawaban(radio.value, null);
            }
        });
    });

    // Scroll panel navigasi agar tombol soal aktif selalu terlihat saat halaman load
    (function () {
        const navPanel  = document.querySelector('.nav-panel');
        const aktifBtn  = navPanel ? navPanel.querySelector('.nav-btn.aktif') : null;
        if (navPanel && aktifBtn) {
            // Hitung posisi tombol relatif terhadap panel, lalu scroll ke tengah
            const panelTop    = navPanel.getBoundingClientRect().top;
            const btnTop      = aktifBtn.getBoundingClientRect().top;
            const offset      = btnTop - panelTop;
            const panelHeight = navPanel.clientHeight;
            const btnHeight   = aktifBtn.clientHeight;
            navPanel.scrollTop = offset - (panelHeight / 2) + (btnHeight / 2);
        }
    }());

    // Intercept semua link navigasi soal (Sebelumnya, Selanjutnya, nav panel)
    document.querySelectorAll('.nav-soal-btn').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const tujuan = this.href;
            const isNavBtn = this.classList.contains('nav-btn'); // tombol nomor di panel

            // Feedback visual langsung — disable & tampilkan spinner
            if (isNavBtn) {
                // Panel nomor: ubah isi jadi spinner kecil
                this.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:.7rem;height:.7rem"></span>';
                this.style.pointerEvents = 'none';
            } else {
                // Tombol Sebelumnya / Selanjutnya
                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.85rem;height:.85rem"></span> Menyimpan...';
                this.classList.add('disabled');
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.75';
            }

            // Disable semua nav btn lain agar tidak double-click
            document.querySelectorAll('.nav-soal-btn').forEach(b => {
                if (b !== this) {
                    b.style.pointerEvents = 'none';
                    b.style.opacity = '0.5';
                }
            });

            const jawaban = getJawabanTerpilih();
            simpanJawaban(jawaban, function () {
                window.location.href = tujuan;
            });
        });
    });

}());

// ── Countdown timer ───────────────────────────────────────────────────────────
(function () {
    const box     = document.getElementById('timer-box');
    const display = document.getElementById('timer-display');
    if (! box || ! display) return;

    const selesaiAt = parseInt(box.getAttribute('data-selesai-at'), 10) * 1000;

    function tick() {
        const sisa = selesaiAt - Date.now();
        if (sisa <= 0) {
            display.textContent = '00:00';
            document.getElementById('form-selesai').submit();
            return;
        }
        const m = Math.floor(sisa / 60000);
        const s = Math.floor((sisa % 60000) / 1000);
        display.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

        // Warna timer
        const detik = Math.floor(sisa / 1000);
        if (detik < 60) {
            box.style.background = 'rgba(220,53,69,.3)';
            box.style.borderColor = 'rgba(220,53,69,.8)';
        } else if (detik < 300) {
            box.style.background = 'rgba(255,193,7,.2)';
            box.style.borderColor = 'rgba(255,193,7,.6)';
            box.style.color = '#fff';
        }
        setTimeout(tick, 1000);
    }
    tick();
}());

// ── Konfirmasi selesai ────────────────────────────────────────────────────────
function konfirmasiSelesai() {
    new bootstrap.Modal(document.getElementById('modalSelesai')).show();
}
function submitSelesai() {
    document.getElementById('form-selesai').submit();
}
</script>

<?= $this->endSection() ?>
