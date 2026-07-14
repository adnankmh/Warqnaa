@echo off
setlocal EnableExtensions
cd /d "%~dp0flutter_app"

where flutter >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Flutter is not available in PATH.
  pause
  exit /b 1
)

call flutter run -d chrome ^
  --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1 ^
  --dart-define=WARQNA_PRODUCTION_MODE=false ^
  --dart-define=WARQNA_APP_VERSION=0.2.5 ^
  --dart-define=WARQNA_APP_BUILD=181

pause
