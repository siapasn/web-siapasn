<?php
/**
 * Partial: Share Button
 *
 * Variables:
 * - $shareTitle (required): Judul konten yang di-share
 * - $shareUrl (optional): URL yang di-share (default: current URL)
 * - $shareText (optional): Deskripsi singkat
 * - $shareBtnClass (optional): CSS class tambahan untuk tombol
 * - $shareBtnSize (optional): 'sm' atau 'lg' (default: 'sm')
 */
$shareUrl      = $shareUrl ?? current_url();
$shareText     = $shareText ?? '';
$shareBtnClass = $shareBtnClass ?? 'btn-outline-secondary';
$shareBtnSize  = $shareBtnSize ?? 'sm';
$shareId       = 'share-' . md5($shareUrl . microtime());
?>

<div class="dropdown d-inline-block">
    <button type="button"
            class="btn btn-<?= $shareBtnSize ?> <?= $shareBtnClass ?> dropdown-toggle"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            id="<?= $shareId ?>"
            title="Bagikan">
        <i class="bi bi-share me-1"></i>Bagikan
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="<?= $shareId ?>">
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('whatsapp','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-whatsapp text-success me-2"></i>WhatsApp
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('telegram','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-telegram text-info me-2"></i>Telegram
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('facebook','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-facebook text-primary me-2"></i>Facebook
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('twitter','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-twitter-x me-2"></i>X (Twitter)
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('copy','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-clipboard me-2"></i>Salin Link
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
               onclick="shareVia('native','<?= esc($shareTitle, 'js') ?>','<?= esc($shareUrl, 'js') ?>','<?= esc($shareText, 'js') ?>');return false">
                <i class="bi bi-box-arrow-up me-2"></i>Lainnya...
            </a>
        </li>
    </ul>
</div>

<script>
if (typeof window.shareVia === 'undefined') {
    window.shareVia = function(platform, title, url, text) {
        const fullText = text ? (title + ' - ' + text) : title;
        switch (platform) {
            case 'whatsapp':
                window.open('https://wa.me/?text=' + encodeURIComponent(fullText + '\n' + url), '_blank');
                break;
            case 'telegram':
                window.open('https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(fullText), '_blank');
                break;
            case 'facebook':
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
                break;
            case 'twitter':
                window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(fullText) + '&url=' + encodeURIComponent(url), '_blank');
                break;
            case 'copy':
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        showShareToast('Link berhasil disalin!');
                    });
                } else {
                    var temp = document.createElement('input');
                    document.body.appendChild(temp);
                    temp.value = url;
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                    showShareToast('Link berhasil disalin!');
                }
                break;
            case 'native':
                if (navigator.share) {
                    navigator.share({ title: title, text: text || title, url: url });
                } else {
                    // Fallback: copy
                    shareVia('copy', title, url, text);
                }
                break;
        }
    };

    window.showShareToast = function(msg) {
        var existing = document.getElementById('shareToast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'shareToast';
        toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1a3a5c;color:#fff;padding:.6rem 1.2rem;border-radius:2rem;font-size:.85rem;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.2);animation:fadeInUp .3s ease';
        toast.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + msg;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 2500);
    };
}
</script>
