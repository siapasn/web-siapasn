# Halaman Profil User — Update Data Diri & Password

## Overview

Fitur baru yang memungkinkan user untuk mengelola data akun mereka sendiri tanpa perlu bantuan admin. User dapat memperbarui nama lengkap, nomor HP, dan password melalui halaman profil yang dapat diakses dari dropdown navbar.

---

## Files Modified / Created

| File | Status | Keterangan |
|---|---|---|
| `app/Controllers/User/ProfilController.php` | **Baru** | Controller dengan 3 method: index, updateProfil, updatePassword |
| `app/Views/user/profil/index.php` | **Baru** | Halaman profil dengan tab Data Diri dan Ubah Password |
| `app/Config/Routes.php` | **Update** | Tambah 3 route profil dalam group `user` |
| `app/Views/layouts/main.php` | **Update** | Tambah link "Profil Saya" di dropdown navbar |

---

## Key Changes

### 1. ProfilController — 3 Method

**`index()`** — Ambil data user dari DB berdasarkan `session('user_id')`, render view dengan data user dan menu sidebar.

**`updateProfil()`** — Validasi dan simpan perubahan nama + telepon:
```php
$rules = [
    'nama'    => 'required|min_length[2]|max_length[100]',
    'telepon' => 'permit_empty|max_length[20]|regex_match[/^[0-9+\-\s()]{6,20}$/]',
];
```
- Setelah berhasil, nama di session diperbarui otomatis: `session()->set('nama', $nama)`
- Telepon kosong disimpan sebagai `null`

**`updatePassword()`** — Validasi dan ganti password:
```php
$rules = [
    'password_lama' => 'required',
    'password_baru' => 'required|min_length[8]|max_length[72]',
    'konfirmasi'    => 'required|matches[password_baru]',
];
```
- Verifikasi password lama dengan `password_verify()`
- Cegah password baru sama dengan password lama
- Hash dengan `PASSWORD_BCRYPT`
- Jika validasi gagal, redirect kembali ke tab password dengan `tab_aktif` flash data

### 2. Routes Baru

```php
// Dalam group 'user' dengan filter 'auth'
$routes->get('profil', 'User\ProfilController::index');
$routes->post('profil/update', 'User\ProfilController::updateProfil');
$routes->post('profil/update-password', 'User\ProfilController::updatePassword');
```

### 3. Navbar Dropdown — Link Profil

Ditambahkan di antara info email dan tombol logout:

```html
<li>
    <a class="dropdown-item" href="<?= base_url('user/profil') ?>">
        <i class="bi bi-person-gear me-1"></i> Profil Saya
    </a>
</li>
```

### 4. View — Tab System

Halaman profil menggunakan dua tab yang diswitch via JavaScript (tanpa reload):

| Tab | Konten |
|---|---|
| **Data Diri** | Form nama + telepon. Email ditampilkan read-only (tidak bisa diubah). |
| **Ubah Password** | Form password lama, password baru, konfirmasi. |

Tab aktif dipertahankan saat redirect kembali setelah error validasi menggunakan flash data `tab_aktif`.

### 5. UX Enhancements (Client-side)

- **Toggle show/hide password** — tombol mata pada setiap field password
- **Indikator kekuatan password** — progress bar 5 level (Sangat lemah → Sangat kuat) berdasarkan panjang, huruf besar, angka, dan karakter spesial
- **Cek kecocokan konfirmasi** — feedback real-time saat mengetik konfirmasi password
- **Cegah submit** — form tidak bisa disubmit jika konfirmasi tidak cocok (client-side guard, server tetap validasi)

---

## Security Considerations

- Email tidak dapat diubah oleh user (hanya admin yang bisa)
- Password lama wajib diverifikasi sebelum ganti password baru
- Password baru tidak boleh sama dengan password lama
- Semua validasi dilakukan di server-side (client-side hanya UX tambahan)
- CSRF token disertakan di semua form (`<?= csrf_field() ?>`)
- Output di-escape dengan `esc()` untuk mencegah XSS

---

## Usage

1. Login sebagai user
2. Klik nama user di pojok kanan atas navbar
3. Pilih **"Profil Saya"** dari dropdown
4. Untuk update nama/HP: isi form di tab **Data Diri** → klik **Simpan Perubahan**
5. Untuk ganti password: klik tab **Ubah Password** → isi ketiga field → klik **Perbarui Password**

---

## Testing Recommendations

1. **Update nama** — ubah nama, pastikan nama di navbar (session) ikut berubah setelah redirect.
2. **Update telepon** — isi format valid (08xxx), format tidak valid (huruf), dan kosongkan — pastikan validasi bekerja.
3. **Ganti password** — masukkan password lama salah, pastikan error muncul di tab password (bukan tab data diri).
4. **Password sama** — coba ganti password baru dengan nilai yang sama dengan password lama, pastikan ditolak.
5. **Konfirmasi tidak cocok** — pastikan form tidak bisa disubmit dan error ditampilkan.
6. **Tab persistence** — setelah error di tab password, pastikan halaman kembali ke tab password (bukan tab data diri).
7. **Session update** — setelah update nama, refresh halaman lain dan pastikan nama baru tampil di navbar.

---

## Git Workflow

```bash
# Buat branch
git checkout -b ft-user-profil-update

# Stage file yang diubah/dibuat
git add bimbel-cpns/app/Controllers/User/ProfilController.php
git add bimbel-cpns/app/Views/user/profil/index.php
git add bimbel-cpns/app/Config/Routes.php
git add bimbel-cpns/app/Views/layouts/main.php

# Commit
git commit -m "feat(user): halaman profil untuk update nama, no hp, dan password

- ProfilController: updateProfil() dan updatePassword() dengan validasi server-side
- View profil dengan tab Data Diri dan Ubah Password
- Indikator kekuatan password + toggle show/hide
- Link 'Profil Saya' di dropdown navbar
- Session nama diperbarui otomatis setelah update"

# Push
git push -u origin ft-user-profil-update
```
