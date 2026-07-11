@echo off
setlocal
cd /d "%~dp0"
echo [1/3] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/3] Running source preflight...
python tools\validate_release.py || goto :fail
echo [3/3] Finished.
echo Warqna v166 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v166 preflight failed. Read the error above.
pause
exit /b 1
