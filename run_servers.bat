@echo off
title Uplyft Unified Ecosystem
echo ===================================================
echo Starting Uplyft Unified Multi-Module Ecosystem
echo - Global Admin Panel (Port 8000)
echo - Principal and Staff Portal (Port 8001)
echo - LMS, Faculty and Staff Portal (Port 8002)
echo - Student Portal (Port 8003)
echo ===================================================
echo.
set "PHP=d:\UPLYFT\php-8.3.31\php.exe"
set "DIR=d:\UPLYFT\uplifyt"
echo Launching Port 8000 (Global Admin)...
start "Uplyft Global Admin" cmd.exe /k "cd /d %DIR% && "%PHP%" artisan serve --host=127.0.0.1 --port=8000 --no-reload"
echo Launching Port 8001 (Principal Portal)...
start "Uplyft Principal Portal" cmd.exe /k "cd /d %DIR% && "%PHP%" artisan serve --host=127.0.0.1 --port=8001 --no-reload"
echo Launching Port 8002 (LMS Faculty and Staff)...
start "Uplyft LMS Portal" cmd.exe /k "cd /d %DIR% && "%PHP%" artisan serve --host=127.0.0.1 --port=8002 --no-reload"
echo Launching Port 8003 (Student Portal)...
start "Uplyft Student Portal" cmd.exe /k "cd /d %DIR% && "%PHP%" artisan serve --host=127.0.0.1 --port=8003 --no-reload"
echo Launching Vite Asset Compiler...
start "Uplyft Vite" cmd.exe /k "cd /d %DIR% && npm run dev -- --host 127.0.0.1"
echo.
echo ===================================================
echo All Uplyft Portals are active!
echo - Global Admin Portal  : http://127.0.0.1:8000/
echo - Principal Portal     : http://127.0.0.1:8001/
echo - LMS Faculty/Staff   : http://127.0.0.1:8002/
echo - Student Portal      : http://127.0.0.1:8003/
echo ===================================================
