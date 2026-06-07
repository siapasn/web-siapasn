# Tombol Aksi di Tabel Gagal Diam-diam — CSRF Regenerate=true

## Gejala

Tombol **Nonaktifkan** / **Aktifkan** / aksi lain di halaman yang memiliki banyak form (satu form per baris tabel) tidak merespons. Tidak ada pesan error, halaman hanya refresh tanpa perubahan data.

Terjadi di:
- `superadmin/akun` — tombol nonaktifkan/aktifkan user
- Halaman admin lain yang menggunakan pola tombol aksi per baris tabel

---

## Root Cause

**`CSRF regenerate = true` + multiple forms pada satu halaman.**

Dengan `regenerate = true`:
1. Halaman dirender → semua form mendapat token CSRF yang **sama**
2. User klik tombol aksi baris pertama → form POST berhasil → **token diregenerasi** oleh server
3. User klik tombol aksi baris kedua → token di HTML sudah **stale** (tidak cocok dengan token baru di cookie)
4. CSRF validation gagal → request dibatalkan

Diperparah oleh `redirect = (ENVIRONMENT === 'production')` → di development CSRF failure **tidak redirect**, melainkan throw exception yang tidak terlihat user (silent failure).

---

## Fix

**File:** `app/Config/Security.php`

```php
// SEBELUM
public bool $regenerate = true;
public bool $redirect = (ENVIRONMENT === 'production');

// SESUDAH
public bool $regenerate = false;  // token tetap valid selama sesi
public bool $redirect = true;     // selalu redirect dengan pesan error
```

### Penjelasan

| Setting | Nilai Lama | Nilai Baru | Alasan |
|---|---|---|---|
| `regenerate` | `true` | `false` | Token tidak berubah per-submit — aman untuk halaman multi-form. Token tetap unik per sesi dan diperbarui saat login/logout. |
| `redirect` | `(ENVIRONMENT === 'production')` | `true` | CSRF failure selalu redirect dengan error message, tidak pernah gagal diam-diam di environment manapun. |

---

## Keamanan

`regenerate = false` **tetap aman** karena:
- Token CSRF unik per sesi user (di-generate saat login)
- Token dihapus saat logout (session destroy)
- Token tidak bisa ditebak (random bytes)
- Proteksi CSRF utama tetap aktif — hanya frekuensi regenerasi yang diubah

Regenerate per-submit hanya menambah security marginal di atas proteksi sesi yang sudah ada.

---

## File Modified

| File | Perubahan |
|---|---|
| `app/Config/Security.php` | `regenerate = false`, `redirect = true` |

---

## Git Workflow

```bash
git add app/Config/Security.php
git add docs/troubleshooting/csrf-regenerate-multi-form.md
git add docs/README.md

git commit -m "fix(csrf): regenerate=false agar tombol aksi tabel tidak gagal diam-diam

CSRF regenerate=true menyebabkan token stale saat ada multiple form
di satu halaman (pola tombol aksi per baris tabel).
- Security.php: regenerate = false, redirect = true"
```
