#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_v174_offline_progression_navigation_contract.py
python3 tools/test_v175_xp_challenges_pasha_designer_contract.py
python3 tools/validate_release.py
echo "Warqna v175 source package passed local preflight."
