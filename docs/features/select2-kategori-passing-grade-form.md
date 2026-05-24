# Select2 Search pada Form Kategori & Passing Grade

## Overview

Peningkatan UX pada dua form admin:

1. **Form Kategori** (`create` & `edit`) — field *Parent Kategori* kini menampilkan **semua kategori** (induk + sub-kategori) dan dilengkapi **Select2** agar bisa di-search.
2. **Form Passing Grade** (`create` & `edit`) — field *Kategori* menampilkan **semua kategori** dengan Select2, dan field *Nilai Minimum* diubah menjadi **opsional** (tidak lagi mandatory).

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Models/KategoriModel.php` | Tambah method `getAll()` — ambil semua kategori beserta nama parent |
| `app/Controllers/Admin/Master/KategoriController.php` | `create()` & `edit()` gunakan `getAll()` |
| `app/Controllers/Admin/Master/PassingGradeController.php` | `create()` & `edit()` gunakan `getAll()`; validasi `nilai_minimum` → `permit_empty`; handle `null` di `store()` & `update()` |
| `app/Views/layouts/admin.php` | Tambah Select2 CSS + Bootstrap 5 theme + JS ke layout global |
| `app/Views/admin/master/kategori/form.php` | Field `parent_id` tampilkan semua kategori dengan format `Parent › Sub`; inisialisasi Select2 |
| `app/Views/admin/master/passing-grade/form.php` | Field `kategori_id` dengan Select2; `nilai_minimum` jadi opsional; modal duplikat handle nilai `null` |

---

## Key Changes

### 1. KategoriModel — method `getAll()`

```php
public function getAll(): array
{
    return $this->db->table('kategori k')
        ->select('k.id, k.nama, k.parent_id, p.nama AS parent_nama')
        ->join('kategori p', 'p.id = k.parent_id', 'left')
        ->orderBy('COALESCE(k.parent_id, k.id)', 'ASC')
        ->orderBy('k.parent_id IS NULL', 'DESC')
        ->orderBy('k.nama', 'ASC')
        ->get()
        ->getResultArray();
}
```

Mengembalikan semua kategori diurutkan secara hierarkis — induk dulu, lalu sub-kategorinya.

### 2. Select2 di Layout Global

Ditambahkan ke `layouts/admin.php` sehingga tersedia di semua halaman admin:

```html
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<!-- JS (setelah jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
```

### 3. Inisialisasi Select2 pada Form

```js
$('#parent_id').select2({
    theme: 'bootstrap-5',
    placeholder: '— Tidak ada (kategori induk) —',
    allowClear: true,
    width: '100%',
});
```

### 4. Label Hierarkis pada Option

Kategori yang memiliki parent ditampilkan dengan format `Parent › Sub`:

```php
$label = esc($p['nama']);
if (! empty($p['parent_nama'])) {
    $label = esc($p['parent_nama']) . ' › ' . $label;
}
```

### 5. Nilai Minimum Opsional

Validasi di controller:
```php
// Sebelum
'nilai_minimum' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',

// Sesudah
'nilai_minimum' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
```

Penyimpanan ke database:
```php
$nilaiMinimumRaw = $this->request->getPost('nilai_minimum');
$nilaiMinimum = ($nilaiMinimumRaw !== '' && $nilaiMinimumRaw !== null) ? (float) $nilaiMinimumRaw : null;
```

---

## Behavior Notes

- **Form Kategori (edit)**: kategori yang sedang diedit tetap di-exclude dari daftar pilihan parent (mencegah self-reference).
- **Aturan 1 tingkat**: validasi di controller tetap berlaku — tidak bisa memilih sub-kategori sebagai parent.
- **Passing Grade**: field Sub Kategori tetap di-load via AJAX berdasarkan kategori yang dipilih (tidak berubah).
- **Modal duplikat**: jika `nilai_minimum` kosong, ditampilkan `—` di tabel konfirmasi.

---

## Testing Recommendations

1. Buka form **Tambah Kategori** → pastikan dropdown Parent Kategori menampilkan semua kategori (induk + sub) dan bisa di-search.
2. Buka form **Edit Kategori** → pastikan kategori yang sedang diedit tidak muncul di daftar parent.
3. Pilih parent yang merupakan sub-kategori → pastikan validasi server menolak (max 1 tingkat).
4. Buka form **Tambah Passing Grade** → pastikan dropdown Kategori menampilkan semua kategori dengan label hierarkis dan bisa di-search.
5. Submit form Passing Grade **tanpa mengisi Nilai Minimum** → harus berhasil disimpan (nilai `null` di DB).
6. Submit form Passing Grade **dengan Nilai Minimum diisi** → harus tersimpan normal.
7. Cek modal konfirmasi duplikat saat nilai minimum kosong → tampilkan `—`.

---

## Git Workflow

```bash
# Buat branch
git checkout -b ft-select2-kategori-passing-grade

# Stage semua file yang diubah
git add app/Models/KategoriModel.php
git add app/Controllers/Admin/Master/KategoriController.php
git add app/Controllers/Admin/Master/PassingGradeController.php
git add app/Views/layouts/admin.php
git add app/Views/admin/master/kategori/form.php
git add app/Views/admin/master/passing-grade/form.php

# Commit
git commit -m "feat(admin): select2 search on kategori & passing grade forms

- Add KategoriModel::getAll() to return all categories with parent name
- Kategori form: show all categories in parent dropdown with Select2 search
- Passing grade form: show all categories in kategori dropdown with Select2 search
- Passing grade: make nilai_minimum optional (permit_empty, nullable)
- Add Select2 CSS/JS to admin layout globally
- Display hierarchical label format 'Parent › Sub' in dropdowns"

# Push
git push origin ft-select2-kategori-passing-grade
```
