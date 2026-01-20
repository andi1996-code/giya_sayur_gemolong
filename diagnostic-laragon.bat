@echo off
echo ========================================
echo   Diagnostic POS Griya - Laragon
echo ========================================
echo.

cd /d %~dp0

echo [CHECK 1] Folder Structure
echo --------------------------
if exist "storage\app\livewire-tmp" (
    echo [OK] storage\app\livewire-tmp EXISTS
) else (
    echo [FAIL] storage\app\livewire-tmp NOT FOUND
)

if exist "storage\app\public\livewire-tmp" (
    echo [OK] storage\app\public\livewire-tmp EXISTS
) else (
    echo [FAIL] storage\app\public\livewire-tmp NOT FOUND
)

echo.
echo [CHECK 2] Symbolic Link
echo -----------------------
if exist "public\storage" (
    echo [OK] public\storage EXISTS
    dir "public\storage" | findstr /C:"<SYMLINK>" >nul
    if %errorlevel% equ 0 (
        echo [OK] Is a symbolic link
    ) else (
        echo [WARNING] Not a symbolic link
    )
) else (
    echo [FAIL] public\storage NOT FOUND
)

echo.
echo [CHECK 3] File Listing
echo ---------------------
echo storage\app\livewire-tmp contents:
dir "storage\app\livewire-tmp" /B 2>nul
if %errorlevel% neq 0 echo [Empty or not accessible]

echo.
echo storage\app\public\livewire-tmp contents:
dir "storage\app\public\livewire-tmp" /B 2>nul
if %errorlevel% neq 0 echo [Empty or not accessible]

echo.
echo [CHECK 4] PHP Info
echo -----------------
php -r "echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;"

echo.
echo [CHECK 5] Laravel Config
echo -----------------------
php artisan config:show filesystems.default 2>nul
php artisan config:show filesystems.disks.local.root 2>nul

echo.
echo [CHECK 6] Write Test
echo -------------------
php -r "$file='storage/app/livewire-tmp/test.txt'; if(file_put_contents($file,'test')) { echo '[OK] Can write to livewire-tmp'; unlink($file); } else { echo '[FAIL] Cannot write to livewire-tmp'; }"

echo.
echo ========================================
echo   DIAGNOSTIC SELESAI
echo ========================================
echo.
echo Next Steps:
echo 1. Jika ada [FAIL], jalankan setup-laragon.bat
echo 2. Test di browser: http://pos_griya.test/test-storage.php
echo 3. Jika masih error, screenshot hasil ini
echo.
pause
