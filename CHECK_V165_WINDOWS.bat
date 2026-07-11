@echo off
setlocal
cd /d "%~dp0"
echo Checking Warqna v165 package...
python tools\validate_release.py
if errorlevel 1 (
  echo Warqna v165 preflight failed.
  pause
  exit /b 1
)
echo Warqna v165 preflight passed.
pause
