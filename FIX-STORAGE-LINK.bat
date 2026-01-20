@echo off
title Fix Storage Link - Run as Administrator
color 0C

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║              FIX STORAGE SYMBOLIC LINK                       ║
echo  ║          (Harus dijalankan sebagai Administrator)            ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.

cd /d %~dp0

:: Check if running as Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    color 0C
    echo  ╔═══════════════════════════════════════════════════════════════╗
    echo  ║                                                               ║
    echo  ║                    ✗ NOT ADMINISTRATOR!                      ║
    echo  ║                                                               ║
    echo  ╚═══════════════════════════════════════════════════════════════╝
    echo.
    echo  ERROR: File ini HARUS dijalankan sebagai Administrator!
    echo.
    echo  Cara menjalankan sebagai Administrator:
    echo  1. Right-click file FIX-STORAGE-LINK.bat
    echo  2. Pilih "Run as administrator"
    echo  3. Klik "Yes" jika muncul UAC prompt
    echo.
    pause
    exit /b 1
)

color 0A
echo [OK] Running as Administrator
echo.
echo Removing old storage link...

:: Remove old link/folder
if exist "public\storage" (
    rmdir "public\storage" /S /Q >nul 2>&1
    del "public\storage" /F /Q >nul 2>&1
    attrib -h -s "public\storage" >nul 2>&1
    rmdir "public\storage" >nul 2>&1
    echo [OK] Old storage link removed
)

echo.
echo Creating new symbolic link...

:: Try symbolic link (best option)
mklink /D "public\storage" "..\storage\app\public" >nul 2>&1
if %errorlevel% equ 0 (
    echo [SUCCESS] Symbolic link created!
    echo Type: Symbolic Link
    goto SUCCESS
)

echo [INFO] Symbolic link failed, trying junction...

:: Try directory junction (works without admin in some cases)
mklink /J "public\storage" "..\storage\app\public" >nul 2>&1
if %errorlevel% equ 0 (
    echo [SUCCESS] Directory junction created!
    echo Type: Directory Junction
    goto SUCCESS
)

echo [INFO] Junction failed, trying Laravel artisan...

:: Try Laravel artisan storage:link
php artisan storage:link 2>&1 | findstr /C:"linked" >nul
if %errorlevel% equ 0 (
    echo [SUCCESS] Storage link created via Laravel!
    echo Type: Laravel Artisan
    goto SUCCESS
)

:: All methods failed
color 0C
echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║                 ✗ ALL METHODS FAILED                         ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  Semua metode gagal membuat storage link!
echo.
echo  Solusi alternatif:
echo  1. Enable Windows Developer Mode:
echo     - Buka Settings
echo     - Privacy ^& Security ^> For developers
echo     - Aktifkan "Developer Mode"
echo     - Restart computer
echo     - Jalankan file ini lagi
echo.
echo  2. Atau gunakan copy folder (tidak ideal):
echo     - Buka file: USE-COPY-STORAGE.bat
echo.
pause
exit /b 1

:SUCCESS
echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║                 ✓ STORAGE LINK CREATED!                      ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  Next steps:
echo  1. Buka: http://pos_griya.test/test-storage.php
echo  2. Pastikan "Symbolic Link Check" sekarang: YES ✅
echo  3. Coba upload gambar di aplikasi
echo.
pause
