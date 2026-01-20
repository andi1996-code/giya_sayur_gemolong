@echo off
title Sync Storage Folder
color 0B

:: Check if using copy method
if not exist "public\storage" (
    echo ERROR: public\storage tidak ada!
    echo Jalankan USE-COPY-STORAGE.bat terlebih dahulu
    pause
    exit /b 1
)

cd /d %~dp0

echo Syncing storage folder...
xcopy "storage\app\public" "public\storage" /E /I /Y /D >nul 2>&1
echo [OK] Storage synced
echo.
echo Files updated: %date% %time%
