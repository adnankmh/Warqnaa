@echo off
cd /d %~dp0
if not exist backups mkdir backups
set TS=%date:~-4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TS=%TS: =0%
echo Creating Warqna backup %TS%...
powershell -Command "Compress-Archive -Path app,config,database,public,resources,routes,tests,*.md,*.bat -DestinationPath backups\warqna_backup_%TS%.zip -Force"
echo Backup created in backups folder.
pause
