<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * SitemapController
 *
 * Menghasilkan sitemap.xml dinamis untuk mesin pencari.
 * Hanya mencakup halaman publik yang boleh diindeks.
 */
class SitemapController extends BaseController
{
    public function index(): ResponseInterface
    {
        $baseUrl = rtrim(base_url(), '/');

        // Halaman statis publik
        $urls = [
            [
                'loc'        => $baseUrl . '/',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => $baseUrl . '/syarat-ketentuan',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
            [
                'loc'        => $baseUrl . '/kebijakan-privasi',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
            [
                'loc'        => $baseUrl . '/hubungi-kami',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ],
        ];

        // Tambahkan halaman web_content tipe 'halaman' yang aktif
        // (selain yang sudah di atas)
        try {
            $db = \Config\Database::connect();
            $pages = $db->table('web_content')
                ->where('tipe', 'halaman')
                ->where('is_active', 1)
                ->whereNotIn('slug', ['syarat-ketentuan', 'kebijakan-privasi', 'hubungi-kami'])
                ->get()->getResultArray();

            foreach ($pages as $page) {
                $urls[] = [
                    'loc'        => $baseUrl . '/' . $page['slug'],
                    'lastmod'    => isset($page['updated_at'])
                        ? date('Y-m-d', strtotime($page['updated_at']))
                        : date('Y-m-d'),
                    'changefreq' => 'monthly',
                    'priority'   => '0.5',
                ];
            }
        } catch (\Throwable $e) {
            // Jika tabel belum ada, lanjutkan tanpa halaman tambahan
        }

        // Build XML
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "        <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "        <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "        <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=86400') // cache 1 hari
            ->setBody($xml);
    }
}
