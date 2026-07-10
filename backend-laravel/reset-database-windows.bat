@echo off

echo Ensuring Laravel writable folders exist...
if not exist bootstrap\cache mkdir bootstrap\cache
if not exist storage mkdir storage
if not exist storage\app mkdir storage\app
if not exist storage\app\public mkdir storage\app\public
if not exist storage\framework mkdir storage\framework
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\cache\data mkdir storage\framework\cache\data
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\testing mkdir storage\framework\testing
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\logs mkdir storage\logs
type nul > bootstrap\cache\.gitkeep
type nul > storage\framework\cache\.gitkeep
type nul > storage\framework\cache\data\.gitkeep
type nul > storage\framework\sessions\.gitkeep
type nul > storage\framework\views\.gitkeep
type nul > storage\logs\.gitkeep

setlocal
chcp 65001 >nul
cd /d "%~dp0"
title Warqna Reset Database

echo This will reset the database and seed fresh data.
echo Press CTRL+C to cancel, or any key to continue.
pause >nul

call php artisan migrate:fresh --seed
if errorlevel 1 (
  echo ERROR: database reset failed.
  pause
  exit /b 1
)

echo Database reset completed.
pause
