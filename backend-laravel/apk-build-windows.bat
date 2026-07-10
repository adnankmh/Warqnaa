@echo off
cd /d %~dp0
echo ===================================================
echo Warqna Zone APK preparation - Capacitor Android
echo ===================================================
where node >nul 2>nul
if errorlevel 1 (
 echo Node.js is required. Install Node.js first.
 pause
 exit /b 1
)
echo Installing npm packages...
npm install
echo Checking PWA files...
npm run pwa:check
if errorlevel 1 (
 echo PWA check failed.
 pause
 exit /b 1
)
echo Preparing Laravel caches...
php artisan optimize:clear
php artisan migrate --force
echo Initializing Capacitor if needed...
if not exist android (
 npx cap add android
)
echo Syncing Android project...
npx cap sync android
echo.
echo Done. Open the android folder in Android Studio or run:
echo cd android
echo gradlew assembleDebug
echo.
pause
