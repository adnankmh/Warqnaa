@echo off
setlocal
cd /d "%~dp0"
title Warqna v164 Launcher
call CHECK_V164_WINDOWS.bat
if errorlevel 1 exit /b 1
start "Warqna Backend v164" cmd /k "cd /d ""%~dp0backend-laravel"" && call start-windows.bat"
timeout /t 2 >nul
start "Warqna Flutter v164" cmd /k "cd /d ""%~dp0flutter_app"" && call RUN_FLUTTER_WEB.bat"
