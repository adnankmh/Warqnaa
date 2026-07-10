@echo off
cd /d %~dp0
echo Checking Warqna APK readiness...
node tools/pwa-check.js
php -l app\Http\Controllers\MobileApiController.php
echo If all OK, run apk-build-windows.bat
pause
