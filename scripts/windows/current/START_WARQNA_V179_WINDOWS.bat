@echo off
cd /d "%~dp0\..\..\.."
echo Starting Warqna V0.3 build 179...
start "Warqna Backend" cmd /k "cd backend-laravel && php artisan serve --host=127.0.0.1 --port=8006"
start "Warqna Flutter" cmd /k "cd flutter_app && flutter run -d chrome --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1"
