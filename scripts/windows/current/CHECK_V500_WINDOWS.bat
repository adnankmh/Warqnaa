@echo off
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_clean_root_policy.py || goto :fail
python tools\test_v175_xp_challenges_pasha_designer_contract.py || goto :fail
python tools\test_v176_daily_pack_inventory_contract.py || goto :fail
python tools\test_v02_daily_prize_boxes_contract.py || goto :fail
python tools\test_v05_global_contract.py || goto :fail
python tools\validate_release.py || goto :fail
echo Warqna V0.5 build 500 source package passed local preflight.
exit /b 0
:fail
echo Warqna V0.5 build 500 preflight failed.
exit /b 1
