@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/4] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/4] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/4] Running source preflight...
python tools\validate_release.py || goto :fail
echo [4/4] Finished.
echo Warqna v168 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v168 preflight failed. Read the error above.
pause
exit /b 1
