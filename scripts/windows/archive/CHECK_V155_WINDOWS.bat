@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo ===============================================
echo Warqna v155 - Source Package Preflight
echo ===============================================
where py >nul 2>nul
if %errorlevel%==0 (
  py -3 tools\validate_release.py
) else (
  where python >nul 2>nul
  if errorlevel 1 (
    echo Python 3 is required for this local preflight.
    echo GitHub Actions will run the same checks after upload.
    if not "%WARQNA_NO_PAUSE%"=="1" pause
    exit /b 1
  )
  python tools\validate_release.py
)
if errorlevel 1 (
  echo.
  echo Validation failed. Do not upload this folder until the reported item is fixed.
  if not "%WARQNA_NO_PAUSE%"=="1" pause
  exit /b 1
)
echo.
echo Validation passed.
if not "%WARQNA_NO_PAUSE%"=="1" pause
exit /b 0
