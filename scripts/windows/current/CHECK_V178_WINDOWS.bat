@echo off
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_v175_xp_challenges_pasha_designer_contract.py || goto :fail
python tools\test_v178_daily_pack_inventory_contract.py || goto :fail
python tools\validate_release.py || goto :fail
echo Warqna v178 source package passed local preflight.
exit /b 0
:fail
echo Warqna v178 preflight failed.
exit /b 1
