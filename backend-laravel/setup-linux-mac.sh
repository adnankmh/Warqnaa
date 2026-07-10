#!/usr/bin/env bash

echo "Ensuring Laravel writable folders exist..."
mkdir -p bootstrap/cache storage/app storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs
touch bootstrap/cache/.gitkeep storage/framework/cache/.gitkeep storage/framework/cache/data/.gitkeep storage/framework/sessions/.gitkeep storage/framework/views/.gitkeep storage/logs/.gitkeep
chmod -R 775 bootstrap/cache storage || true

set -e
cd "$(dirname "$0")"
echo "=================================================="
echo "Warqna Laravel Platform v68 - First Setup"
echo "=================================================="

command -v php >/dev/null || { echo "ERROR: PHP 8.2+ is required"; exit 1; }
command -v composer >/dev/null || { echo "ERROR: Composer is required"; exit 1; }
command -v npm >/dev/null || { echo "ERROR: Node.js / npm is required"; exit 1; }

[ -f .env ] || cp .env.example .env
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

echo "Installing Composer dependencies..."
composer config audit.block-insecure false >/dev/null 2>&1 || true
composer config audit.block-insecure false || true
composer install --no-security-blocking || composer install --no-blocking || composer install

echo "Ensuring APP_KEY without using key:generate..."
php -r '$f=".env"; $c=file_exists($f)?file_get_contents($f):""; if(!preg_match("/^APP_KEY=base64:.+/m",$c)){ $key="base64:".base64_encode(random_bytes(32)); if(preg_match("/^APP_KEY=.*/m",$c)){ $c=preg_replace("/^APP_KEY=.*/m","APP_KEY=".$key,$c); } else { $c .= PHP_EOL."APP_KEY=".$key.PHP_EOL; } file_put_contents($f,$c); echo "APP_KEY created".PHP_EOL; } else { echo "APP_KEY already exists".PHP_EOL; }'

echo "Installing Node dependencies..."
npm install

chmod -R 775 storage bootstrap/cache database || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Setup completed. Run ./start-linux-mac.sh"
