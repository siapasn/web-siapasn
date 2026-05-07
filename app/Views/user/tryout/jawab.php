<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $tryoutDurasi  = (int) $tryout['durasi'];
    $sesiMulaiAt   = $sesi['mulai_at'];
    $sesiId        = (int) $sesi['id'];
    $tryoutId      = (int) $sesi['tryout_id'];
    $jawabanPilih  = $jawabanUser ? $jawabanUser['jawaban'] : null;
    $csrfName      = csrf_token();
    $csrfHash      = csrf_hash();
?>

<!-- Timer bar (sticky top) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-journal-text me-2"></i><?= esc($tryout['nama']) ?>
    </h5>
    <div class="d-flex align-items-center gap-3">
        <div id="timer-box"
             class="badge bg-danger fs-6 px-3 py-2"
             data-selesai-at="<?= $selesaiAt ?>"
             data-sesi-id="<?= $sesiId ?>">
            <i class="bi bi-clock me-1"></i>
            <span id="timer-display">--:--</span>
        </div>
    </div>
</div>

<div class="row g-3">

    <!-- Navigasi soal (kiri) -->
    <div class="col-md-3 col-lg-2">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <small class="fw-semibold text-muted">Navigasi Soal</small>
            </div>
            <div class="card-body p-2">
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($soalList as $idx => $s): ?>
                        <?php
                            $nomor    = $idx + 1;
                            $soalIdNv = $s['soal_id'];
                            $dijawab  = isset($soalDijawab[$soalIdNv]) && $soalDijawab[$soalIdNv] !== null;
                            $aktif    = ($nomor === $soalIndex);
                            $btnClass = $aktif ? 'btn-primary' : ($dijawab ? 'btn-success' : 'btn-outline-secondary');
                        ?>
                        <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . $nomor) ?>"
                           class="btn btn-sm <?= $btnClass ?> nav-soal-btn"
                           data-nomor="<?= $nomor ?>"
                           style="width:36px;height:36px;padding:0;line-height:36px;text-align:center;">
                            <?= $nomor ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2 small text-muted">
                    <span class="badge bg-success me-1">&nbsp;</span>Dijawab
                    <span class="badge bg-outline-secondary border me-1 ms-2">&nbsp;</span>Belum
                </div>
            </div>
        </div>
    </div>

    <!-- Area soal (kanan) -->
    <div class="col-md-9 col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold">Soal <?= $soalIndex ?> / <?= $totalSoal ?></span>
                <span id="save-indicator" class="text-success small" style="display:none;">
                    <i class="bi bi-check-circle me-1"></i>Tersimpan
                </span>
            </div>
            <div class="card-body">

                <!-- Pertanyaan -->
                <div class="mb-4">
                    <p class="fs-6"><?= nl2br(esc($soalSaatIni['pertanyaan'])) ?></p>
                </div>

                <!-- Pilihan jawaban -->
                <form id="form-jawab">
                    <input type="hidden" id="sesi_id" value="<?= $sesiId ?>">
                    <input type="hidden" id="soal_id" value="<?= (int) $soalSaatIni['soal_id'] ?>">
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
                        <?php if (!empty($teks)): ?>
                            <div class="form-check mb-3 p-3 border rounded <?= ($jawabanPilih === $key) ? 'border-primary bg-primary bg-opacity-10' : '' ?>">
                                <input class="form-check-input pilihan-radio"
                                       type="radio"
                                       name="jawaban"
                                       id="pilihan_<?= $key ?>"
                                       value="<?= $key ?>"
                                       <?= ($jawabanPilih === $key) ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="pilihan_<?= $key ?>">
                                    <strong><?= strtoupper($key) ?>.</strong> <?= esc($teks) ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>

            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">

                <!-- Navigasi prev/next -->
                <div class="d-flex gap-2">
                    <?php if ($soalIndex > 1): ?>
                        <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . ($soalIndex - 1)) ?>"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php if ($soalIndex < $totalSoal): ?>
                        <a href="<?= base_url('user/tryout/jawab/' . $sesiId . '?soal_index=' . ($soalIndex + 1)) ?>"
                           class="btn btn-outline-primary btn-sm">
                            Selanjutnya<i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Tombol selesai -->
                <form id="form-selesai"
                      method="post"
                      action="<?= base_url('user/tryout/selesai/' . $sesiId) ?>">
                    <?= csrf_field() ?>
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="konfirmasiSelesai()">
                        <i class="bi bi-flag-fill me-1"></i>Selesai
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

<!-- Modal konfirmasi selesai -->
<div class="modal fade" id="modalSelesai" tabindex="-1" aria-labelledby="modalSelesaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelesaiLabel">
                    <i class="bi bi-flag-fill me-2 text-danger"></i>Konfirmasi Selesai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengakhiri tryout ini?</p>
                <p class="text-muted small mb-0">Setelah dikonfirmasi, sesi tidak dapat dilanjutkan kembali.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitSelesai()">
                    <i class="bi bi-flag-fill me-1"></i>Ya, Selesaikan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// Auto-save jawaban via AJAX
// ============================================================
(function () {
    const radioButtons = document.querySelectorAll('.pilihan-radio');
    const saveIndicator = document.getElementById('save-indicator');

    function simpanJawaban(jawaban) {
        const sesiId   = document.getElementById('sesi_id').value;
        const soalId   = document.getElementById('soal_id').value;
        const csrfName = document.getElementById('csrf_name').value;
        const csrfHash = document.getElementById('csrf_hash').value;

        const formData = new FormData();
        formData.append('sesi_id', sesiId);
        formData.append('soal_id', soalId);
        formData.append('jawaban', jawaban);
        formData.append(csrfName, csrfHash);

        fetch('<?= base_url('user/tryout/simpan-jawaban') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status === true) {
                // Tampilkan indikator "Tersimpan"
                if (saveIndicator) {
                    saveIndicator.style.display = 'inline';
                    setTimeout(function () {
                        saveIndicator.style.display = 'none';
                    }, 2000);
                }
            }
        })
        .catch(function (err) {
            console.error('Gagal menyimpan jawaban:', err);
        });
    }

    radioButtons.forEach(function (radio) {
        radio.addEventListener('change', function () {
            simpanJawaban(this.value);
        });
    });
})();

// ============================================================
// Countdown timer dengan auto-submit saat waktu habis
// ============================================================
(function () {
    const timerBox     = document.getElementById('timer-box');
    const timerDisplay = document.getElementById('timer-display');

    if (!timerBox || !timerDisplay) return;

    const selesaiAt = parseInt(timerBox.getAttribute('data-selesai-at'), 10) * 1000; // ms

    function updateTimer() {
        const now      = Date.now();
        const sisaMs   = selesaiAt - now;

        if (sisaMs <= 0) {
            timerDisplay.textContent = '00:00';
            timerBox.classList.remove('bg-danger', 'bg-warning');
            timerBox.classList.add('bg-secondary');
            // Auto-submit form selesai
            autoSubmitSelesai();
            return;
        }

        const totalDetik = Math.floor(sisaMs / 1000);
        const menit      = Math.floor(totalDetik / 60);
        const detik      = totalDetik % 60;

        timerDisplay.textContent =
            String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');

        // Ubah warna saat sisa < 5 menit
        if (totalDetik < 300) {
            timerBox.classList.remove('bg-danger');
            timerBox.classList.add('bg-warning', 'text-dark');
        }
        // Ubah warna saat sisa < 1 menit
        if (totalDetik < 60) {
            timerBox.classList.remove('bg-warning', 'text-dark');
            timerBox.classList.add('bg-danger', 'text-white');
        }

        setTimeout(updateTimer, 1000);
    }

    function autoSubmitSelesai() {
        const formSelesai = document.getElementById('form-selesai');
        if (formSelesai) {
            formSelesai.submit();
        }
    }

    updateTimer();
})();

// ============================================================
// Konfirmasi selesai
// ============================================================
function konfirmasiSelesai() {
    const modal = new bootstrap.Modal(document.getElementById('modalSelesai'));
    modal.show();
}

function submitSelesai() {
    document.getElementById('form-selesai').submit();
}
</script>

<?= $this->endSection() ?>
