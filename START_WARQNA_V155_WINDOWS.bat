@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Warqna v155 Launcher

echo ==================================================
echo Warqna v155 - Complete Local Launcher
echo ==================================================

echo [1/4] Validating source package...
set "WARQNA_NO_PAUSE=1"
call CHECK_V155_WINDOWS.bat
if errorlevel 1 (
  set "WARQNA_NO_PAUSE="
  pause
  exit /b 1
)
set "WARQNA_NO_PAUSE="

echo [2/4] Checking Laravel dependencies...
if not exist "backend-laravel\vendor\autoload.php" (
  echo Laravel dependencies are not installed yet.
  echo Starting the safe setup now...
  call "backend-laravel\setup-windows.bat"
  if errorlevel 1 exit /b 1
)

echo [3/4] Starting Laravel and socket services...
start "Warqna Backend v155" cmd /k "cd /d ""%~dp0backend-laravel"" && call start-windows.bat"
timeout /t 5 /nobreak >nul

echo [4/4] Starting Flutter Web...
start "Warqna Flutter v155" cmd /k "cd /d ""%~dp0flutter_app"" && call RUN_FLUTTER_WEB.bat"

echo.
echo Warqna launch windows were opened.
echo Backend: http://127.0.0.1:8006
echo Flutter will open in Chrome after packages are prepared.
pause
