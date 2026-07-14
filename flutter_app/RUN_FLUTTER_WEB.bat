@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Warqna Flutter V0.2.5 Build 181

where flutter >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Flutter SDK was not found in PATH.
  echo Install Flutter and run flutter doctor before continuing.
  pause
  exit /b 1
)

if not exist "web\index.html" (
  echo [INFO] Creating the missing Flutter Web platform files...
  call flutter create . --platforms=web --project-name warqna_mobile --org com.warqna --no-pub
  if errorlevel 1 goto :fail
)

call flutter pub get
if errorlevel 1 goto :fail

if exist "..\tools\verify_flutter_lock.py" (
  where py >nul 2>nul
  if not errorlevel 1 (
    py -3 ..\tools\verify_flutter_lock.py pubspec.lock google_mobile_ads=7.0.0 flutter_webrtc=1.4.0 firebase_core=4.11.0 firebase_messaging=16.4.1
  ) else (
    python ..\tools\verify_flutter_lock.py pubspec.lock google_mobile_ads=7.0.0 flutter_webrtc=1.4.0 firebase_core=4.11.0 firebase_messaging=16.4.1
  )
  if errorlevel 1 goto :fail
)

set "API_URL=http://127.0.0.1:8006/api/mobile/v1"
echo [INFO] Starting Warqna V0.2.5 build 181
echo [INFO] API: %API_URL%
call flutter run -d chrome ^
  --dart-define=WARQNA_API_URL=%API_URL% ^
  --dart-define=WARQNA_PRODUCTION_MODE=false ^
  --dart-define=WARQNA_APP_VERSION=0.2.5 ^
  --dart-define=WARQNA_APP_BUILD=181
if errorlevel 1 goto :fail
exit /b 0

:fail
echo.
echo [ERROR] Flutter Web failed. Read the first compiler error above.
pause
exit /b 1
