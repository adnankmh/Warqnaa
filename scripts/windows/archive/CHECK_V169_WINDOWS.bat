@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/5] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/5] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/5] Testing Flutter CI/API compatibility...
python tools\test_flutter_ci_contract.py || goto :fail
echo [4/5] Running source preflight...
python tools\validate_release.py || goto :fail
echo [5/5] Finished.
echo Warqna v169 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v169 preflight failed. Read the error above.
pause
exit /b 1
