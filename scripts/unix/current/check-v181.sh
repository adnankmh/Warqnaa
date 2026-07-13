#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_v025_complete_contract.py
python3 tools/validate_release.py
echo "Warqna V0.2.5 build 181 full-source package passed local preflight."
