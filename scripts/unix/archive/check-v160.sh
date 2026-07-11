#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
python3 tools/validate_release.py
echo "Warqna v160 source preflight passed."
