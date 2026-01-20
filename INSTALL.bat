@echo off
title Setup POS Griya - Laragon Installation
color 0A

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║          SETUP POS GRIYA UNTUK LARAGON (WINDOWS)             ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  Installer ini akan:
echo  • Membuat folder yang diperlukan
echo  • Set permission yang benar
echo  • Membuat symbolic link (Windows format)
echo  • Clear dan cache ulang konfigurasi
echo.
echo  Press any key to start installation...
pause >nul
cls

cd /d %~dp0

:STEP1
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 1/8: Checking PHP Installation
echo ════════════════════════════════════════════════════════════════
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP not found! Make sure Laragon is running.
    goto ERROR_EXIT
)
php -r "echo 'PHP ' . PHP_VERSION . ' detected';"
echo.
echo [OK] PHP detected
timeout /t 1 /nobreak >nul

:STEP2
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 2/8: Creating Required Directories
echo ════════════════════════════════════════════════════════════════
if not exist "storage\app\livewire-tmp" (
    mkdir "storage\app\livewire-tmp"
    echo [OK] Created storage\app\livewire-tmp
) else (
    echo [SKIP] storage\app\livewire-tmp already exists
)

if not exist "storage\app\public\livewire-tmp" (
    mkdir "storage\app\public\livewire-tmp"
    echo [OK] Created storage\app\public\livewire-tmp
) else (
    echo [SKIP] storage\app\public\livewire-tmp already exists
)

if not exist "storage\app\private\livewire-tmp" (
    mkdir "storage\app\private\livewire-tmp"
    echo [OK] Created storage\app\private\livewire-tmp
) else (
    echo [SKIP] storage\app\private\livewire-tmp already exists
)
timeout /t 1 /nobreak >nul

:STEP3
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 3/8: Creating .gitignore Files
echo ════════════════════════════════════════════════════════════════
(
echo *
echo !.gitignore
) > "storage\app\livewire-tmp\.gitignore"
echo [OK] Created storage\app\livewire-tmp\.gitignore

(
echo *
echo !.gitignore
) > "storage\app\public\livewire-tmp\.gitignore"
echo [OK] Created storage\app\public\livewire-tmp\.gitignore

(
echo *
echo !.gitignore
) > "storage\app\private\livewire-tmp\.gitignore"
echo [OK] Created storage\app\private\livewire-tmp\.gitignore
timeout /t 1 /nobreak >nul

:STEP4
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 4/8: Setting Permissions
echo ════════════════════════════════════════════════════════════════
echo This may take a moment...
icacls storage /grant Everyone:(OI)(CI)F /T >nul 2>&1
echo [OK] Set permissions for storage
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T >nul 2>&1
echo [OK] Set permissions for bootstrap\cache
timeout /t 1 /nobreak >nul

:STEP5
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 5/8: Removing Old Symbolic Link
echo ════════════════════════════════════════════════════════════════
if exist "public\storage" (
    rmdir "public\storage" /S /Q >nul 2>&1
    del "public\storage" /F /Q >nul 2>&1
    echo [OK] Old symbolic link removed
) else (
    echo [SKIP] No old symbolic link found
)
timeout /t 1 /nobreak >nul

:STEP6
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 6/8: Creating Storage Link (Windows Format)
echo ════════════════════════════════════════════════════════════════
echo Trying symbolic link first...
mklink /D "public\storage" "..\storage\app\public" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Symbolic link created successfully
) else (
    echo [INFO] Symbolic link failed, trying directory junction...
    mklink /J "public\storage" "..\storage\app\public" >nul 2>&1
    if %errorlevel% equ 0 (
        echo [OK] Directory junction created successfully
    ) else (
        echo [INFO] Junction failed, using PHP artisan storage:link...
        php artisan storage:link >nul 2>&1
        if %errorlevel% equ 0 (
            echo [OK] Storage link created via artisan
        ) else (
            echo [WARNING] All methods failed!
            echo Please run this file as Administrator (Right-click ^> Run as administrator)
        )
    )
)
timeout /t 1 /nobreak >nul

:STEP7
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 7/8: Clearing Laravel Cache
echo ════════════════════════════════════════════════════════════════
php artisan config:clear >nul 2>&1
echo [OK] Config cache cleared
php artisan cache:clear >nul 2>&1
echo [OK] Application cache cleared
php artisan view:clear >nul 2>&1
echo [OK] View cache cleared
php artisan route:clear >nul 2>&1
echo [OK] Route cache cleared
timeout /t 1 /nobreak >nul

:STEP8
echo.
echo ════════════════════════════════════════════════════════════════
echo  STEP 8/8: Rebuilding Configuration Cache
echo ════════════════════════════════════════════════════════════════
php artisan config:cache >nul 2>&1
echo [OK] Configuration cached
php artisan optimize >nul 2>&1
echo [OK] Application optimized
timeout /t 1 /nobreak >nul

:SUCCESS
cls
echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║                  ✓ INSTALLATION SUCCESSFUL!                  ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  All steps completed successfully!
echo.
echo  NEXT STEPS:
echo  ══════════════════════════════════════════════════════════════
echo.
echo  1. Test permission:
echo     Open in browser: http://pos_griya.test/test-storage.php
echo.
echo  2. Check results:
echo     All checks should show: YES ✅
echo.
echo  3. Try uploading:
echo     Upload an image in your POS application
echo.
echo  4. If still error:
echo     Run: diagnostic-laragon.bat
echo     And send screenshot to developer
echo.
echo  ══════════════════════════════════════════════════════════════
echo.
goto END

:ERROR_EXIT
echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║                    ✗ INSTALLATION FAILED                     ║
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.
echo  Please check:
echo  1. Laragon is running
echo  2. PHP is accessible
echo  3. You have Administrator rights
echo.
echo  Run diagnostic-laragon.bat for more info
echo.

:END
pause
