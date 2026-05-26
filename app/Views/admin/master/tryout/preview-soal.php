<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Soal — <?= esc($tryout['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-size: .92rem; }
        .soal-card { border-left: 4px solid #1a3a5c; }
        .soal-card .kunci-badge { font-size: .75rem; }
        .pilihan-row { padding: .4rem .75rem; border-radius: .4rem; margin-bottom: .3rem; }
        .pilihan-row.correct { background: #d1fae5; border: 1px solid #86efac; }
        .pilihan-row.normal  { background: #f8f9fa; border: 1px solid #e9ecef; }
        .pembahasan-box { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: .4rem; }
        .header-sticky { position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 2px solid #1a3a5c; }
        @media print {
            .no-print { display: none !important; }
            .soal-card { break-inside: avoid; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header-sticky py-3 px-4 mb-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0" style="color:#1a3a5c">
                <i class="bi bi-journal-text me-2"></i><?= esc($tryout['nama']) ?>
            </h5>
            <small class="text-muted">
                <?= count($soalList) ?> soal &bull; Durasi: <?= (int) $tryout['durasi'] ?> menit
            </small>
        </div>
        <div class="no-print d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-x-lg me-1"></i>Tutup
            </button>
        </div>
    </div>
</div>

<div class="container-fluid px-4 pb-5">

    <?php if (empty($soalList)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p>Belum ada soal yang di-mapping ke tryout ini.</p>
        </div>
    <?php else: ?>

        <?php foreach ($soalList as $index => $soal):
            $nomor     = $index + 1;
            $kunci     = $soal['kunci_jawaban'] ?? null;
            $tipeSoal  = $soal['tipe_soal'] ?? 'POINT';

            // Fallback SCORE → POINT jika nilai_a–e semua NULL/0
            $nilaiMap = [
                'a' => (int)($soal['nilai_a'] ?? 0),
                'b' => (int)($soal['nilai_b'] ?? 0),
                'c' => (int)($soal['nilai_c'] ?? 0),
                'd' => (int)($soal['nilai_d'] ?? 0),
                'e' => (int)($soal['nilai_e'] ?? 0),
            ];
            if ($tipeSoal === 'SCORE') {
                $adaNilai = false;
                foreach ($nilaiMap as $n) { if ($n > 0) { $adaNilai = true; break; } }
                if (! $adaNilai) $tipeSoal = 'POINT';
            }

            $pilihan = [
                'a' => $soal['pilihan_a'] ?? null,
                'b' => $soal['pilihan_b'] ?? null,
                'c' => $soal['pilihan_c'] ?? null,
                'd' => $soal['pilihan_d'] ?? null,
                'e' => $soal['pilihan_e'] ?? null,
            ];
        ?>
            <div class="card border-0 shadow-sm soal-card mb-3">
                <div class="card-body">

                    <!-- Header soal -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary fw-bold" style="font-size:.8rem">
                                Soal <?= $nomor ?>
                            </span>
                            <?php if ($tipeSoal === 'SCORE'): ?>
                                <span class="badge bg-warning text-dark kunci-badge">SCORE</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark kunci-badge">POINT</span>
                            <?php endif; ?>
                            <?php if (! empty($soal['kategori_nama'])): ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border kunci-badge">
                                    <?= esc($soal['kategori_nama']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($kunci): ?>
                                <span class="badge bg-success kunci-badge">
                                    <i class="bi bi-key me-1"></i>Kunci: <?= strtoupper($kunci) ?>
                                </span>
                            <?php endif; ?>
                            <a href="<?= base_url('admin/master/soal/' . $soal['soal_id'] . '/edit') ?>"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary py-0 px-2 no-print"
                               title="Edit soal ini">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <div class="mb-3" style="line-height:1.7">
                        <?= $soal['pertanyaan'] ?>
                    </div>

                    <!-- Pilihan -->
                    <div class="mb-3">
                        <?php foreach ($pilihan as $huruf => $teks): ?>
                            <?php if ($teks === null || $teks === '') continue; ?>
                            <?php
                            $isCorrect = ($kunci === $huruf);
                            $rowClass  = $isCorrect ? 'correct' : 'normal';
                            ?>
                            <div class="pilihan-row <?= $rowClass ?> d-flex align-items-start gap-2">
                                <strong class="text-uppercase" style="min-width:20px"><?= $huruf ?>.</strong>
                                <span class="flex-grow-1"><?= esc($teks) ?></span>
                                <?php if ($tipeSoal === 'SCORE' && $nilaiMap[$huruf] > 0): ?>
                                    <span class="badge bg-warning text-dark" style="font-size:.68rem">
                                        Nilai: <?= $nilaiMap[$huruf] ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($isCorrect): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pembahasan -->
                    <?php
                    $pembahasanPlain = trim(strip_tags($soal['pembahasan'] ?? ''));
                    if (! empty($pembahasanPlain)):
                    ?>
                        <div class="pembahasan-box p-3">
                            <div class="fw-semibold text-primary mb-1" style="font-size:.82rem">
                                <i class="bi bi-lightbulb-fill me-1"></i>Pembahasan
                            </div>
                            <div style="font-size:.88rem;line-height:1.6"><?= $soal['pembahasan'] ?></div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>

        <!-- Footer -->
        <div class="text-center text-muted small mt-4 no-print">
            <i class="bi bi-info-circle me-1"></i>
            Total <?= count($soalList) ?> soal ditampilkan dari mapping tryout "<?= esc($tryout['nama']) ?>"
        </div>

    <?php endif; ?>

</div>

</body>
</html>
