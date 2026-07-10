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
title Warqna Setup v142

echo ==================================================
echo Warqna Laravel Platform v142 - Safe Windows Setup
echo ==================================================
echo Folder: %CD%
echo.

REM Add common XAMPP PHP path if available
if exist "C:\xampp\php\php.exe" set "PATH=C:\xampp\php;%PATH%"
if exist "C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe" set "PATH=C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64;%PATH%"

where php >nul 2>nul
if %errorlevel% neq 0 (
  echo ERROR: PHP was not found.
  echo If you use XAMPP, make sure this file exists: C:\xampp\php\php.exe
  echo Then close this window and run setup-windows.bat again.
  pause
  exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
  echo ERROR: Composer was not found.
  echo Install Composer for Windows, then close CMD and try again.
  pause
  exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
  echo ERROR: npm was not found.
  echo Install Node.js 18 or newer, then close CMD and try again.
  pause
  exit /b 1
)

if not exist ".env" (
  echo Creating .env file...
  copy /Y ".env.example" ".env" >nul
) else (
  echo .env already exists.
)

if not exist "database" mkdir "database"
if not exist "database\database.sqlite" (
  echo Creating SQLite database file...
  type nul > "database\database.sqlite"
) else (
  echo SQLite database already exists.
)

echo.
echo Installing Composer dependencies...
call composer config audit.block-insecure false >nul 2>nul
call composer install --no-security-blocking
if %errorlevel% neq 0 (
  echo.
  echo First composer install attempt failed. Trying compatibility mode...
  call composer install --no-blocking
)
if %errorlevel% neq 0 (
  echo.
  echo Second composer install attempt failed. Trying standard install after disabling security blocking...
  call composer install
)
if %errorlevel% neq 0 (
  echo ERROR: composer install failed.
  echo Tip: run: composer self-update
  echo Then run setup-windows.bat again.
  pause
  exit /b 1
)

echo.
echo Creating Laravel APP_KEY if missing...
php create-app-key.php
if %errorlevel% neq 0 (
  echo ERROR: APP_KEY creation failed.
  pause
  exit /b 1
)

echo.
echo Installing Node dependencies...
call npm install
if %errorlevel% neq 0 (
  echo ERROR: npm install failed.
  pause
  exit /b 1
)

echo.
echo Clearing Laravel cache...
call php artisan optimize:clear

echo.
echo Rebuilding database and seed data...
call php artisan migrate:fresh --seed --force
if %errorlevel% neq 0 (
  echo ERROR: database migration or seed failed.
  pause
  exit /b 1
)

echo.
echo ==================================================
echo Setup completed successfully.
echo Admin username: Adnan
echo Admin email: adnanasd63@gmail.com
echo Admin password: Adnan123
echo Now run: start-windows.bat
echo ==================================================
pause
