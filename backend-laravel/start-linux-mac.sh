#!/usr/bin/env bash

echo "Ensuring Laravel writable folders exist..."
mkdir -p bootstrap/cache storage/app storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs
touch bootstrap/cache/.gitkeep storage/framework/cache/.gitkeep storage/framework/cache/data/.gitkeep storage/framework/sessions/.gitkeep storage/framework/views/.gitkeep storage/logs/.gitkeep
chmod -R 775 bootstrap/cache storage || true

set -e
(npm run socket) &
php artisan serve --host=127.0.0.1 --port=8000
