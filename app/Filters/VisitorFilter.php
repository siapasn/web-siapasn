<?php

namespace App\Filters;

use App\Models\VisitorModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class VisitorFilter implements FilterInterface
{
    /**
     * Catat pengunjung unik per hari.
     * Hanya halaman publik & user yang di-track (bukan admin/superadmin/cron/webhook).
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip jika bukan GET request (form POST, API call, dll.)
        if ($request->getMethod() !== 'get') {
            return null;
        }

        // Skip route admin, superadmin, cron, webhook, file serve
        $uri = service('uri')->getPath();
        $excludedPrefixes = ['admin', 'superadmin', 'cron', 'webhook', 'file'];
        foreach ($excludedPrefixes as $prefix) {
            if (str_starts_with(ltrim($uri, '/'), $prefix)) {
                return null;
            }
        }

        // Skip bot/crawler umum
        $userAgent = $request->getUserAgent()->getAgentString();
        if ($this->isBot($userAgent)) {
            return null;
        }

        $ipAddress = $request->getIPAddress();
        $pageUrl   = current_url();

        try {
            $visitorModel = new VisitorModel();
            $visitorModel->recordVisit($ipAddress, $userAgent, $pageUrl);
        } catch (\Throwable $e) {
            // Jangan ganggu user experience jika tracking gagal
            log_message('error', '[VisitorFilter] Gagal mencatat pengunjung: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * No-op setelah response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /**
     * Deteksi apakah user agent adalah bot/crawler.
     */
    private function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $bots = [
            'googlebot', 'bingbot', 'slurp', 'duckduckbot',
            'baiduspider', 'yandexbot', 'sogou', 'facebookexternalhit',
            'twitterbot', 'rogerbot', 'linkedinbot', 'embedly',
            'semrushbot', 'ahrefsbot', 'mj12bot', 'dotbot',
            'petalbot', 'bytespider', 'crawl', 'spider', 'bot/',
        ];

        $lowerAgent = strtolower($userAgent);

        foreach ($bots as $bot) {
            if (str_contains($lowerAgent, $bot)) {
                return true;
            }
        }

        return false;
    }
}
