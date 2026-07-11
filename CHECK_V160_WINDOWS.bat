@echo off
setlocal
cd /d "%~dp0"
echo ================================================
echo Warqna v160 - Source Package Preflight
echo ================================================
where python >nul 2>nul
if errorlevel 1 (
  echo Python was not found. Install Python 3 or run the GitHub Actions checks.
  exit /b 1
)
python tools\validate_release.py
if errorlevel 1 exit /b 1
echo.
echo Warqna v160 source preflight passed.
endlocal
