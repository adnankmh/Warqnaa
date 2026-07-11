@echo off
setlocal
cd /d "%~dp0"
title Warqna v160 Launcher
echo Warqna v160 - Complete Local Launcher
call CHECK_V160_WINDOWS.bat
if errorlevel 1 exit /b 1
if not exist backend-laravel\vendor\autoload.php (
  echo Backend dependencies are missing. Run backend-laravel\setup-windows.bat first.
  exit /b 1
)
start "Warqna Backend v160" cmd /k "cd /d ""%~dp0backend-laravel"" && call start-windows.bat"
timeout /t 2 /nobreak >nul
start "Warqna Flutter v160" cmd /k "cd /d ""%~dp0flutter_app"" && call RUN_FLUTTER_WEB.bat"
endlocal
