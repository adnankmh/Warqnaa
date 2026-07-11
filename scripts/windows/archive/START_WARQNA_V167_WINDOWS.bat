@echo off
setlocal
cd /d "%~dp0..\..\.."
call scripts\windows\current\CHECK_V167_WINDOWS.bat || exit /b 1
cd flutter_app
if exist RUN_FLUTTER_WEB.bat call RUN_FLUTTER_WEB.bat
