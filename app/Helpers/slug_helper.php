<?php

/**
 * Slug Helper
 * Fungsi-fungsi bantu untuk generate dan validasi slug.
 */

if (! function_exists('make_slug')) {
    /**
     * Convert teks menjadi slug URL-friendly.
     *
     * @param string $text Teks yang akan dikonversi
     * @return string Slug yang sudah dinormalisasi
     */
    function make_slug(string $text): string
    {
        // Gunakan url_title bawaan CodeIgniter jika tersedia
        if (function_exists('url_title')) {
            $slug = url_title(strtolower($text), '-', true);
        } else {
            // Transliterasi dasar (huruf beraksent → ASCII)
            $slug = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
            $slug = strtolower($slug);
            $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
            $slug = preg_replace('/[\s\-]+/', '-', $slug);
        }

        // Hapus karakter non-alphanumeric (kecuali strip)
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'item';
    }
}

if (! function_exists('make_unique_slug')) {
    /**
     * Generate slug unik untuk suatu tabel database.
     * Jika slug sudah ada, tambahkan suffix angka incrementing.
     *
     * @param string   $table     Nama tabel database
     * @param string   $text      Teks sumber slug
     * @param int|null $excludeId ID record yang dikecualikan (saat update)
     * @return string Slug unik
     */
    function make_unique_slug(string $table, string $text, ?int $excludeId = null): string
    {
        $db   = \Config\Database::connect();
        $base = make_slug($text);

        $slug  = $base;
        $count = 1;

        while (true) {
            $builder = $db->table($table)->where('slug', $slug);

            if ($excludeId !== null) {
                $builder->where('id !=', $excludeId);
            }

            if ($builder->countAllResults() === 0) {
                break;
            }

            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
