@echo off
start "PHP-8000" cmd /k "cd /d D:\UPLYFT\uplifyt && D:\UPLYFT\php-8.3.31\php.exe artisan serve --host=127.0.0.1 --port=8000"
start "PHP-8001" cmd /k "cd /d D:\UPLYFT\uplifyt && D:\UPLYFT\php-8.3.31\php.exe artisan serve --host=127.0.0.1 --port=8001"
start "PHP-8002" cmd /k "cd /d D:\UPLYFT\uplifyt && D:\UPLYFT\php-8.3.31\php.exe artisan serve --host=127.0.0.1 --port=8002"
start "PHP-8003" cmd /k "cd /d D:\UPLYFT\uplifyt && D:\UPLYFT\php-8.3.31\php.exe artisan serve --host=127.0.0.1 --port=8003"
start "Vite" cmd /k "cd /d D:\UPLYFT\uplifyt && npx vite --host 127.0.0.1"
