@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Warqna V0.2.5 Quality Gate

where py >nul 2>nul
if not errorlevel 1 (
  py -3 tools\quality_gate.py
) else (
  where python >nul 2>nul
  if errorlevel 1 (
    echo [ERROR] Python 3 is required.
    pause
    exit /b 1
  )
  python tools\quality_gate.py
)

if errorlevel 1 (
  echo.
  echo [FAILED] Do not publish this source until the errors above are fixed.
  pause
  exit /b 1
)

echo.
echo [PASSED] Source contracts, PHP syntax, and available Flutter checks passed.
pause
exit /b 0
