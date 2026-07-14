@echo off
setlocal EnableExtensions
cd /d "%~dp0flutter_app"

echo =====================================================
echo Warqna V0.2.5 Flutter compile repair
echo =====================================================

where flutter >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Flutter is not available in PATH.
  echo Run flutter doctor after adding Flutter\bin to PATH.
  pause
  exit /b 1
)

echo [1/6] Removing the old dependency resolution...
if exist pubspec.lock del /f /q pubspec.lock
if exist .dart_tool rmdir /s /q .dart_tool
if exist build rmdir /s /q build

echo [2/6] Cleaning Flutter artifacts...
call flutter clean
if errorlevel 1 goto :failed

echo [3/6] Resolving the pinned safe dependencies...
call flutter pub get
if errorlevel 1 goto :failed

echo [4/6] Verifying critical package versions...
where python >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Python is required to verify pubspec.lock.
  goto :failed
)
python ..\tools\verify_flutter_lock.py pubspec.lock google_mobile_ads=7.0.0 flutter_webrtc=1.4.0 firebase_core=4.11.0 firebase_messaging=16.4.1
if errorlevel 1 goto :failed

echo [5/6] Running Flutter analyzer...
call flutter analyze --no-fatal-infos --no-fatal-warnings
if errorlevel 1 goto :failed

echo [6/6] Repair completed successfully.
echo Run RUN_FLUTTER_WEB_V025.bat to start the web app.
pause
exit /b 0

:failed
echo.
echo [ERROR] The repair stopped because a command failed.
echo Copy the complete output and send it for diagnosis.
pause
exit /b 1
