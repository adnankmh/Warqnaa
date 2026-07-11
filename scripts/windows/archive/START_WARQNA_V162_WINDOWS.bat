@echo off
setlocal
cd /d "%~dp0"
title Warqna v162 Launcher
call CHECK_V162_WINDOWS.bat
if errorlevel 1 exit /b 1
if not exist backend-laravel\start-windows.bat (echo Backend launcher missing.& pause & exit /b 1)
if not exist flutter_app\RUN_FLUTTER_WEB.bat (echo Flutter launcher missing.& pause & exit /b 1)
start "Warqna Backend v162" cmd /k "cd /d ""%~dp0backend-laravel"" && call start-windows.bat"
timeout /t 4 /nobreak >nul
start "Warqna Flutter v162" cmd /k "cd /d ""%~dp0flutter_app"" && call RUN_FLUTTER_WEB.bat"
