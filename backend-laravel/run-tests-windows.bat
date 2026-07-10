@echo off
cd /d %~dp0
echo Running Warqna automated tests...
if not exist vendor (
 echo Vendor folder missing. Run setup-windows.bat first.
 pause
 exit /b 1
)
php artisan test
pause
