@echo off
title Use Copy Storage (Fallback Solution)
color 0E

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║          FALLBACK SOLUTION: COPY STORAGE FOLDER              ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  PERINGATAN:
echo  • Solusi ini TIDAK IDEAL tapi akan membuat upload bekerja
echo  • File akan dicopy, bukan linked
echo  • Anda perlu run script ini setiap kali ada file baru di storage
echo.
echo  Gunakan ini HANYA jika:
echo  • FIX-STORAGE-LINK.bat gagal
echo  • Developer Mode tidak bisa diaktifkan
echo  • Tidak bisa run as Administrator
echo.
pause

cd /d %~dp0

echo.
echo Removing old storage...
if exist "public\storage" (
    rmdir "public\storage" /S /Q >nul 2>&1
    del "public\storage" /F /Q >nul 2>&1
)

echo.
echo Copying storage folder...
xcopy "storage\app\public" "public\storage" /E /I /Y >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Storage folder copied
) else (
    echo [ERROR] Failed to copy storage folder
    pause
    exit /b 1
)

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║                  ✓ COPY COMPLETED                            ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  PENTING:
echo  • Upload sekarang akan bekerja
echo  • File akan tersimpan di: public\storage
echo  • Tapi jika ada file baru di storage\app\public,
echo    Anda perlu jalankan script ini lagi
echo.
echo  Untuk auto-sync, gunakan: SYNC-STORAGE.bat
echo.
pause
