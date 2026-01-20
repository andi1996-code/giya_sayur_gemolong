@echo off
echo ========================================
echo   Setup POS Griya untuk Laragon
echo ========================================
echo.

cd /d %~dp0

echo [1/7] Membuat folder livewire-tmp...
if not exist "storage\app\livewire-tmp" mkdir "storage\app\livewire-tmp"
if not exist "storage\app\public\livewire-tmp" mkdir "storage\app\public\livewire-tmp"
echo [OK] Folder livewire-tmp dibuat

echo.
echo [2/7] Membuat .gitignore untuk livewire-tmp...
echo * > "storage\app\livewire-tmp\.gitignore"
echo !.gitignore >> "storage\app\livewire-tmp\.gitignore"
echo * > "storage\app\public\livewire-tmp\.gitignore"
echo !.gitignore >> "storage\app\public\livewire-tmp\.gitignore"
echo [OK] .gitignore dibuat

echo.
echo [3/7] Set permission untuk folder storage...
icacls storage /grant Everyone:(OI)(CI)F /T >nul 2>&1
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T >nul 2>&1
echo [OK] Permission diset

echo.
echo [4/7] Hapus symbolic link lama...
if exist "public\storage" (
    rmdir "public\storage" /S /Q >nul 2>&1
    del "public\storage" /F /Q >nul 2>&1
)
echo [OK] Link lama dihapus

echo.
echo [5/7] Buat symbolic link baru (Windows format)...
mklink /D "public\storage" "..\storage\app\public" >nul
echo [OK] Symbolic link dibuat

echo.
echo [6/7] Clear cache Laravel...
php artisan config:clear >nul
php artisan cache:clear >nul
php artisan view:clear >nul
php artisan route:clear >nul
echo [OK] Cache dibersihkan

echo.
echo [7/7] Cache ulang konfigurasi...
php artisan config:cache >nul
echo [OK] Konfigurasi di-cache

echo.
echo ========================================
echo   SETUP SELESAI!
echo ========================================
echo.
echo Silakan test dengan:
echo 1. Buka: http://pos_griya.test/test-storage.php
echo 2. Semua check harus hijau (YES)
echo 3. Coba upload gambar di aplikasi
echo.
pause
