@echo off
setlocal
cd /d "%~dp0"
title Warqna v161 Source Preflight
echo ========================================
echo Warqna v161 - Source Package Preflight
echo ========================================
where python >nul 2>nul || (echo Python 3 is required.& pause & exit /b 1)
python tools\validate_release.py
if errorlevel 1 (echo.& echo Preflight failed.& pause & exit /b 1)
echo.
echo Warqna v161 source preflight passed.
pause
