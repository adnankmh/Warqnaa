@echo off
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_v030_contract.py || goto :fail
python tools\validate_v030_static.py || goto :fail
python tools\test_v02_daily_prize_boxes_contract.py || goto :fail
php backend-laravel\tools\test-engine-adapters.php || goto :fail
php backend-laravel\tools\test-v142-rule-cores.php || goto :fail
php backend-laravel\tools\test-v030-banakil.php || goto :fail
python tools\validate_release.py || goto :fail
echo Warqnaa V0.3 v180 source package passed local preflight.
exit /b 0
:fail
echo Warqnaa V0.3 v180 preflight failed.
exit /b 1
