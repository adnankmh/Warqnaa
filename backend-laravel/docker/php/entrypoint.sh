#!/usr/bin/env sh
set -eu
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ "${WARQNA_RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
exec "$@"
