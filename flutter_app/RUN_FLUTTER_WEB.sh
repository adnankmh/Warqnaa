#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
command -v flutter >/dev/null || { echo "Flutter SDK is required, or use GitHub Actions."; exit 1; }
flutter create . --platforms=web,android,ios --project-name warqna_mobile --org com.warqna
flutter pub get
flutter run -d chrome --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1
