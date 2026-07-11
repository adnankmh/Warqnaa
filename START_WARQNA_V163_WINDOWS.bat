@echo off
setlocal
cd /d "%~dp0"
title Warqna v163 Launcher
call CHECK_V163_WINDOWS.bat
if errorlevel 1 exit /b 1
start "Warqna Backend v163" cmd /k "cd /d ""%~dp0backend-laravel"" && call start-windows.bat"
timeout /t 2 >nul
start "Warqna Flutter v163" cmd /k "cd /d ""%~dp0flutter_app"" && call RUN_FLUTTER_WEB.bat"
