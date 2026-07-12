#!/usr/bin/env python3
"""Semantic controller-reference regression contract for Warqna v171.

The former GitHub check compared one exact source string and failed whenever a
valid callback used a block body (for example, closing a sheet before opening
friends). This test validates the actual showFriends calls and their arguments
without depending on whitespace or callback formatting.
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MAIN = ROOT / "flutter_app/lib/main.dart"
WORKFLOWS = [
    ROOT / ".github/workflows/flutter-web-pages.yml",
    ROOT / ".github/workflows/flutter-android.yml",
    ROOT / ".github/workflows/flutter-ios.yml",
    ROOT / ".github/workflows/production-release-check.yml",
]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def main() -> None:
    source = MAIN.read_text(encoding="utf-8")
    calls = re.findall(
        r"showFriends\s*\(\s*context\s*,\s*([A-Za-z_][A-Za-z0-9_.]*)\s*\)",
        source,
    )
    if not calls:
        fail("No showFriends(context, ...) calls were found in lib/main.dart")

    allowed = {"controller", "widget.controller"}
    invalid = sorted({argument for argument in calls if argument not in allowed})
    if invalid:
        fail("Invalid AppController arguments passed to showFriends: " + repr(invalid))

    if calls.count("widget.controller") < 1:
        fail("A StatefulWidget page must call showFriends with widget.controller")
    if calls.count("controller") < 2:
        fail("Expected controller-based friend actions in profile/lobby contexts")

    compact = re.sub(r"\s+", " ", source)
    direct_widget = "onTap: () => showFriends(context, widget.controller)"
    if direct_widget not in compact:
        fail("Games-page friends action is not bound to widget.controller")

    profile_pattern = re.compile(
        r"onPressed\s*:\s*\(\)\s*\{\s*"
        r"Navigator\.pop\(context\);\s*"
        r"showFriends\(context,\s*controller\);\s*\}",
        re.S,
    )
    if not profile_pattern.search(source):
        fail("Profile friends action must close the sheet then use controller")

    for workflow in WORKFLOWS:
        text = workflow.read_text(encoding="utf-8")
        if "test_v171_controller_references.py" not in text:
            fail(f"Workflow does not run the v171 controller contract: {workflow.name}")
        if "Incorrect controller references in lib/main.dart" in text:
            fail(f"Brittle literal controller check returned in {workflow.name}")

    print(
        "[PASS] Controller references are semantically correct: "
        f"{calls.count('controller')} controller, "
        f"{calls.count('widget.controller')} widget.controller"
    )


if __name__ == "__main__":
    main()
