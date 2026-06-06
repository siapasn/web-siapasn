<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * SitemapController
 *
 * Menghasilkan sitemap.xml dinamis untuk mesin pencari.
 * Mencakup halaman publik, produk highlight, dan tryout event aktif.
 */
class SitemapController extends BaseController
{
    public function index(): ResponseInterface
    {
        $baseUrl = rtrim(base_url(), '/');
        $db      = \Config\Database::connect();

        // ── 1. Halaman statis publik ──────────────────────────────────────────
        $urls = [
            [
                'loc'        => $baseUrl . '/',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ],
            [
                'loc'        => $baseUrl . '/syarat-ketentuan',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.4',
            ],
            [
                'loc'        => $baseUrl . '/kebijakan-privasi',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.4',
            ],
            [
                'loc'        => $baseUrl . '/hubungi-kami',
                'lastmod'    => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
        ];

        // ── 2. Halaman web_content dinamis (tipe 'halaman') ──────────────────
        try {
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
            // tabel belum ada, lanjutkan
        }

        // ── 3. Produk highlight (is_highlight = 1, is_active = 1) ─────────────
        // Ini produk yang diprioritaskan untuk SEO — tampil di landing page
        try {
            $produkHighlight = $db->table('produk p')
                ->select('p.id, p.nama, p.slug, p.updated_at, p.kategori_id, k.nama AS kategori_nama')
                ->join('kategori k', 'k.id = p.kategori_id', 'left')
                ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
                ->where('p.is_active', 1)
                ->where('p.is_highlight', 1)
                ->groupBy('p.id')
                ->having('COUNT(mt.id) >', 0)
                ->orderBy('p.updated_at', 'DESC')
                ->get()->getResultArray();

            foreach ($produkHighlight as $p) {
                $slugUrl = ! empty($p['slug']) ? $p['slug'] : $p['id'];
                $urls[] = [
                    'loc'        => $baseUrl . '/user/produk/' . $slugUrl,
                    'lastmod'    => isset($p['updated_at'])
                        ? date('Y-m-d', strtotime($p['updated_at']))
                        : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority'   => '0.9',
                    'title'      => $p['nama'],          // untuk referensi internal (tidak masuk XML)
                    'kategori'   => $p['kategori_nama'], // idem
                ];
            }
        } catch (\Throwable $e) {
            // lanjutkan
        }

        // ── 4. Semua produk aktif (non-highlight) yang punya tryout ───────────
        // Priority lebih rendah dari highlight
        try {
            $produkBiasa = $db->table('produk p')
                ->select('p.id, p.nama, p.slug, p.updated_at')
                ->join('mapping_tryout mt', 'mt.produk_id = p.id', 'inner')
                ->where('p.is_active', 1)
                ->where('p.is_highlight', 0)
                ->groupBy('p.id')
                ->having('COUNT(mt.id) >', 0)
                ->orderBy('p.updated_at', 'DESC')
                ->get()->getResultArray();

            foreach ($produkBiasa as $p) {
                $slugUrl = ! empty($p['slug']) ? $p['slug'] : $p['id'];
                $urls[] = [
                    'loc'        => $baseUrl . '/user/produk/' . $slugUrl,
                    'lastmod'    => isset($p['updated_at'])
                        ? date('Y-m-d', strtotime($p['updated_at']))
                        : date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            }
        } catch (\Throwable $e) {
            // lanjutkan
        }

        // ── 5. Tryout Event aktif (publik, bisa dilihat sebelum login) ────────
        try {
            $now    = date('Y-m-d H:i:s');
            $events = $db->table('tryout_event te')
                ->select('te.id, te.nama, te.slug, te.updated_at, te.tutup_pelaksanaan')
                ->where('te.is_active', 1)
                ->where('te.tutup_pelaksanaan >=', $now)
                ->orderBy('te.mulai_pelaksanaan', 'ASC')
                ->get()->getResultArray();

            foreach ($events as $ev) {
                $slugUrl = ! empty($ev['slug']) ? $ev['slug'] : $ev['id'];
                $urls[] = [
                    'loc'        => $baseUrl . '/user/tryout-event/' . $slugUrl,
                    'lastmod'    => isset($ev['updated_at'])
                        ? date('Y-m-d', strtotime($ev['updated_at']))
                        : date('Y-m-d'),
                    'changefreq' => 'daily',
                    'priority'   => '0.8',
                ];
            }
        } catch (\Throwable $e) {
            // lanjutkan
        }

        // ── Build XML ─────────────────────────────────────────────────────────
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
            ->setHeader('Cache-Control', 'public, max-age=3600') // cache 1 jam
            ->setBody($xml);
    }
}
