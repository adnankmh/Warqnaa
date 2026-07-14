@echo off
cd /d "%~dp0\..\..\.."
python tools\verify_release_versions.py || goto :fail
python tools\test_v025_complete_contract.py || goto :fail
python tools\validate_release.py || goto :fail
echo Warqna V0.2.5 build 181 full-source package passed local preflight.
exit /b 0
:fail
echo Warqna V0.2.5 build 181 preflight failed.
exit /b 1
