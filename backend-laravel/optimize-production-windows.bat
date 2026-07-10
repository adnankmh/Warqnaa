@echo off
cd /d %~dp0
echo Optimizing Warqna for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo Done.
pause
