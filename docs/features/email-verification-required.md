# Email Verification Required Before Login

## Overview

Sebelumnya user yang baru registrasi langsung mendapat `is_active = 1` dan bisa login tanpa memverifikasi email. Fitur ini mewajibkan verifikasi email sebelum akun aktif — user yang belum verifikasi tidak bisa login dan mendapat tombol "Kirim Ulang Email Verifikasi" di halaman login.

**Alur baru:**
```
Register → is_active=0 → Cek Email → Klik Link → is_active=1 → Bisa Login
```

---

## Files Modified

| File | Perubahan |
|---|---|
| `app/Controllers/Auth/AuthController.php` | `registerProcess()`: `is_active=0`; `loginProcess()`: blokir non-aktif; tambah `resendVerification()` |
| `app/Models/UserModel.php` | `verifyEmail()`: tambah `is_active=1` |
| `app/Config/Routes.php` | Tambah `POST /resend-verification` |
| `app/Views/auth/login.php` | Tampilkan alert + tombol kirim ulang jika belum verifikasi |

---

## Key Changes

### 1. Register — `is_active = 0`

```php
// AuthController::registerProcess()
$userId = $this->userModel->insert([
    ...
    'is_active' => 0, // nonaktif sampai verifikasi email
]);
```

### 2. Verify Email — aktifkan akun

```php
// UserModel::verifyEmail()
$this->update($userId, [
    'email_verified_at' => date('Y-m-d H:i:s'),
    'is_active'         => 1,  // aktifkan sekaligus
]);
```

### 3. Login — blokir user belum verifikasi

```php
// AuthController::loginProcess()
if (! $user['is_active']) {
    return redirect()->back()
        ->with('error', 'Akun belum aktif. Silakan verifikasi email.')
        ->with('show_resend_verification', $user['email']);
}
```

Pesan dibedakan:
- `email_verified_at` kosong / ada token aktif → "belum verifikasi email" + tombol resend
- `is_active=0` tapi sudah pernah verifikasi → "dinonaktifkan admin"

### 4. Resend Verification — `POST /resend-verification`

- Hapus token lama dari `password_resets`, buat token baru
- Kirim ulang email verifikasi
- Aman dari enumeration: selalu beri response sukses meski email tidak terdaftar

### 5. View Login — tombol kirim ulang

Muncul hanya saat flashdata `show_resend_verification` ada:

```php
<?php if (session()->getFlashdata('show_resend_verification')): ?>
<div class="alert alert-warning">
    <form action="/resend-verification" method="post">
        <input type="hidden" name="email" value="<?= esc($resendEmail) ?>">
        <button type="submit">Kirim Ulang Email Verifikasi</button>
    </form>
</div>
<?php endif; ?>
```

---

## Impact pada Existing Users

User lama yang sudah `is_active = 1` tidak terpengaruh — mereka tetap bisa login normal.

User lama yang `is_active = 0` (dinonaktifkan admin) tidak akan mendapat tombol resend — hanya pesan "hubungi admin".

---

## Testing Recommendations

1. **Register baru** → login langsung → harus ditolak dengan pesan "belum verifikasi"
2. **Register baru** → klik link verifikasi → `is_active` berubah jadi 1 → bisa login
3. **Login user belum verifikasi** → muncul alert warning + tombol "Kirim Ulang"
4. **Klik "Kirim Ulang"** → email terkirim, token lama terhapus
5. **Klik link lama** (sudah dikirim ulang) → token tidak valid → redirect login dengan error
6. **User nonaktif oleh admin** (`email_verified_at` ada, `is_active=0`) → pesan "hubungi admin" (tanpa tombol resend)
7. **Akun sudah aktif klik resend** → redirect login dengan pesan "sudah terverifikasi"

---

## Git Workflow

```bash
git checkout -b ft-email-verification-required

git add app/Controllers/Auth/AuthController.php
git add app/Models/UserModel.php
git add app/Config/Routes.php
git add app/Views/auth/login.php
git add docs/features/email-verification-required.md
git add docs/README.md

git commit -m "feat(auth): user nonaktif sebelum verifikasi email

- Register: is_active = 0, aktif setelah klik link verifikasi email
- verifyEmail(): set is_active = 1 + email_verified_at sekaligus
- Login: blokir user is_active = 0 dengan pesan kontekstual
- Tambah POST /resend-verification — kirim ulang link verifikasi
- Login view: tombol resend muncul otomatis saat akun belum aktif"

git push -u origin ft-email-verification-required
```
