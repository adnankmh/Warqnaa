#!/usr/bin/env python3
"""Warqna v159 source-package preflight using the Python standard library.

Framework-level Laravel and Flutter tests remain authoritative in GitHub Actions.
This preflight rejects known regressions before a commit reaches CI.
"""
from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXPECTED_VERSION = "1.59.0"
EXPECTED_BUILD = 159
TEXT_SUFFIXES = {
    ".dart", ".php", ".py", ".js", ".ts", ".yml", ".yaml", ".json",
    ".md", ".html", ".css", ".xml", ".gradle", ".properties", ".sh", ".bat",
}
SKIP_DIRS = {".git", "vendor", "node_modules", "build", ".dart_tool", ".idea", ".vscode"}
CONFLICT_PATTERNS = ("<<<<<<<", "=======", ">>>>>>>")


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def read(rel: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"Required file missing: {rel}")
    return path.read_text(encoding="utf-8")


def iter_text_files():
    for path in ROOT.rglob("*"):
        if not path.is_file() or any(part in SKIP_DIRS for part in path.parts):
            continue
        if path.suffix.lower() in TEXT_SUFFIXES or path.name in {"Dockerfile", ".gitignore"}:
            yield path


def require(rel: str, needles: list[str]) -> None:
    text = read(rel)
    for needle in needles:
        if needle not in text:
            fail(f"Missing contract in {rel}: {needle}")


def forbid(rel: str, needles: list[str]) -> None:
    text = read(rel)
    for needle in needles:
        if needle in text:
            fail(f"Forbidden regression in {rel}: {needle}")


def check_required_files() -> None:
    required = [
        "flutter_app/lib/main.dart",
        "flutter_app/pubspec.yaml",
        "flutter_app/lib/services/api_client.dart",
        "backend-laravel/artisan",
        "backend-laravel/composer.json",
        "backend-laravel/phpunit.xml",
        "backend-laravel/config/database.php",
        "backend-laravel/app/Services/WarqnaPro/PlayActionNormalizer.php",
        "backend-laravel/app/Services/GameEngine/TarneebRules.php",
        "backend-laravel/tests/Unit/EnvironmentSanityTest.php",
        "backend-laravel/database/migrations/2026_07_11_000158_ci_lock_and_laravel_quality_hotfix.php",
        ".github/workflows/backend-ci.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
        "tools/verify_flutter_lock.py",
        "START_HERE_V159_AR.md",
        "GITHUB_UPLOAD_V159_AR.md",
        "RELEASE_MANIFEST_V159.json",
        "QUALITY_REPORT_V159_AR.md",
        "CHECK_V159_WINDOWS.bat",
        "check-v159.sh",
        "START_WARQNA_V159_WINDOWS.bat",
        "backend-laravel/app/Http/Controllers/Controller.php",
        "backend-laravel/tests/Unit/ControllerFoundationTest.php",
        "backend-laravel/tools/verify_http_foundation.php",
    ]
    missing = [item for item in required if not (ROOT / item).is_file()]
    if missing:
        fail("Missing release files: " + ", ".join(missing))
    print("[OK] Required v159 release files")


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
    require("flutter_app/lib/main.dart", [
        "Future<String?> login(String loginId, String password",
        "return this.login(loginId, password, offline: true);",
        "final fallback = await this.login(loginId, password, offline: true);",
    ])
    forbid("flutter_app/lib/main.dart", [
        "Future<String?> login(String login, String password",
        "return login(login, password, offline: true);",
        "await login(login, password, offline: true);",
    ])
    print("[OK] Merge-safe login fallback")


def check_versions() -> None:
    checks = {
        "flutter_app/pubspec.yaml": f"version: {EXPECTED_VERSION}+{EXPECTED_BUILD}",
        "flutter_app/lib/services/api_client.dart": f"defaultValue: '{EXPECTED_VERSION}'",
        "backend-laravel/config/warqna.php": f"env('WARQNA_VERSION', '{EXPECTED_VERSION}')",
        "backend-laravel/.env.example": f"WARQNA_BUILD={EXPECTED_BUILD}",
        "backend-laravel/.env.production.example": f"WARQNA_VERSION={EXPECTED_VERSION}",
        ".github/workflows/flutter-android.yml": f"WARQNA_APP_BUILD={EXPECTED_BUILD}",
        ".github/workflows/flutter-web-pages.yml": f"WARQNA_APP_BUILD={EXPECTED_BUILD}",
        ".github/workflows/flutter-ios.yml": f"WARQNA_APP_BUILD={EXPECTED_BUILD}",
        ".github/workflows/production-release-check.yml": f"version: {EXPECTED_VERSION}+{EXPECTED_BUILD}",
    }
    for rel, needle in checks.items():
        if needle not in read(rel):
            fail(f"Version mismatch in {rel}; expected {needle}")
    require("backend-laravel/config/warqna.php", [f"env('WARQNA_BUILD', {EXPECTED_BUILD})"])
    require("flutter_app/lib/services/api_client.dart", [f"defaultValue: {EXPECTED_BUILD}"])
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


def check_yaml_basics() -> None:
    count = 0
    for pattern in ("*.yml", "*.yaml"):
        for path in ROOT.rglob(pattern):
            if any(part in SKIP_DIRS for part in path.parts):
                continue
            text = path.read_text(encoding="utf-8")
            if "\t" in text:
                fail(f"YAML contains tab indentation: {path.relative_to(ROOT)}")
            if path.parent.name == "workflows" and not re.search(r"(?m)^name:\s*.+$", text):
                fail(f"Workflow has no top-level name: {path.relative_to(ROOT)}")
            count += 1
    print(f"[OK] YAML structural audit ({count} files)")


def check_secrets() -> None:
    forbidden_names = {".env", "key.properties", "upload-keystore.jks"}
    found = []
    for path in ROOT.rglob("*"):
        if not path.is_file() or any(part in SKIP_DIRS for part in path.parts):
            continue
        if path.name in forbidden_names or path.suffix.lower() in {".jks", ".keystore", ".p12", ".pem"}:
            found.append(str(path.relative_to(ROOT)))
    if found:
        fail("Secret-bearing files must not ship: " + ", ".join(found))
    print("[OK] No runtime secrets or signing files")


def check_flutter_lock_verification() -> None:
    pubspec = read("flutter_app/pubspec.yaml")
    for needle in ["google_mobile_ads: 7.0.0", "flutter_webrtc: 1.4.0"]:
        if needle not in pubspec:
            fail(f"Pinned Flutter dependency missing: {needle}")

    workflow = read(".github/workflows/flutter-android.yml")
    for needle in [
        "python3 ../tools/verify_flutter_lock.py pubspec.lock",
        "google_mobile_ads=7.0.0",
        "flutter_webrtc=1.4.0",
        "name: warqna-v159-android",
    ]:
        if needle not in workflow:
            fail(f"Android lock verification incomplete: {needle}")
    if "grep -A2" in workflow:
        fail("Brittle grep -A2 lockfile verification returned")

    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/verify_flutter_lock.py"), "--self-test"],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Lock parser self-test failed: " + result.stdout.strip())
    print("[OK] Robust Flutter pubspec.lock parser and pinned dependencies")


def check_sqlite_memory_contract() -> None:
    db = read("backend-laravel/config/database.php")
    for needle in ["$db !== ':memory:'", "!str_starts_with($db, 'file:')", "$db = base_path($db);"]:
        if needle not in db:
            fail(f"SQLite memory/URI guard incomplete: {needle}")
    phpunit = read("backend-laravel/phpunit.xml")
    if '<env name="DB_DATABASE" value=":memory:"/>' not in phpunit:
        fail("PHPUnit is not configured for SQLite :memory:")
    if re.search(r"\$db\s*=\s*base_path\([\'\"]:memory:[\'\"]\)", db):
        fail("SQLite :memory: is still converted to a filesystem path")
    print("[OK] SQLite :memory: and URI preservation")


def check_backend_ci() -> None:
    composer = json.loads(read("backend-laravel/composer.json"))
    if composer.get("license") != "proprietary":
        fail('composer.json must declare license "proprietary"')
    workflow = read(".github/workflows/backend-ci.yml")
    for needle in [
        "composer validate --no-check-lock --strict",
        "test -d tests/Feature",
        "test -d tests/Unit",
        "php artisan migrate:fresh --seed --force",
        "php artisan test",
        "cp .env.production.example .env",
        "docker compose -f docker-compose.production.yml config",
        "rm -f .env",
    ]:
        if needle not in workflow:
            fail(f"Backend CI contract incomplete: {needle}")
    print("[OK] Composer, PHPUnit, migration and Docker CI contracts")


def check_http_controller_foundation() -> None:
    require("backend-laravel/app/Http/Controllers/Controller.php", [
        "namespace App\\Http\\Controllers;",
        "abstract class Controller",
    ])
    unit = read("backend-laravel/tests/Unit/ControllerFoundationTest.php")
    for needle in [
        "MobileAdminController::class",
        "MobileApiController::class",
        "MobileAuthRecoveryController::class",
        "MobileGameController::class",
        "MobileModerationController::class",
        "MobilePlatformController::class",
        "MobileSafetyController::class",
        "MobileSocialController::class",
        "MobileVoiceController::class",
        "MobileAccountController::class",
        "LegalPageController::class",
    ]:
        if needle not in unit:
            fail(f"HTTP controller inheritance regression guard missing: {needle}")
    verify = read("backend-laravel/tools/verify_http_foundation.php")
    for needle in ["class_exists($base)", "is_subclass_of($controller, $base)"]:
        if needle not in verify:
            fail(f"HTTP controller CI preflight incomplete: {needle}")
    print("[OK] Laravel HTTP base controller and autoload guards")


def check_stable_deal_tests() -> None:
    for rel in [
        "backend-laravel/tests/Feature/V128StoreGameplayNavTest.php",
        "backend-laravel/tests/Feature/V131PremiumFinalFixesTest.php",
    ]:
        text = read(rel)
        for forbidden in ["assertGreaterThanOrEqual(2,$high)", "count(array_filter($hand"]:
            if forbidden in text:
                fail(f"Probabilistic card-strength assertion remains in {rel}: {forbidden}")
        for required in ["assertCount(52,$cards)", "assertCount(52,array_unique($cards))", "$card->id()"]:
            if required not in text:
                fail(f"Deterministic full-deck invariant missing in {rel}: {required}")
        if "(string)$card" in text:
            fail(f"Card object string cast would crash the deterministic test in {rel}")
    print("[OK] Deterministic duplicate-free card-deal tests")


def check_gameplay_fixes() -> None:
    normalizer = read("backend-laravel/app/Services/WarqnaPro/PlayActionNormalizer.php")
    for needle in [
        "private const SUIT_ALIASES",
        "public function normalizeCardId",
        "public function canonicalCard",
        "preg_split('/[_\\s\\-\\/|:]+/u'",
        "A_clubs becoming a corrupted value",
    ]:
        if needle not in normalizer:
            fail(f"Card normalizer regression guard missing: {needle}")
    # The old implementation used chained str_replace calls that could turn
    # `clubs` into a malformed value after replacing the single-letter alias.
    if re.search(r"str_replace\([^\n]+['\"]c['\"]", normalizer, re.I):
        fail("Recursive single-letter card replacement returned")

    tarneeb = read("backend-laravel/app/Services/GameEngine/TarneebRules.php")
    for needle in [
        "private PlayActionNormalizer $normalizer;",
        "if(isset($state['_tarneeb_v2'])",
        "Keep the authoritative engine representation synchronized",
        "return $this->normalizer->canonicalCard($card,$hand);",
    ]:
        if needle not in tarneeb:
            fail(f"Tarneeb compatibility fix missing: {needle}")
    print("[OK] Unicode card normalization and Tarneeb state compatibility")


def check_api_pwa_contracts() -> None:
    require("backend-laravel/routes/api.php", [
        "Route::prefix('mobile')->group",
        "legacyHealth",
        "legacyBootstrap",
        "legacyGames",
        "Route::prefix('mobile/v1')->group",
    ])
    require("backend-laravel/app/Http/Controllers/MobileGameController.php", [
        "if (Schema::hasTable('games'))",
        "maintenance windows even when the database has not been migrated yet",
        "$dbGames = collect();",
    ])
    require("backend-laravel/routes/web.php", [
        "Route::get('/manifest.webmanifest'",
        "Route::get('/sw.js'",
        "Route::get('/offline.html'",
        "Keep the static sitemap valid even when the database is unavailable",
    ])
    for rel in [
        "backend-laravel/public/manifest.webmanifest",
        "backend-laravel/public/sw.js",
        "backend-laravel/public/offline.html",
    ]:
        if not (ROOT / rel).is_file():
            fail(f"PWA asset missing: {rel}")
    print("[OK] Versioned/legacy Mobile API and explicit PWA routes")


def check_product_contract_tests() -> None:
    catalog = read("backend-laravel/app/Services/Games/GameCatalog.php")
    # Count only top-level catalog entries in the literal return block.
    block = catalog.split("return [", 1)[1].split("];", 1)[0]
    keys = re.findall(r"(?m)^\s{12}'([a-z0-9_]+)'\s*=>\s*\[", block)
    expected = [
        "tarneeb", "syrian_tarneeb", "tarneeb_400", "trix", "trix_partner",
        "trix_complex", "hand", "hand_partner", "saudi_hand", "banakil", "baloot", "basra",
    ]
    if keys != expected:
        fail(f"Current curated game contract changed unexpectedly: {keys}")

    require("backend-laravel/tests/Feature/V122CatalogAndEnginesTest.php", [
        "$this->assertSame($expected,array_keys($games));",
        "'domino','ludo','jackaroo','chess'",
    ])
    require("backend-laravel/tests/Feature/V131PremiumFinalFixesTest.php", [
        "assertCount(12,GameCatalog::all())",
    ])
    require("backend-laravel/tests/Feature/V128StoreGameplayNavTest.php", [
        "assertCount(50,$service->tableSkins())",
        "assertCount(40,$service->cardBacks())",
    ])
    require("backend-laravel/resources/views/store/index.blade.php", [
        'data-warqna-store-contract="v158"',
    ])

    stale_patterns = [
        ("backend-laravel/tests/Feature/V122CatalogAndEnginesTest.php", "assertGreaterThanOrEqual(40"),
        ("backend-laravel/tests/Feature/V131PremiumFinalFixesTest.php", "assertCount(15"),
        ("backend-laravel/tests/Feature/V128StoreGameplayNavTest.php", "assertCount(40,$service->tableSkins())"),
    ]
    for rel, needle in stale_patterns:
        if needle in read(rel):
            fail(f"Stale historical test contract remains in {rel}: {needle}")
    print("[OK] Current 12-game, 50-table, 40-card-back product contract")


def check_android_ci_order() -> None:
    workflow = read(".github/workflows/flutter-android.yml")
    for needle in ["actions/checkout@v5", "actions/setup-java@v5", "actions/upload-artifact@v6"]:
        if needle not in workflow:
            fail(f"Android official action version missing: {needle}")
    java_block = workflow.split("- name: Set up Java 17", 1)[1].split("- name:", 1)[0]
    if "cache: gradle" in java_block:
        fail("setup-java still caches Gradle before the Android project exists")
    if workflow.index("Create a clean Android platform") > workflow.index("Resolve and verify Flutter packages"):
        fail("Android platform must exist before package/build-dependent phases")
    print("[OK] Android project generation and Java/Gradle CI order")


def check_dart_structure() -> None:
    for path in (ROOT / "flutter_app/lib").rglob("*.dart"):
        text = path.read_text(encoding="utf-8")
        scrubbed = re.sub(r"//.*?$|/\*.*?\*/|'(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"", "", text, flags=re.M | re.S)
        for left, right in [("(", ")"), ("[", "]"), ("{", "}")]:
            if scrubbed.count(left) != scrubbed.count(right):
                fail(f"Unbalanced {left}{right} in {path.relative_to(ROOT)}")
    print("[OK] Dart delimiter structure")


def main() -> None:
    print(f"Warqna {EXPECTED_VERSION}+{EXPECTED_BUILD} preflight")
    check_required_files()
    check_conflicts()
    check_login_fix()
    check_versions()
    check_json()
    check_yaml_basics()
    check_flutter_lock_verification()
    check_sqlite_memory_contract()
    check_backend_ci()
    check_http_controller_foundation()
    check_stable_deal_tests()
    check_gameplay_fixes()
    check_api_pwa_contracts()
    check_product_contract_tests()
    check_android_ci_order()
    check_secrets()
    check_dart_structure()
    print("[PASS] Warqna v159 source-package preflight completed successfully")


if __name__ == "__main__":
    main()
