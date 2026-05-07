<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold">
        <i class="bi bi-book-fill text-primary me-2"></i>
        Pembahasan: <?= esc($tryout['nama'] ?? 'Tryout') ?>
    </h4>
    <a href="<?= base_url('user/tryout/hasil/' . $sesi['id']) ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Hasil
    </a>
</div>

<?php if (empty($soalList)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Tidak ada soal yang tersedia untuk ditampilkan.
    </div>
<?php else: ?>

    <?php foreach ($soalList as $index => $soal): ?>
        <?php
            $nomorSoal   = $index + 1;
            $jawabanUser = $soal['jawaban_user'] ?? null;
            $kunci       = $soal['kunci_jawaban'] ?? null;
            $isBenar     = ($jawabanUser !== null && $jawabanUser === $kunci);
            $isKosong    = ($jawabanUser === null);

            // Status badge
            if ($isKosong) {
                $statusBadge = '<span class="badge bg-secondary">Tidak Dijawab</span>';
            } elseif ($isBenar) {
                $statusBadge = '<span class="badge bg-success">Benar</span>';
            } else {
                $statusBadge = '<span class="badge bg-danger">Salah</span>';
            }

            // Pilihan jawaban
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
                <?= $statusBadge ?>
            </div>
            <div class="card-body">

                <!-- Pertanyaan -->
                <div class="mb-3">
                    <?= nl2br(esc($soal['pertanyaan'])) ?>
                </div>

                <!-- Pilihan Jawaban -->
                <div class="mb-3">
                    <?php foreach ($pilihan as $huruf => $teks): ?>
                        <?php if ($teks === null || $teks === '') continue; ?>
                        <?php
                            $isUserAnswer   = ($jawabanUser === $huruf);
                            $isCorrectAnswer = ($kunci === $huruf);

                            if ($isCorrectAnswer && $isUserAnswer) {
                                $rowClass  = 'list-group-item-success';
                                $icon      = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                            } elseif ($isCorrectAnswer) {
                                $rowClass  = 'list-group-item-success';
                                $icon      = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                            } elseif ($isUserAnswer) {
                                $rowClass  = 'list-group-item-primary';
                                $icon      = '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
                            } else {
                                $rowClass  = '';
                                $icon      = '<i class="bi bi-circle text-muted me-2"></i>';
                            }
                        ?>
                        <div class="list-group-item <?= $rowClass ?> d-flex align-items-start py-2 px-3 mb-1 rounded border">
                            <?= $icon ?>
                            <span>
                                <strong class="text-uppercase"><?= $huruf ?>.</strong>
                                <?= esc($teks) ?>
                                <?php if ($isUserAnswer && !$isCorrectAnswer): ?>
                                    <span class="badge bg-primary ms-2">Jawaban Anda</span>
                                <?php elseif ($isUserAnswer && $isCorrectAnswer): ?>
                                    <span class="badge bg-success ms-2">Jawaban Anda ✓</span>
                                <?php elseif ($isCorrectAnswer): ?>
                                    <span class="badge bg-success ms-2">Jawaban Benar</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pembahasan -->
                <?php if (!empty($soal['pembahasan'])): ?>
                    <div class="alert alert-light border-start border-4 border-info mb-0">
                        <div class="fw-semibold text-info mb-1">
                            <i class="bi bi-lightbulb-fill me-1"></i> Pembahasan
                        </div>
                        <div class="text-dark">
                            <?= nl2br(esc($soal['pembahasan'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Pembahasan belum tersedia.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<div class="text-center mt-2 mb-4">
    <a href="<?= base_url('user/tryout/hasil/' . $sesi['id']) ?>"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Hasil
    </a>
</div>

<?= $this->endSection() ?>
