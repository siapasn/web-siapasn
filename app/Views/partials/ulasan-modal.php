<?php
$ulasanContext = $ulasanContext ?? [];
$modalId = $modalId ?? 'modalUlasan';
$ratingId = $ratingId ?? 'ratingInputUlasan';
$starsId = $starsId ?? 'ratingStarsUlasan';
$submitId = $submitId ?? 'btnSubmitUlasan';
$produk = $ulasanContext['produk'] ?? null;
?>

<?php if (! empty($produk) && ! empty($ulasanContext['can_review'])): ?>
<div class="modal fade" id="<?= esc($modalId) ?>" tabindex="-1" aria-labelledby="<?= esc($modalId) ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= base_url('user/ulasan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="produk_id" value="<?= (int) $produk['id'] ?>">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h6 class="modal-title fw-bold" id="<?= esc($modalId) ?>Label">
                            <i class="bi bi-star-half text-warning me-1"></i>Beri Ulasan
                        </h6>
                        <div class="text-muted small"><?= esc($produk['nama']) ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rating</label>
                        <div class="rating-stars d-flex gap-1" id="<?= esc($starsId) ?>">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="bi bi-star fs-3 text-muted" data-value="<?= $s ?>" style="cursor:pointer"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="<?= esc($ratingId) ?>" value="0" required>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Komentar</label>
                        <textarea name="komentar" class="form-control" rows="4" placeholder="Tulis pengalaman Anda setelah mengikuti tryout ini... (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="<?= esc($submitId) ?>" disabled>
                        <i class="bi bi-send me-1"></i>Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const stars = document.querySelectorAll('#<?= esc($starsId) ?> i');
    const input = document.getElementById('<?= esc($ratingId) ?>');
    const submitBtn = document.getElementById('<?= esc($submitId) ?>');

    if (!stars.length || !input) return;

    function paint(value) {
        stars.forEach(function (star, idx) {
            if (idx < value) {
                star.classList.remove('bi-star', 'text-muted');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star', 'text-muted');
            }
        });
    }

    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            const value = parseInt(this.dataset.value, 10) || 0;
            input.value = value;
            if (submitBtn) submitBtn.disabled = value < 1;
            paint(value);
        });
    });
}());
</script>
<?php endif; ?>
