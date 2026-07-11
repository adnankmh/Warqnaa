#!/usr/bin/env python3
"""Warqna release preflight that uses only the Python standard library."""
from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXPECTED_VERSION = "1.56.0"
EXPECTED_BUILD = 156
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



def check_ci_hotfixes() -> None:
    composer_path = ROOT / "backend-laravel/composer.json"
    composer = json.loads(composer_path.read_text(encoding="utf-8"))
    if composer.get("license") != "proprietary":
        fail('composer.json must declare license "proprietary" for strict validation')

    workflow_path = ROOT / ".github/workflows/backend-ci.yml"
    workflow = workflow_path.read_text(encoding="utf-8")
    required = [
        "composer validate --no-check-lock --strict",
        "cp .env.production.example .env",
        "DB_PASSWORD=ci-placeholder",
        "docker compose -f docker-compose.production.yml config",
        "rm -f .env",
    ]
    for needle in required:
        if needle not in workflow:
            fail(f"Backend CI hotfix is incomplete: {needle}")
    if (ROOT / "backend-laravel/.env").exists():
        fail("A runtime backend-laravel/.env must not be committed")
    print("[OK] Composer strict-validation and Compose CI environment hotfixes")


def check_v156_regressions() -> None:
    engine = (ROOT / "flutter_app/lib/engines/tarneeb_engine.dart").read_text(encoding="utf-8")
    for needle in [
        "if (biddingSeat == null || contract == null)",
        "phase = TarneebPhase.roundEnd;",
        "تم حفظ آخر لَمّة بأمان",
    ]:
        if needle not in engine:
            fail(f"Tarneeb final-trick guard missing: {needle}")

    migration_144 = (ROOT / "backend-laravel/database/migrations/2026_07_10_000144_expand_store_item_category.php").read_text(encoding="utf-8")
    for needle in ["category_v156", "dropColumn('category')", "renameColumn('category_v156', 'category')"]:
        if needle not in migration_144:
            fail(f"Store category migration is incomplete: {needle}")

    migration_145 = (ROOT / "backend-laravel/database/migrations/2026_07_10_000145_curated_mobile_games.php").read_text(encoding="utf-8")
    if re.search(r"'Premium (?:Dark|Classic|Purple)'\s*,\s*'theme'", migration_145):
        fail("Migration 145 still writes unsupported category 'theme'")

    database_config = (ROOT / "backend-laravel/config/database.php").read_text(encoding="utf-8")
    if "'pgsql' => [" not in database_config:
        fail("Production PostgreSQL connection is missing from config/database.php")

    store = (ROOT / "backend-laravel/app/Http/Controllers/StoreController.php").read_text(encoding="utf-8")
    if "profile_cover" not in store or "active_profile_cover" not in store:
        fail("Profile-cover activation support is incomplete")

    print("[OK] Tarneeb, store migration, profile-cover and PostgreSQL regressions")

def check_required_files() -> None:
    required = [
        "flutter_app/lib/main.dart",
        "flutter_app/lib/production_v153.dart",
        "backend-laravel/artisan",
        "backend-laravel/composer.json",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        "START_HERE_V156_AR.md",
        "RELEASE_MANIFEST_V156.json",
        "backend-laravel/database/migrations/2026_07_10_000144_expand_store_item_category.php",
        "backend-laravel/database/migrations/2026_07_11_000156_tests_migrations_hotfix.php",
        "GITHUB_UPLOAD_V156_AR.md",
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
    check_ci_hotfixes()
    check_v156_regressions()
    check_secrets()
    check_dart_structure()
    print("[PASS] Source package preflight completed successfully")


if __name__ == "__main__":
    main()
