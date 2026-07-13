#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../../.."
python3 tools/validate_release.py
echo "Warqna V0.3 build 179 source package passed local preflight."
