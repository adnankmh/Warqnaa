#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_v022_economy_rooms_clubs_engines_contract.py
python3 tools/validate_release.py
echo "Warqna V0.2.2 build 178 source patch passed local preflight."
