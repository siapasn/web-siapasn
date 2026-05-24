# Form Soal — Tipe Soal Langsung dari Kategori, Hapus Sub Kategori

## Overview

Penyederhanaan form tambah/edit soal:

- **Field Sub Kategori dihapus** — tidak diperlukan lagi di form
- **Tipe soal (SCORE/POINT) dibaca langsung dari kategori yang dipilih** — tanpa AJAX tambahan, menggunakan `data-tipe` attribute pada `<option>`
- **Badge tipe soal** ditampilkan di bawah dropdown kategori sebagai feedback visual
- **Controller** diperbarui: `tipe_soal` diambil dari `kategori_id`, bukan `sub_kategori_id`

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Models/KategoriModel.php` | `getAll()` sekarang include kolom `tipe_soal` |
| `app/Controllers/Admin/Master/SoalController.php` | `store()` & `update()` ambil tipe dari kategori; hapus validasi sub_kategori; tambah `getTipeSoal()`; update `getTipeSoalFromSoal()` |
| `app/Config/Routes.php` | Tambah route `GET master/soal/tipe-soal/(:num)` |
| `app/Views/admin/master/soal/form.php` | Hapus field Sub Kategori; tipe soal dari `data-tipe` attribute; badge tipe soal |

---

## Key Changes

### 1. KategoriModel — `getAll()` include `tipe_soal`

```php
public function getAll(): array
{
    return $this->db->query('
        SELECT k.id, k.nama, k.parent_id, k.tipe_soal, p.nama AS parent_nama
        FROM kategori k
        LEFT JOIN kategori p ON p.id = k.parent_id
        ORDER BY COALESCE(k.parent_id, k.id) ASC, k.parent_id IS NULL DESC, k.nama ASC
    ')->getResultArray();
}
```

### 2. View — `data-tipe` pada option kategori

```php
<option value="<?= $k['id'] ?>"
    data-tipe="<?= esc($k['tipe_soal'] ?? '') ?>"
    <?= selected ?>>
    <?= $label ?>
</option>
```

### 3. JS — baca tipe dari attribute, tidak perlu AJAX

```js
$('#kategori_id').on('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const tipe = selectedOption ? (selectedOption.dataset.tipe || '') : '';
    updateTipeSoal(tipe);  // toggle SCORE/POINT fields
});
```

### 4. Controller — tipe dari kategori langsung

```php
// store() dan update()
$kategoriId = (int) $this->request->getPost('kategori_id');
$kat        = $this->kategoriModel->find($kategoriId);
$tipeSoal   = $kat['tipe_soal'] ?? null;

// sub_kategori_id selalu null
$this->soalModel->insert([
    'kategori_id'     => $kategoriId,
    'sub_kategori_id' => null,
    ...
]);
```

### 5. Badge tipe soal (UX feedback)

```
SCORE → badge kuning: "Tipe: SCORE — nilai per pilihan (1–5)"
POINT → badge biru:   "Tipe: POINT — pilihan ganda kunci jawaban"
kosong → tidak ada badge
```

---

## Behavior

| Kondisi | Tampilan |
|---|---|
| Kategori belum dipilih | Semua field jawaban tersembunyi |
| Kategori tipe `POINT` | Field Kunci Jawaban muncul, Nilai A-E tersembunyi |
| Kategori tipe `SCORE` | Field Nilai A-E muncul, Kunci Jawaban tersembunyi |
| Kategori tanpa tipe | Semua field jawaban tersembunyi |

---

## Testing Recommendations

1. Buka form **Tambah Soal** → pilih kategori bertipe POINT → pastikan field Kunci Jawaban muncul dan badge biru tampil
2. Pilih kategori bertipe SCORE → pastikan field Nilai A-E muncul dan badge kuning tampil
3. Pilih kategori tanpa tipe → pastikan semua field jawaban tersembunyi
4. Submit form dengan POINT tanpa kunci jawaban → harus error validasi
5. Submit form dengan SCORE tanpa nilai → harus error validasi
6. Buka form **Edit Soal** → tipe soal harus ter-load otomatis sesuai kategori yang tersimpan
7. Pastikan field Sub Kategori tidak muncul sama sekali

---

## Git Commands

```bash
git checkout -b ft-soal-tipe-dari-kategori

git add app/Models/KategoriModel.php
git add app/Controllers/Admin/Master/SoalController.php
git add app/Config/Routes.php
git add app/Views/admin/master/soal/form.php

git commit -m "feat(soal): remove sub-kategori field, load tipe_soal directly from kategori selection"

git push origin ft-soal-tipe-dari-kategori
```
