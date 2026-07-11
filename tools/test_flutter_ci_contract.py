#!/usr/bin/env python3
"""Reject known Flutter CI regressions before `flutter analyze` runs.

This is intentionally dependency-free so GitHub Actions and local source checks can
catch API drift and protected-member misuse before the Flutter SDK is installed.
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MAIN = ROOT / "flutter_app/lib/main.dart"
NOTIFICATIONS = ROOT / "flutter_app/lib/services/app_notifications.dart"
POLISH = ROOT / "flutter_app/lib/v166_polish.dart"


def fail(message: str) -> None:
    raise SystemExit(f"[FAIL] {message}")


def require(text: str, needle: str, label: str) -> None:
    if needle not in text:
        fail(f"{label} missing: {needle}")


def forbid(text: str, needle: str, label: str) -> None:
    if needle in text:
        fail(f"{label} returned: {needle}")


def main() -> None:
    main = MAIN.read_text(encoding="utf-8")
    notifications = NOTIFICATIONS.read_text(encoding="utf-8")
    polish = POLISH.read_text(encoding="utf-8")

    # Translation helper must use the actual central localization API.
    forbid(main, "v166Text(", "Undefined v166Text translation helper")
    require(main, "L.t(localeCode, 'activeGame')", "Active-game translation")
    require(main, "L.t(localeCode, 'friendsChat')", "Friends-chat translation")

    # flutter_local_notifications 22 converted initialize/show to named args.
    require(notifications, "settings: const InitializationSettings(", "Named initialize settings")
    require(notifications, "id: DateTime.now().millisecondsSinceEpoch", "Named notification id")
    require(notifications, "notificationDetails: details", "Named notification details")
    if re.search(r"_local\.initialize\(\s*const\s+InitializationSettings", notifications):
        fail("Positional flutter_local_notifications initialize API returned")
    if re.search(r"_local\.show\(\s*DateTime\.now", notifications):
        fail("Positional flutter_local_notifications show API returned")

    # Widgets must not invoke ChangeNotifier protected methods directly.
    forbid(main, "widget.controller.notifyListeners()", "Protected notifyListeners call")
    require(main, "void addNotice(AppNotice notice)", "Public notice mutation method")
    require(main, "widget.controller.addNotice(AppNotice(", "Room-chat notice mutation")

    # Known analyzer lints from the v168 failure should stay fixed.
    forbid(main, "import 'dart:typed_data';", "Unnecessary typed_data import")
    forbid(main, "points[number]", "Unrelated map-key lookup")
    forbid(polish, "'${service.diagnostics['peers']", "Unnecessary string interpolation")

    print("[PASS] Flutter v169 CI/API regression contract")


if __name__ == "__main__":
    main()
