@echo off
title Uplyft Unified Portal
echo Starting Uplyft Unified Portal on Port 8000...
start "PHP-8000" cmd /k "cd /d D:\UPLYFT && D:\UPLYFT\php\php.exe artisan serve --host=127.0.0.1 --port=8000 --no-reload"
start "Vite" cmd /k "cd /d D:\UPLYFT && npx vite --host 127.0.0.1"
echo Active at http://127.0.0.1:8000/
