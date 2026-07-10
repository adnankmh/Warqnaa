@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Warqna Flutter v142

where flutter >nul 2>nul
if %errorlevel% neq 0 (
  echo ERROR: Flutter SDK was not found in PATH.
  echo Install Flutter SDK or use GitHub Actions without Android Studio.
  pause
  exit /b 1
)

if not exist "web\index.html" (
  flutter create . --platforms=web,android,ios --project-name warqna_mobile --org com.warqna
)

call flutter pub get
if %errorlevel% neq 0 (
  echo ERROR: flutter pub get failed.
  pause
  exit /b 1
)

set "API_URL=http://127.0.0.1:8006/api/mobile/v1"
echo Starting Warqna Flutter Web with API: %API_URL%
call flutter run -d chrome --dart-define=WARQNA_API_URL=%API_URL%
pause
