@echo off
cd /d %~dp0
echo Clearing Laravel caches...
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Done.
pause
