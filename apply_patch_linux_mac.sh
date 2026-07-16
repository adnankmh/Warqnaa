#!/usr/bin/env bash
set -euo pipefail
TARGET="${1:-}"
if [[ -z "$TARGET" ]]; then echo "Pass the Warqnaa project folder."; exit 1; fi
cp -a "$(dirname "$0")/files/." "$TARGET/"
while IFS= read -r rel; do [[ -z "$rel" ]] || rm -f "$TARGET/$rel"; done < "$(dirname "$0")/DELETE_FILES.txt"
echo "Patch applied successfully."
