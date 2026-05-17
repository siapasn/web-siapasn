# Filter Pencarian Paket & Redirect Tombol Mulai Tryout

## Overview

Dua peningkatan UX pada halaman user:

1. **Filter pencarian nama paket** — ditambahkan di halaman `/user/produk` dan `/user/tryout` agar user dapat menyaring daftar paket secara real-time tanpa reload halaman.
2. **Redirect tombol "Mulai Tryout"** — di halaman `/user/produk`, tombol "Mulai Tryout" pada paket yang sudah dibeli kini langsung mengarahkan ke halaman **Lihat Sesi** (`/user/tryout/{tryout_id}/sesi`) alih-alih ke halaman daftar paket umum.

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/User/ProdukController.php` | Tambah query `first_tryout_id` per produk |
| `app/Views/user/produk/index.php` | Tambah filter pencarian + update href tombol Mulai Tryout |
| `app/Views/user/tryout/index.php` | Tambah filter pencarian + `data-nama` pada card |

---

## Key Changes

### 1. ProdukController — `first_tryout_id`

Query baru ditambahkan di dalam loop produk untuk mengambil `tryout_id` pertama (berdasarkan `urutan ASC`) dari `mapping_tryout` yang aktif:

```php
$firstTryout = $db->table('mapping_tryout mt')
    ->select('mt.tryout_id')
    ->join('tryout t', 't.id = mt.tryout_id')
    ->where('mt.produk_id', $p['id'])
    ->where('t.is_active', 1)
    ->orderBy('mt.urutan', 'ASC')
    ->limit(1)
    ->get()->getRowArray();
$p['first_tryout_id'] = $firstTryout ? $firstTryout['tryout_id'] : 0;
```

### 2. Tombol "Mulai Tryout" — Redirect ke Halaman Sesi

Sebelum:
```php
<a href="<?= base_url('user/tryout/') ?>">Mulai Tryout</a>
```

Sesudah:
```php
<a href="<?= base_url('user/tryout/' . $p['first_tryout_id'] . '/sesi') ?>">Mulai Tryout</a>
```

URL tujuan mengikuti pola `/user/tryout/{tryout_id}/sesi` yang ditangani oleh `TryoutController::detailSesi()`.

### 3. Filter Pencarian — Halaman Produk & Tryout

Komponen filter terdiri dari:
- Input text dengan ikon search
- Tombol clear (×) yang muncul saat ada teks
- Pesan "tidak ada hasil" saat semua item tersaring
- Atribut `data-nama` (lowercase) pada setiap card item

**HTML filter:**
```html
<div class="input-group" style="max-width:360px">
    <span class="input-group-text bg-white border-end-0">
        <i class="bi bi-search text-muted"></i>
    </span>
    <input type="text" id="filterProduk" class="form-control border-start-0 ps-0"
           placeholder="Cari nama paket..." autocomplete="off">
    <button type="button" id="clearFilterProduk" class="btn btn-outline-secondary d-none">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
```

**JavaScript filter (client-side, tanpa request ke server):**
```js
(function () {
    const input    = document.getElementById('filterProduk');
    const clearBtn = document.getElementById('clearFilterProduk');
    const empty    = document.getElementById('emptyFilterProduk');
    const grid     = document.getElementById('produkGrid');

    function doFilter() {
        const q     = input.value.trim().toLowerCase();
        const items = grid.querySelectorAll('.produk-item');
        let visible = 0;

        items.forEach(function (el) {
            const nama = el.dataset.nama || '';
            const show = !q || nama.includes(q);
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        clearBtn.classList.toggle('d-none', !q);
        empty.classList.toggle('d-none', visible > 0);
        grid.classList.toggle('d-none', visible === 0);
    }

    input.addEventListener('input', doFilter);
    clearBtn.addEventListener('click', function () {
        input.value = '';
        doFilter();
        input.focus();
    });
}());
```

---

## Behavior

| Kondisi | Hasil |
|---|---|
| Input kosong | Semua paket tampil |
| Input diisi | Hanya paket yang namanya mengandung teks (case-insensitive) |
| Tidak ada hasil | Grid disembunyikan, pesan "tidak ada paket yang cocok" muncul |
| Klik tombol × | Input dikosongkan, semua paket tampil kembali |
| Produk sudah dibeli, klik "Mulai Tryout" | Redirect ke `/user/tryout/{first_tryout_id}/sesi` |
| `first_tryout_id = 0` | Redirect ke `/user/tryout/0/sesi` — controller akan redirect balik dengan pesan error (edge case: produk tanpa tryout aktif) |

---

## Testing Recommendations

1. **Filter produk** — Ketik sebagian nama paket, pastikan card yang tidak cocok hilang dan muncul kembali saat input dihapus.
2. **Filter tryout** — Sama seperti di atas, di halaman `/user/tryout`.
3. **Tombol Mulai Tryout** — Login sebagai user yang sudah membeli paket, klik "Mulai Tryout" di `/user/produk`, pastikan diarahkan ke halaman sesi tryout yang benar.
4. **Edge case** — Produk dengan lebih dari satu tryout: pastikan diarahkan ke tryout pertama berdasarkan urutan.
5. **Edge case** — Produk tanpa tryout aktif (`first_tryout_id = 0`): pastikan controller menangani dengan redirect + pesan error yang sesuai.

---

## Git Workflow

```bash
# Buat branch
git checkout -b ft-filter-paket-redirect-mulai-tryout

# Stage file yang diubah
git add bimbel-cpns/app/Controllers/User/ProdukController.php
git add bimbel-cpns/app/Views/user/produk/index.php
git add bimbel-cpns/app/Views/user/tryout/index.php

# Commit
git commit -m "feat(user): filter pencarian paket & redirect mulai tryout ke halaman sesi

- Tambah filter pencarian real-time di halaman /user/produk dan /user/tryout
- Tombol 'Mulai Tryout' di katalog produk diarahkan ke /user/tryout/{id}/sesi
- ProdukController: query first_tryout_id (urutan pertama, is_active=1) per produk"

# Push
git push -u origin ft-filter-paket-redirect-mulai-tryout
```
