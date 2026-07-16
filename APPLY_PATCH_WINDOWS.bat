@echo off
setlocal
set "TARGET=%~1"
if "%TARGET%"=="" (echo Please pass the Warqnaa project folder.& exit /b 1)
xcopy "%~dp0files\*" "%TARGET%\" /E /I /Y >nul
for /f "usebackq delims=" %%F in ("%~dp0DELETE_FILES.txt") do if not "%%F"=="" del /Q "%TARGET%\%%F" 2>nul
echo Patch applied successfully.
