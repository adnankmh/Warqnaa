@echo off
setlocal EnableExtensions
cd /d "%~dp0\..\..\.."
title Warqna V0.2.5 Build 181 Launcher

echo ==================================================
echo Warqna V0.2.5 Build 181 - Local Launcher
echo ==================================================

echo [1/4] Running source quality gate...
where py >nul 2>nul
if not errorlevel 1 (
  py -3 tools\quality_gate.py --skip-flutter
) else (
  python tools\quality_gate.py --skip-flutter
)
if errorlevel 1 goto :fail

echo [2/4] Checking Laravel dependencies...
if not exist "backend-laravel\vendor\autoload.php" (
  echo [INFO] Laravel dependencies are missing. Starting first-time setup...
  call "backend-laravel\setup-windows.bat"
  if errorlevel 1 goto :fail
)

echo [3/4] Starting Laravel and socket services...
start "Warqna Backend V0.2.5" cmd /k "cd /d ""%CD%\backend-laravel"" && call start-windows.bat"
timeout /t 5 /nobreak >nul

echo [4/4] Starting Flutter Web...
start "Warqna Flutter V0.2.5" cmd /k "cd /d ""%CD%\flutter_app"" && call RUN_FLUTTER_WEB.bat"

echo.
echo [READY] Launcher windows were opened.
echo Backend: http://127.0.0.1:8006
echo Keep the backend and socket windows open.
exit /b 0

:fail
echo.
echo [FAILED] Warqna could not start. Fix the first error shown above.
pause
exit /b 1
