@echo off
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_v022_economy_rooms_clubs_engines_contract.py || goto :fail
python tools\validate_release.py || goto :fail
echo Warqna V0.2.2 build 178 source patch passed local preflight.
exit /b 0
:fail
echo Warqna V0.2.2 build 178 preflight failed.
exit /b 1
