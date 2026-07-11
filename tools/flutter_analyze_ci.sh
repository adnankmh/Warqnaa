#!/usr/bin/env bash
set -uo pipefail
log_file="${RUNNER_TEMP:-/tmp}/warqna-flutter-analyze.log"
set +e
flutter analyze --no-fatal-infos --no-fatal-warnings 2>&1 | tee "$log_file"
status=${PIPESTATUS[0]}
set -e
if grep -Eq '^[[:space:]]*(error|warning)[[:space:]]+•' "$log_file"; then
  echo 'Flutter analyzer found a real error or warning.' >&2
  exit 1
fi
if [ "$status" -eq 0 ]; then
  exit 0
fi
if grep -Eq '^[[:space:]]*info[[:space:]]+•' "$log_file"; then
  echo 'Flutter analyzer returned a non-zero status for informational lints only; release continues.'
  exit 0
fi
echo "Flutter analyzer failed unexpectedly with exit code $status." >&2
exit "$status"
