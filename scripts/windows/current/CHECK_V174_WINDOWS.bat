@echo off
setlocal
cd /d "%~dp0..\..\.."
echo [1/10] Verifying release versions...
python tools\verify_release_versions.py || goto :fail
echo [2/10] Testing clean-root policy...
python tools\test_clean_root_policy.py || goto :fail
echo [3/10] Testing Flutter CI compatibility...
python tools\test_flutter_ci_contract.py || goto :fail
echo [4/10] Testing v170 feature contract...
python tools\test_v170_contract.py || goto :fail
echo [5/10] Testing v171 controller references...
python tools\test_v171_controller_references.py || goto :fail
echo [6/10] Testing v172 brand/table preservation...
python tools\test_v172_brand_table_contract.py || goto :fail
echo [7/10] Testing inherited v173 engagement assets...
python tools\test_v173_online_engagement_contract.py || goto :fail
echo [8/10] Testing v174 offline, XP and direct-room contract...
python tools\test_v174_offline_progression_navigation_contract.py || goto :fail
echo [9/10] Running source preflight...
python tools\validate_release.py || goto :fail
echo [10/10] Finished.
echo Warqna v174 source package passed local preflight.
pause
exit /b 0
:fail
echo.
echo Warqna v174 preflight failed. Read the error above.
pause
exit /b 1
