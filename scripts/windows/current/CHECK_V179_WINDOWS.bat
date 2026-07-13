@echo off
cd /d "%~dp0\..\..\.."
python tools\validate_release.py
if errorlevel 1 goto fail
echo Warqna V0.3 build 179 source package passed local preflight.
exit /b 0
:fail
echo Warqna V0.3 build 179 preflight failed.
exit /b 1
