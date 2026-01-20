@echo off
title Test Livewire Upload Configuration
color 0B

echo.
echo ═══════════════════════════════════════════════════════════════
echo   TEST LIVEWIRE UPLOAD CONFIGURATION
echo ═══════════════════════════════════════════════════════════════
echo.

cd /d %~dp0

echo [TEST 1] Checking livewire-tmp folder...
if exist "storage\app\livewire-tmp" (
    echo [OK] storage\app\livewire-tmp exists
) else (
    echo [FAIL] storage\app\livewire-tmp NOT FOUND
    echo Run INSTALL.bat first!
    pause
    exit /b 1
)

echo.
echo [TEST 2] Checking folder permissions...
php -r "$file='storage/app/livewire-tmp/test.txt'; if(file_put_contents($file,'test')) { echo '[OK] Can write to livewire-tmp'; unlink($file); } else { echo '[FAIL] Cannot write'; }"

echo.
echo [TEST 3] Checking Laravel disk configuration...
php artisan tinker --execute="echo config('filesystems.disks.livewire.root') ? '[OK] Livewire disk configured' : '[FAIL] Disk not configured';" 2>nul

echo.
echo [TEST 4] Checking Livewire temporary file upload config...
php artisan tinker --execute="echo is_null(config('livewire.temporary_file_upload.disk')) ? '[OK] Using default disk' : '[INFO] Using: ' . config('livewire.temporary_file_upload.disk');" 2>nul

echo.
echo [TEST 5] Checking symbolic link...
if exist "public\storage" (
    echo [OK] public\storage exists
    dir "public\storage" | findstr /C:"<SYMLINK>" >nul 2>&1 || dir "public\storage" | findstr /C:"<JUNCTION>" >nul 2>&1
    if %errorlevel% equ 0 (
        echo [OK] Is a link/junction
    ) else (
        echo [WARNING] Not a symbolic link/junction
    )
) else (
    echo [FAIL] public\storage NOT FOUND
    echo Run FIX-STORAGE-LINK.bat as Administrator
)

echo.
echo ═══════════════════════════════════════════════════════════════
echo   TEST COMPLETED
echo ═══════════════════════════════════════════════════════════════
echo.
echo Next step: Test upload di browser
echo Open: http://pos_griya.test/test-storage.php
echo.
pause
