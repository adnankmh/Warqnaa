#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_v030_contract.py
python3 tools/validate_v030_static.py
python3 tools/test_v02_daily_prize_boxes_contract.py
php backend-laravel/tools/test-engine-adapters.php
php backend-laravel/tools/test-v142-rule-cores.php
php backend-laravel/tools/test-v030-banakil.php
python3 tools/validate_release.py
echo "Warqnaa V0.3 v180 source package passed local preflight."
