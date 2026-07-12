@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/6] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/6] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/6] Testing Flutter API compatibility...
python tools\test_flutter_ci_contract.py || goto :fail
echo [4/6] Testing v170 product contract...
python tools\test_v170_contract.py || goto :fail
echo [5/6] Running source preflight...
python tools\validate_release.py || goto :fail
echo [6/6] Finished.
echo Warqna v170 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v170 preflight failed. Read the error above.
pause
exit /b 1
