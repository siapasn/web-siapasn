<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div class="d-flex align-items-center gap-3">
    <div class="ph-icon"><i class="bi bi-book-fill"></i></div>
    <div>
        <div class="ph-title">Pembahasan</div>
        <div class="ph-subtitle"><?= esc($tryout['nama'] ?? 'Tryout') ?></div>
        <div class="ph-accent-line"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-3 d-flex justify-content-end">
    <a href="<?= base_url('user/tryout/hasil/' . $sesi['id']) ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Hasil
    </a>
</div>

<?php if (empty($soalList)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Tidak ada soal yang tersedia untuk ditampilkan.
    </div>
<?php else: ?>

    <?php foreach ($soalList as $index => $soal):
        $nomorSoal   = $index + 1;
        $jawabanUser = $soal['jawaban_user'] ?? null;
        $kunci       = $soal['kunci_jawaban'] ?? null;
        $tipeSoal    = $soal['sub_tipe_soal'] ?? 'POINT';
        $isKosong    = ($jawabanUser === null || $jawabanUser === '');

        // Nilai per pilihan untuk SCORE
        $nilaiMap = [
            'a' => (int)($soal['nilai_a'] ?? 0),
            'b' => (int)($soal['nilai_b'] ?? 0),
            'c' => (int)($soal['nilai_c'] ?? 0),
            'd' => (int)($soal['nilai_d'] ?? 0),
            'e' => (int)($soal['nilai_e'] ?? 0),
        ];

        // Cari pilihan dengan nilai tertinggi (untuk SCORE)
        $nilaiTertinggi = 0;
        $pilihanTerbaik = null;
        if ($tipeSoal === 'SCORE') {
            foreach ($nilaiMap as $huruf => $nilai) {
                if ($nilai > $nilaiTertinggi) {
                    $nilaiTertinggi = $nilai;
                    $pilihanTerbaik = $huruf;
                }
            }
        }

        // Status badge
        if ($tipeSoal === 'SCORE') {
            $nilaiDipilih = $isKosong ? 0 : ($nilaiMap[$jawabanUser] ?? 0);
            if ($isKosong) {
                $statusBadge = '<span class="badge bg-secondary">Tidak Dijawab</span>';
            } else {
                $statusBadge = '<span class="badge bg-info text-dark">Nilai: ' . $nilaiDipilih . '</span>';
            }
        } else {
            $isBenar = (! $isKosong && $jawabanUser === $kunci);
            if ($isKosong) {
                $statusBadge = '<span class="badge bg-secondary">Tidak Dijawab</span>';
            } elseif ($isBenar) {
                $statusBadge = '<span class="badge bg-success">Benar</span>';
            } else {
                $statusBadge = '<span class="badge bg-danger">Salah</span>';
            }
        }

        $pilihan = [
            'a' => $soal['pilihan_a'] ?? null,
            'b' => $soal['pilihan_b'] ?? null,
            'c' => $soal['pilihan_c'] ?? null,
            'd' => $soal['pilihan_d'] ?? null,
            'e' => $soal['pilihan_e'] ?? null,
        ];
    ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                <span class="fw-semibold">Soal <?= $nomorSoal ?></span>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($tipeSoal === 'SCORE'): ?>
                        <span class="badge bg-warning text-dark" style="font-size:.68rem">SCORE</span>
                    <?php else: ?>
                        <span class="badge bg-primary bg-opacity-75" style="font-size:.68rem">POINT</span>
                    <?php endif; ?>
                    <?= $statusBadge ?>
                </div>
            </div>
            <div class="card-body">

                <!-- Pertanyaan -->
                <div class="mb-4" style="font-size:1rem;line-height:1.7">
                    <?= $soal['pertanyaan'] ?>
                </div>

                <!-- Pilihan Jawaban -->
                <div class="mb-4">
                    <?php foreach ($pilihan as $huruf => $teks): ?>
                        <?php if ($teks === null || $teks === '') continue; ?>
                        <?php
                        $isUserAnswer = ($jawabanUser === $huruf);

                        if ($tipeSoal === 'SCORE') {
                            // SCORE: tidak ada benar/salah
                            $nilaiPilihan = $nilaiMap[$huruf] ?? 0;
                            $isTerbaik    = ($huruf === $pilihanTerbaik);

                            if ($isTerbaik && $isUserAnswer) {
                                // User pilih yang terbaik
                                $rowClass = 'border-success bg-success bg-opacity-10';
                                $icon     = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                                $badge    = '<span class="badge bg-success ms-2">Jawaban Anda ✓ Nilai: ' . $nilaiPilihan . '</span>';
                            } elseif ($isTerbaik) {
                                // Pilihan terbaik tapi bukan jawaban user
                                $rowClass = 'border-success bg-success bg-opacity-10';
                                $icon     = '<i class="bi bi-star-fill text-success me-2"></i>';
                                $badge    = '<span class="badge bg-success ms-2">Nilai Tertinggi: ' . $nilaiPilihan . '</span>';
                            } elseif ($isUserAnswer) {
                                // Jawaban user tapi bukan terbaik
                                $rowClass = 'border-primary bg-primary bg-opacity-10';
                                $icon     = '<i class="bi bi-check-circle-fill text-primary me-2"></i>';
                                $badge    = '<span class="badge bg-primary ms-2">Jawaban Anda · Nilai: ' . $nilaiPilihan . '</span>';
                            } else {
                                $rowClass = '';
                                $icon     = '<i class="bi bi-circle text-muted me-2"></i>';
                                $badge    = $nilaiPilihan > 0
                                    ? '<span class="badge bg-secondary bg-opacity-50 ms-2" style="font-size:.68rem">Nilai: ' . $nilaiPilihan . '</span>'
                                    : '';
                            }
                        } else {
                            // POINT: benar/salah
                            $isCorrect = ($kunci === $huruf);
                            if ($isCorrect && $isUserAnswer) {
                                $rowClass = 'border-success bg-success bg-opacity-10';
                                $icon     = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                                $badge    = '<span class="badge bg-success ms-2">Jawaban Anda ✓</span>';
                            } elseif ($isCorrect) {
                                $rowClass = 'border-success bg-success bg-opacity-10';
                                $icon     = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                                $badge    = '<span class="badge bg-success ms-2">Jawaban Benar</span>';
                            } elseif ($isUserAnswer) {
                                $rowClass = 'border-danger bg-danger bg-opacity-10';
                                $icon     = '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
                                $badge    = '<span class="badge bg-danger ms-2">Jawaban Anda</span>';
                            } else {
                                $rowClass = '';
                                $icon     = '<i class="bi bi-circle text-muted me-2"></i>';
                                $badge    = '';
                            }
                        }
                        ?>
                        <div class="d-flex align-items-start py-2 px-3 mb-2 rounded border <?= $rowClass ?>">
                            <?= $icon ?>
                            <span class="flex-grow-1">
                                <strong class="text-uppercase"><?= $huruf ?>.</strong>
                                <?= esc($teks) ?>
                                <?= $badge ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pembahasan -->
                <?php
                $pembahasanPlain = trim(strip_tags($soal['pembahasan'] ?? ''));
                if (! empty($pembahasanPlain)):
                ?>
                    <div class="alert alert-light border-start border-4 border-info mb-0">
                        <div class="fw-semibold text-info mb-1">
                            <i class="bi bi-lightbulb-fill me-1"></i>Pembahasan
                        </div>
                        <div class="text-dark"><?= $soal['pembahasan'] ?></div>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>Pembahasan belum tersedia.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<div class="text-center mt-2 mb-4">
    <a href="<?= base_url('user/tryout/hasil/' . $sesi['id']) ?>"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Hasil
    </a>
</div>

<?= $this->endSection() ?>
