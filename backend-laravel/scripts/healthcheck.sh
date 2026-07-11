#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${1:-http://127.0.0.1:8080}"
curl --fail --silent --show-error "$BASE_URL/health" >/dev/null
curl --fail --silent --show-error "$BASE_URL/ready" >/dev/null
curl --fail --silent --show-error "$BASE_URL/api/mobile/v1/app-config?platform=web" >/dev/null
echo "Warqna health checks passed: $BASE_URL"
