@echo off
title Uplyft Unified Portal System
echo ===================================================
echo Starting Uplyft Unified Portal (Single Port)
echo - Unified Portal   : http://127.0.0.1:8000/
echo   - Principal      : http://127.0.0.1:8000/principal/login
echo   - Faculty        : http://127.0.0.1:8000/faculty/login
echo   - Student        : http://127.0.0.1:8000/student/login
echo - Global Admin     : http://127.0.0.1:8000/globaladmin/login
echo ===================================================
echo.
set "PHP=d:\UPLYFT\php\php.exe"
set "DIR=d:\UPLYFT"

echo Launching Uplyft Unified Server (Port 8000)...
start "Uplyft Unified Portal" cmd.exe /k "cd /d %DIR% && "%PHP%" artisan serve --host=127.0.0.1 --port=8000 --no-reload"

echo Launching Vite Asset Compiler...
start "Uplyft Vite" cmd.exe /k "cd /d %DIR% && npm run dev -- --host 127.0.0.1"

echo.
echo ===================================================
echo Unified Uplyft Portal is active on Port 8000!
echo ===================================================
