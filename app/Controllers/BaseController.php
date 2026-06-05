<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 *
 * Validasi Input Server-Side:
 * Semua controller yang extend BaseController (Admin, SuperAdmin, User) menggunakan
 * CI4's built-in validation via $this->validate() / $this->validator.
 * Aturan validasi didefinisikan langsung di setiap method controller sebelum
 * memproses data, memastikan semua input tervalidasi di sisi server.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // --- Visitor Tracking ---
        $this->recordVisitor($request);

        // Content Security Policy — izinkan Midtrans Snap dan semua domain yang dibutuhkan
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'"
                . " https://app.sandbox.midtrans.com"
                . " https://app.midtrans.com"
                . " https://snap-assets.al-pc-id-b-cdn.gtflabs.io"
                . " https://api.sandbox.midtrans.com"
                . " https://api.midtrans.com"
                . " https://pay.google.com"
                . " https://gwk.gopayapi.com"
                . " https://cdn.jsdelivr.net"
                . " https://code.jquery.com"
                . " https://cdn.datatables.net"
                . " https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline'"
                . " https://cdn.jsdelivr.net"
                . " https://fonts.googleapis.com"
                . " https://cdn.datatables.net"
                . " https://cdnjs.cloudflare.com"
                . " https://snap-assets.al-pc-id-b-cdn.gtflabs.io",
            "font-src 'self' data:"
                . " https://fonts.gstatic.com"
                . " https://cdn.jsdelivr.net"
                . " https://cdn.datatables.net"
                . " https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob:"
                . " https://cdn.datatables.net"
                . " https://*.midtrans.com"
                . " https://*.gtflabs.io",
            "connect-src 'self'"
                . " https://api.sandbox.midtrans.com"
                . " https://api.midtrans.com"
                . " https://*.gopayapi.com"
                . " https://global.faro.katulampa.gopay.sh",
            "frame-src 'self'"
                . " https://app.sandbox.midtrans.com"
                . " https://app.midtrans.com"
                . " https://*.gtflabs.io",
            "worker-src blob:",
            "object-src 'none'",
        ]);

        $this->response->setHeader('Content-Security-Policy', $csp);
    }

    /**
     * Catat pengunjung unik per hari.
     * Hanya GET request pada halaman publik/user (bukan admin/superadmin/cron).
     */
    protected function recordVisitor(RequestInterface $request): void
    {
        try {
            // Hanya GET request
            if (strtolower($request->getMethod()) !== 'get') {
                return;
            }

            // Skip admin, superadmin, cron, webhook
            $uri = uri_string();
            $excludedPrefixes = ['admin', 'superadmin', 'cron', 'webhook'];
            foreach ($excludedPrefixes as $prefix) {
                if (str_starts_with($uri, $prefix)) {
                    return;
                }
            }

            // Skip bot/crawler
            $userAgent = $request->getUserAgent()->getAgentString() ?? '';
            if (empty($userAgent) || preg_match('/bot|crawl|spider|slurp|bingbot|googlebot/i', $userAgent)) {
                return;
            }

            $ipAddress = $request->getIPAddress();
            $today     = date('Y-m-d');

            $db = \Config\Database::connect();
            $db->query(
                "INSERT IGNORE INTO visitors (ip_address, user_agent, page_url, visited_at, created_at) VALUES (?, ?, ?, ?, ?)",
                [$ipAddress, mb_substr($userAgent, 0, 500), current_url(), $today, date('Y-m-d H:i:s')]
            );
        } catch (\Throwable $e) {
            log_message('error', '[Visitor] ' . $e->getMessage());
        }
    }
}
