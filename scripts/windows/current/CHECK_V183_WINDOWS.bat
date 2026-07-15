@echo off
setlocal
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_clean_root_policy.py || goto :fail
python tools\test_flutter_ci_contract.py || goto :fail
python tools\test_v02_daily_prize_boxes_contract.py || goto :fail
python tools\test_v030_contract.py || goto :fail
python tools\test_v170_contract.py || goto :fail
python tools\test_v171_controller_references.py || goto :fail
python tools\test_v172_brand_table_contract.py || goto :fail
python tools\test_v173_online_engagement_contract.py || goto :fail
python tools\test_v174_offline_progression_navigation_contract.py || goto :fail
python tools\test_v175_xp_challenges_pasha_designer_contract.py || goto :fail
python tools\test_v176_daily_pack_inventory_contract.py || goto :fail
python tools\test_v181_ci_regression_contract.py || goto :fail
python tools\test_v182_rewards_contract.py
python tools\test_v183_overhaul_contract.py || goto :fail
php backend-laravel\tools\test-v183-engine-overhaul.php || goto :fail || goto :fail
python tools\validate_v030_static.py || goto :fail
php backend-laravel\tools\test-engine-adapters.php || goto :fail
php backend-laravel\tools\test-v142-rule-cores.php || goto :fail
php backend-laravel\tools\test-v030-banakil.php || goto :fail
python tools\validate_release.py || goto :fail
echo Warqnaa V0.3.2 v183 source package passed local preflight.
exit /b 0
:fail
echo Warqnaa V0.3.2 v183 preflight failed.
exit /b 1
