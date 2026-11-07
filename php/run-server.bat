@echo off
REM ============================================================
REM  HikePass - Shortcut menjalankan PHP Built-in Server
REM  buka browser: http://127.0.0.1:8000/php/
REM ============================================================
setlocal

set "PHP_DIR=C:\tools\php"
set "PROJECT_DIR=%~dp0.."

if not exist "%PHP_DIR%\php.exe" (
    echo [ERROR] PHP tidak ditemukan di "%PHP_DIR%".
    echo         Edit variabel PHP_DIR pada file ini, atau jalankan:
    echo         php -S 127.0.0.1:8000 -t "%PROJECT_DIR%"
    pause
    exit /b 1
)

echo.
echo  HikePass PHP Server
echo  Docroot : %PROJECT_DIR%
echo  URL     : http://127.0.0.1:8000/php/
echo  Tekan CTRL+C untuk menghentikan server.
echo.

cd /d "%PROJECT_DIR%"
"%PHP_DIR%\php.exe" -S 127.0.0.1:8000 -t "%PROJECT_DIR%"

endlocal
