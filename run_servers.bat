@echo off
title Uplyft Dev Servers
echo ===================================================
echo Starting Uplyft Local Development Servers
echo ===================================================
echo.
echo [1/2] Launching Laravel server on port 8000...
start "Uplyft Laravel" /Min "C:\php82\php.exe" artisan serve
echo.
echo [2/2] Launching Vite compiler on port 5173...
start "Uplyft Vite" /Min cmd.exe /c "npm run dev"
echo.
echo ===================================================
echo Dev servers are now running in the background!
echo - Student Admission Portal: http://127.0.0.1:8000/
echo - Direct Invoice PDF Route: http://127.0.0.1:8000/api/admissions/1/invoice
echo ===================================================
echo.
echo (Press any key to close this wrapper window. The servers will continue running in the background).
pause > nul
