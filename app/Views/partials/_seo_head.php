<?php
/**
 * SEO Head Partial
 *
 * Variabel yang bisa dioper dari view pemanggil:
 *   $seo_title       — judul halaman (tanpa nama situs)
 *   $seo_description — meta description (maks 160 karakter)
 *   $seo_keywords    — meta keywords (opsional)
 *   $seo_canonical   — URL kanonik (default: current URL)
 *   $seo_og_image    — URL gambar Open Graph (default: logo)
 *   $seo_og_type     — 'website' | 'article' (default: 'website')
 *   $seo_noindex     — true untuk noindex (halaman auth/admin/user)
 *   $seo_page_type   — 'home' | 'page' | 'auth' | 'admin' (untuk schema)
 */

$appName    = config('App')->appName ?? 'SiapASN Simulation Center';
$baseUrl    = rtrim(base_url(), '/');

// Title
$title      = isset($seo_title) && $seo_title !== ''
    ? esc($seo_title) . ' — ' . $appName
    : $appName . ' | Platform Tryout CPNS & PPPK Terpercaya';

// Description
$description = isset($seo_description) && $seo_description !== ''
    ? esc($seo_description)
    : 'Platform simulasi tryout CPNS & PPPK terlengkap. Ribuan soal SKD & SKB terverifikasi, pembahasan lengkap, analisis nilai real-time. Persiapkan dirimu lolos seleksi ASN bersama SiapASN.';

// Keywords
$keywords = isset($seo_keywords) && $seo_keywords !== ''
    ? esc($seo_keywords)
    : 'tryout CPNS, simulasi CPNS, latihan soal CPNS, SKD, SKB, PPPK, seleksi ASN, bimbel CPNS, passing grade CPNS';

// Canonical URL
$canonical = isset($seo_canonical) && $seo_canonical !== ''
    ? esc($seo_canonical)
    : current_url();

// OG Image
$ogImage = isset($seo_og_image) && $seo_og_image !== ''
    ? esc($seo_og_image)
    : $baseUrl . '/assets/images/og-image.svg';

// OG Type
$ogType = isset($seo_og_type) ? esc($seo_og_type) : 'website';

// Noindex
$noindex = isset($seo_noindex) && $seo_noindex === true;
?>

    <!-- =====================================================================
         SEO — Primary Meta Tags
    ====================================================================== -->
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords"    content="<?= $keywords ?>">
    <meta name="author"      content="<?= $appName ?>">
    <meta name="robots"      content="<?= $noindex ? 'noindex, nofollow' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1' ?>">
    <link rel="canonical"    href="<?= $canonical ?>">

    <!-- =====================================================================
         SEO — Open Graph (Facebook, WhatsApp, LinkedIn)
    ====================================================================== -->
    <meta property="og:type"        content="<?= $ogType ?>">
    <meta property="og:url"         content="<?= $canonical ?>">
    <meta property="og:title"       content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:image"       content="<?= $ogImage ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"   content="<?= $appName ?>">
    <meta property="og:locale"      content="id_ID">

    <!-- =====================================================================
         SEO — Twitter Card
    ====================================================================== -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $title ?>">
    <meta name="twitter:description" content="<?= $description ?>">
    <meta name="twitter:image"       content="<?= $ogImage ?>">

    <!-- =====================================================================
         PWA — Web App Manifest & Theme
    ====================================================================== -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#0f2744">
    <meta name="application-name" content="<?= $appName ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SiapASN">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/SiapASN.png') ?>">

    <!-- =====================================================================
         Schema.org — JSON-LD Structured Data
    ====================================================================== -->
<?php if (!$noindex): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "<?= $appName ?>",
        "url": "<?= $baseUrl ?>",
        "logo": "<?= $ogImage ?>",
        "description": "<?= addslashes($description) ?>",
        "sameAs": [],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "url": "<?= $baseUrl ?>/hubungi-kami"
        }
    }
    </script>
<?php if (isset($seo_page_type) && $seo_page_type === 'home'): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= $appName ?>",
        "url": "<?= $baseUrl ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?= $baseUrl ?>/user/produk?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
<?php endif; ?>
<?php endif; ?>
