#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
cd "$ROOT"
python3 tools/verify_release_versions.py
python3 tools/test_clean_root_policy.py
python3 tools/test_flutter_ci_contract.py
python3 tools/test_v170_contract.py
python3 tools/test_v171_controller_references.py
python3 tools/validate_release.py
echo "Warqna v171 source package passed local preflight."
