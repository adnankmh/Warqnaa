#!/usr/bin/env bash
set -euo pipefail
SOURCE="${1:?Usage: restore-production.sh /path/to/backup-folder}"
: "${DB_HOST:?DB_HOST is required}" "${DB_DATABASE:?DB_DATABASE is required}" "${DB_USERNAME:?DB_USERNAME is required}" "${DB_PASSWORD:?DB_PASSWORD is required}"
export PGPASSWORD="$DB_PASSWORD"
sha256sum -c "$SOURCE/SHA256SUMS"
pg_restore --clean --if-exists -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -d "$DB_DATABASE" "$SOURCE/database.dump"
if [ -f "$SOURCE/storage.tar.gz" ]; then tar -xzf "$SOURCE/storage.tar.gz"; fi
php artisan optimize:clear
php artisan migrate --force
php artisan optimize
