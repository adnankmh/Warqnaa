@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/9] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/9] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/9] Testing Flutter CI compatibility...
python tools\test_flutter_ci_contract.py || goto :fail
echo [4/9] Testing v170 feature contract...
python tools\test_v170_contract.py || goto :fail
echo [5/9] Testing v171 controller references...
python tools\test_v171_controller_references.py || goto :fail
echo [6/9] Testing v172 brand/table preservation...
python tools\test_v172_brand_table_contract.py || goto :fail
echo [7/9] Testing v173 online engagement contract...
python tools\test_v173_online_engagement_contract.py || goto :fail
echo [8/9] Running source preflight...
python tools\validate_release.py || goto :fail
echo [9/9] Finished.
echo Warqna v173 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v173 preflight failed. Read the error above.
pause
exit /b 1
