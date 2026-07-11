#!/usr/bin/env python3
"""Warqna release preflight that uses only the Python standard library."""
from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXPECTED_VERSION = "1.54.0"
EXPECTED_BUILD = 154
TEXT_SUFFIXES = {
    ".dart", ".php", ".py", ".js", ".ts", ".yml", ".yaml", ".json",
    ".md", ".html", ".css", ".xml", ".gradle", ".properties", ".sh", ".bat",
}
SKIP_DIRS = {".git", "vendor", "node_modules", "build", ".dart_tool", ".idea", ".vscode"}
CONFLICT_PATTERNS = ("<<<<<<<", "=======", ">>>>>>>")


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def iter_text_files():
    for path in ROOT.rglob("*"):
        if not path.is_file() or any(part in SKIP_DIRS for part in path.parts):
            continue
        if path.suffix.lower() in TEXT_SUFFIXES or path.name in {"Dockerfile", ".gitignore"}:
            yield path


def check_conflicts() -> None:
    offenders: list[str] = []
    for path in iter_text_files():
        if path.suffix.lower() == ".md":
            continue
        text = path.read_text(encoding="utf-8", errors="ignore")
        for line_no, line in enumerate(text.splitlines(), start=1):
            stripped = line.lstrip()
            if any(stripped.startswith(marker) for marker in CONFLICT_PATTERNS):
                offenders.append(f"{path.relative_to(ROOT)}:{line_no}")
    if offenders:
        fail("Git conflict markers found: " + ", ".join(offenders[:20]))
    print("[OK] No Git conflict markers")


def check_login_fix() -> None:
    path = ROOT / "flutter_app/lib/main.dart"
    text = path.read_text(encoding="utf-8")
    required = [
        "Future<String?> login(String loginId, String password",
        "return this.login(loginId, password, offline: true);",
        "final fallback = await this.login(loginId, password, offline: true);",
    ]
    for needle in required:
        if needle not in text:
            fail(f"Missing merge-safe login implementation: {needle}")
    forbidden = [
        "Future<String?> login(String login, String password",
        "return login(login, password, offline: true);",
        "await login(login, password, offline: true);",
    ]
    for needle in forbidden:
        if needle in text:
            fail(f"Unsafe shadowed login call remains: {needle}")
    print("[OK] Login fallback is merge-safe and unambiguous")


def check_versions() -> None:
    checks = {
        ROOT / "flutter_app/pubspec.yaml": f"version: {EXPECTED_VERSION}+{EXPECTED_BUILD}",
        ROOT / "flutter_app/lib/services/api_client.dart": f"defaultValue: '{EXPECTED_VERSION}'",
        ROOT / "backend-laravel/config/warqna.php": f"env('WARQNA_VERSION', '{EXPECTED_VERSION}')",
        ROOT / ".github/workflows/flutter-android.yml": f"WARQNA_APP_BUILD={EXPECTED_BUILD}",
        ROOT / ".github/workflows/flutter-web-pages.yml": f"WARQNA_APP_BUILD={EXPECTED_BUILD}",
    }
    for path, needle in checks.items():
        if not path.is_file():
            fail(f"Required file missing: {path.relative_to(ROOT)}")
        if needle not in path.read_text(encoding="utf-8"):
            fail(f"Version mismatch in {path.relative_to(ROOT)}; expected {needle}")
    print(f"[OK] Version consistency {EXPECTED_VERSION}+{EXPECTED_BUILD}")


def check_json() -> None:
    count = 0
    for path in ROOT.rglob("*.json"):
        if any(part in SKIP_DIRS for part in path.parts):
            continue
        try:
            json.loads(path.read_text(encoding="utf-8"))
        except Exception as exc:
            fail(f"Invalid JSON in {path.relative_to(ROOT)}: {exc}")
        count += 1
    print(f"[OK] JSON syntax ({count} files)")


def check_secrets() -> None:
    forbidden_names = {".env", "key.properties", "upload-keystore.jks"}
    found = []
    for path in ROOT.rglob("*"):
        if not path.is_file() or any(part in SKIP_DIRS for part in path.parts):
            continue
        if path.name in forbidden_names or path.suffix.lower() in {".jks", ".keystore", ".p12", ".pem"}:
            found.append(str(path.relative_to(ROOT)))
    if found:
        fail("Secret-bearing files must not ship in the source package: " + ", ".join(found))
    print("[OK] No committed runtime secrets or signing files")


def check_required_files() -> None:
    required = [
        "flutter_app/lib/main.dart",
        "flutter_app/lib/production_v153.dart",
        "backend-laravel/artisan",
        "backend-laravel/composer.json",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        "START_HERE_V154_AR.md",
        "RELEASE_MANIFEST_V154.json",
    ]
    missing = [item for item in required if not (ROOT / item).is_file()]
    if missing:
        fail("Missing release files: " + ", ".join(missing))
    print("[OK] Required release files are present")


def check_dart_structure() -> None:
    # Lightweight delimiter audit; the authoritative analyzer runs in GitHub Actions.
    for path in (ROOT / "flutter_app/lib").rglob("*.dart"):
        text = path.read_text(encoding="utf-8")
        scrubbed = re.sub(r"//.*?$|/\*.*?\*/|'(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"", "", text, flags=re.M | re.S)
        for left, right in [("(", ")"), ("[", "]"), ("{", "}")]:
            if scrubbed.count(left) != scrubbed.count(right):
                fail(f"Unbalanced {left}{right} in {path.relative_to(ROOT)}")
    print("[OK] Dart delimiter structure")


def main() -> None:
    print(f"Warqna v{EXPECTED_VERSION} build {EXPECTED_BUILD} preflight")
    check_required_files()
    check_conflicts()
    check_login_fix()
    check_versions()
    check_json()
    check_secrets()
    check_dart_structure()
    print("[PASS] Source package preflight completed successfully")


if __name__ == "__main__":
    main()
