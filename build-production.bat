@echo off
REM ============================================================
REM Build script untuk persiapan deploy ke shared hosting
REM Jalankan: build-production.bat
REM ============================================================

echo ============================================================
echo  SiapASN - Build Production Package
echo ============================================================
echo.

REM Install/update composer dependencies tanpa dev packages
echo [1/4] Install Composer dependencies (no-dev)...
composer install --no-dev --optimize-autoloader --no-interaction
if %errorlevel% neq 0 (
    echo ERROR: Composer install gagal!
    pause
    exit /b 1
)
echo OK
echo.

REM Bersihkan cache CI4
echo [2/4] Membersihkan cache...
if exist writable\cache\* del /q writable\cache\*
if exist writable\logs\* del /q writable\logs\*
if exist writable\session\* del /q writable\session\*
echo OK
echo.

REM Buat folder writable jika belum ada
echo [3/4] Memastikan folder writable ada...
if not exist writable\cache mkdir writable\cache
if not exist writable\logs mkdir writable\logs
if not exist writable\session mkdir writable\session
if not exist writable\uploads mkdir writable\uploads
if not exist writable\backups mkdir writable\backups
echo OK
echo.

REM Buat zip untuk upload
echo [4/4] Membuat package zip...
if exist siapasn-production.zip del siapasn-production.zip

powershell -Command "Compress-Archive -Path app,public,vendor,writable,index.php,.htaccess -DestinationPath siapasn-production.zip -Force"
if %errorlevel% neq 0 (
    echo PERINGATAN: Gagal membuat zip otomatis.
    echo Silakan zip manual folder: app, public, vendor, writable, index.php, .htaccess
) else (
    echo OK - File: siapasn-production.zip
)
echo.

echo ============================================================
echo  SELESAI! Langkah selanjutnya:
echo ============================================================
echo.
echo 1. Edit .env untuk production:
echo    - CI_ENVIRONMENT = production
echo    - app.baseURL = 'https://namadomain.com/'
echo    - Isi kredensial database hosting
echo.
echo 2. Upload ke public_html/ di cPanel:
echo    - Semua isi dari siapasn-production.zip
echo    - File .env (edit dulu sesuai hosting)
echo.
echo 3. Import database via phpMyAdmin
echo.
echo 4. Set permission writable/ menjadi 755
echo.
pause
