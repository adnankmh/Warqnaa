@echo off
setlocal
cd /d "%~dp0\..\..\.."
echo Starting Warqnaa V0.3.2 v183...
if exist flutter_app\RUN_FLUTTER_WEB.bat (
  call flutter_app\RUN_FLUTTER_WEB.bat
) else (
  echo Flutter web launcher was not found.
  pause
  exit /b 1
)
