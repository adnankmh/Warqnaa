#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
python3 tools/verify_release_versions.py
python3 tools/validate_release.py
echo "Warqna v166 source package passed local preflight."
