#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_clean_root_policy.py
python3 tools/test_v175_xp_challenges_pasha_designer_contract.py
python3 tools/test_v176_daily_pack_inventory_contract.py
python3 tools/test_v02_daily_prize_boxes_contract.py
python3 tools/test_v05_global_contract.py
python3 tools/validate_release.py
echo "Warqna V0.5 build 500 source package passed local preflight."
