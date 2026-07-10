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

setlocal EnableExtensions
cd /d "%~dp0"
title Warqna Manual Start
if exist "C:\xampp\php\php.exe" set "PATH=C:\xampp\php;%PATH%"
cmd /k "php -v && echo. && echo Run these two commands in two windows: && echo npm run socket && echo php artisan serve --host=127.0.0.1 --port=8000"
