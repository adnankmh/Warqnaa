#!/usr/bin/env bash
set -euo pipefail
STAMP="$(date +%Y%m%d_%H%M%S)"
DEST="${BACKUP_DIR:-./backups}/$STAMP"
mkdir -p "$DEST"
: "${DB_HOST:?DB_HOST is required}" "${DB_DATABASE:?DB_DATABASE is required}" "${DB_USERNAME:?DB_USERNAME is required}" "${DB_PASSWORD:?DB_PASSWORD is required}"
export PGPASSWORD="$DB_PASSWORD"
pg_dump -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -Fc "$DB_DATABASE" > "$DEST/database.dump"
tar -czf "$DEST/storage.tar.gz" storage/app/public 2>/dev/null || true
sha256sum "$DEST"/* > "$DEST/SHA256SUMS"
find "${BACKUP_DIR:-./backups}" -mindepth 1 -maxdepth 1 -type d -mtime +"${BACKUP_RETENTION_DAYS:-14}" -exec rm -rf {} +
echo "Backup created: $DEST"
