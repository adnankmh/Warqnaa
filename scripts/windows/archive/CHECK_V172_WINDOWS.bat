@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/8] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/8] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/8] Testing Flutter API compatibility...
python tools\test_flutter_ci_contract.py || goto :fail
echo [4/8] Testing v170 feature contract...
python tools\test_v170_contract.py || goto :fail
echo [5/8] Testing v171 controller references...
python tools\test_v171_controller_references.py || goto :fail
echo [6/8] Testing v172 brand and 40-table collection...
python tools\test_v172_brand_table_contract.py || goto :fail
echo [7/8] Running source preflight...
python tools\validate_release.py || goto :fail
echo [8/8] Finished.
echo Warqna v172 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v172 preflight failed. Read the error above.
pause
exit /b 1
