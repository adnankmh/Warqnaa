#!/usr/bin/env python3
"""Warqna source-package preflight using the Python standard library.

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
RELEASE_META = json.loads((ROOT / "RELEASE_VERSION.json").read_text(encoding="utf-8"))
EXPECTED_VERSION = str(RELEASE_META["version"])
EXPECTED_BUILD = int(RELEASE_META["build"])
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
        "backend-laravel/database/migrations/2026_07_11_000161_voice_mobile_social_progression.php",
        "backend-laravel/tests/Feature/V161VoiceSocialProgressionTest.php",
        "flutter_app/lib/data/countries.dart",
        "flutter_app/lib/services/voice_room_service.dart",
        "flutter_app/lib/v166_polish.dart",
        "flutter_app/lib/services/app_sounds.dart",
        "flutter_app/lib/services/app_notifications.dart",
        "flutter_app/lib/services/connection_diagnostics.dart",
        "backend-laravel/app/Http/Controllers/MobilePushController.php",
        "backend-laravel/app/Models/PushDevice.php",
        "backend-laravel/app/Services/Notifications/FirebasePushService.php",
        "backend-laravel/config/push.php",
        "backend-laravel/database/migrations/2026_07_12_000166_create_push_devices_table.php",
        "backend-laravel/tests/Feature/V166GlobalPolishContractTest.php",
        "docs/ar/product/VOICE_ANDROID_PUSH_SETUP_V166_AR.md",
        "docs/ar/validation/archive/VALIDATION_RESULTS_V166.txt",
        "docs/ar/product/PLAY_STORE_ASSETS_V166_AR.md",
        "assets/play-store/icon-512.png",
        "assets/play-store/feature-graphic-1024x500.png",
        "backend-laravel/app/Http/Controllers/SocialAuthController.php",
        "docs/ar/troubleshooting/VOICE_AND_SOCIAL_SETUP_V161_AR.md",
        "backend-laravel/app/Services/Account/AccountCancellationService.php",
        "backend-laravel/app/Console/Commands/PurgeCancelledAccounts.php",
        "backend-laravel/tests/Feature/V162AccountCancellationLifecycleTest.php",
        "backend-laravel/tests/Unit/V163CiRegressionContractTest.php",
        "tools/flutter_analyze_ci.sh",
        "tools/test_flutter_ci_contract.py",
        "tools/test_v171_controller_references.py",
        "tools/test_v172_brand_table_contract.py",
        "tools/test_v173_online_engagement_contract.py",
        "tools/test_v174_offline_progression_navigation_contract.py",
        "tools/test_v175_xp_challenges_pasha_designer_contract.py",
        "tools/test_v176_daily_pack_inventory_contract.py",
        "tools/test_v02_daily_prize_boxes_contract.py",
        "tools/test_v022_economy_rooms_clubs_engines_contract.py",
        "tools/test_v03_platform_contract.py",
        "flutter_app/lib/v03_release.dart",
        "backend-laravel/app/Services/GameEngine/GlobalEngines/BalancedDealV03.php",
        "backend-laravel/app/Services/WarqnaPro/ChallengeCampaignService.php",
        "backend-laravel/app/Services/Leveling/LevelUpRewardService.php",
        "backend-laravel/database/factories/UserFactory.php",
        "backend-laravel/database/seeders/V03DemoPlayersSeeder.php",
        "backend-laravel/database/migrations/2026_07_13_140000_v03_challenge_campaigns_level_rewards_and_room_presence.php",
        "backend-laravel/tests/Feature/V03GlobalReleaseTest.php",
        "flutter_app/lib/v176_release.dart",
        "flutter_app/lib/v02_release.dart",
        "backend-laravel/app/Models/PrizeBox.php",
        "backend-laravel/app/Services/WarqnaPro/PrizeBoxService.php",
        "backend-laravel/database/migrations/2026_07_13_000200_create_v02_prize_boxes.php",
        "backend-laravel/tests/Feature/V02DailyPrizeBoxesTest.php",
        "backend-laravel/tests/Feature/V176DailyPackInventoryTest.php",
        "flutter_app/lib/v175_release.dart",
        "backend-laravel/config/warqna_xp_levels.php",
        "backend-laravel/app/Services/WarqnaPro/ChallengeService.php",
        "backend-laravel/tests/Feature/V174DirectInviteOrientationXpTest.php",
        "backend-laravel/tests/Feature/V175XpChallengesDesignerTest.php",
        "docs/reference/XPs_levels_1_to_100_source.xlsx",
        "docs/reference/XP_LEVELS_V175.csv",
        "docs/ar/validation/current/VALIDATION_RESULTS_V175.txt",
        "flutter_app/lib/v173_global.dart",
        "backend-laravel/resources/data/v173_store_catalog.json",
        "backend-laravel/app/Services/WarqnaPro/DailyPackService.php",
        "backend-laravel/app/Services/WarqnaPro/CompetitionService.php",
        "backend-laravel/app/Http/Controllers/MobileEngagementController.php",
        "backend-laravel/database/migrations/2026_07_12_000173_online_competitions_tickets_packs_designer.php",
        "backend-laravel/tests/Feature/V173OnlineEngagementTest.php",
        "backend-laravel/database/migrations/2026_07_13_000174_offline_progression_navigation.php",
        "backend-laravel/tests/Feature/V174OfflineProgressionNavigationTest.php",
        "tools/apply_brand_assets.py",
        "flutter_app/assets/images/brand/warqna_logo.png",
        "flutter_app/assets/images/tables/reference/catalog.json",
        "backend-laravel/tests/Feature/V172BrandTableCatalogTest.php",
        ".github/workflows/backend-ci.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
        "tools/verify_flutter_lock.py",
        "tools/configure_android_startup.py",
        "tools/verify_android_startup.py",
        "tools/configure_android_workmanager_guard.py",
        "docs/ar/troubleshooting/ANDROID_APK_STARTUP_FIX_V164_AR.md",
        "docs/ar/troubleshooting/ANDROID_WORKMANAGER_BOOT_FIX_V165_AR.md",
        "scripts/windows/archive/CAPTURE_ANDROID_CRASH_LOG_WINDOWS.bat",
        f"docs/ar/releases/current/START_HERE_V{EXPECTED_BUILD}_AR.md",
        f"docs/ar/deployment/GITHUB_UPLOAD_V{EXPECTED_BUILD}_AR.md",
        f"releases/manifests/current/RELEASE_MANIFEST_V{EXPECTED_BUILD}.json",
        f"docs/ar/reports/current/QUALITY_REPORT_V{EXPECTED_BUILD}_AR.md",
        f"scripts/windows/current/CHECK_V{EXPECTED_BUILD}_WINDOWS.bat",
        f"scripts/unix/current/check-v{EXPECTED_BUILD}.sh",
        f"scripts/windows/current/START_WARQNA_V{EXPECTED_BUILD}_WINDOWS.bat",
        "RELEASE_VERSION.json",
        "tools/release_metadata.py",
        "tools/verify_release_versions.py",
        "tools/test_clean_root_policy.py",
        "backend-laravel/app/Http/Controllers/Controller.php",
        "backend-laravel/tests/Unit/ControllerFoundationTest.php",
        "backend-laravel/tools/verify_http_foundation.php",
    ]
    missing = [item for item in required if not (ROOT / item).is_file()]
    if missing:
        fail("Missing release files: " + ", ".join(missing))
    require("CHECK_WARQNA_WINDOWS.bat", [f"CHECK_V{EXPECTED_BUILD}_WINDOWS.bat"])
    require("START_WARQNA_WINDOWS.bat", [f"START_WARQNA_V{EXPECTED_BUILD}_WINDOWS.bat"])
    forbid(f"scripts/windows/current/START_WARQNA_V{EXPECTED_BUILD}_WINDOWS.bat", ["call START_WARQNA_WINDOWS.bat"])
    print(f"[OK] Required v{EXPECTED_BUILD} release files")


ROOT_ALLOWED_ENTRIES = {
    ".github", ".gitignore", "CHECK_WARQNA_WINDOWS.bat", "README.md",
    "RELEASE_VERSION.json", "START_HERE_AR.md", "START_WARQNA_WINDOWS.bat",
    "assets", "backend-laravel", "docs", "flutter_app", "releases", "scripts", "tools",
}

# Repository/runtime metadata is present in CI checkouts but is not shipped project clutter.
# `.git` may be a directory (normal clone/GitHub Actions) or a file (Git worktree).
ROOT_IGNORED_METADATA = {
    ".git", ".gitattributes", ".gitmodules", ".editorconfig",
    ".DS_Store", "Thumbs.db",
}


ROOT_LEGACY_DOCUMENT_PATTERNS = (
    re.compile(r"^APPLY_PATCH(?:_[A-Z0-9.]+)?_?AR\.md$", re.IGNORECASE),
    re.compile(r"^CHANGELOG_[A-Z0-9._-]+_AR\.md$", re.IGNORECASE),
    re.compile(r"^FILES_MANIFEST(?:_[A-Z0-9._-]+)?\.txt$", re.IGNORECASE),
    re.compile(r"^VALIDATION(?:_RESULTS)?_[A-Z0-9._-]+\.txt$", re.IGNORECASE),
)

def _is_allowed_root_entry(name: str) -> bool:
    if name in ROOT_ALLOWED_ENTRIES or name in ROOT_IGNORED_METADATA:
        return True
    # Older patch-only packages placed their read-only handoff documents in the root.
    # They are safe release metadata, not runtime clutter, so full releases remain compatible.
    return any(pattern.fullmatch(name) for pattern in ROOT_LEGACY_DOCUMENT_PATTERNS)

def unexpected_root_entries(names) -> list[str]:
    return sorted(str(name) for name in names if not _is_allowed_root_entry(str(name)))


def check_clean_root_policy_self_test() -> None:
    accepted = set(ROOT_ALLOWED_ENTRIES) | {".git", ".gitattributes", ".gitmodules", ".editorconfig"}
    if unexpected_root_entries(accepted):
        fail("Clean-root policy rejected standard repository metadata")
    if unexpected_root_entries({"unexpected.tmp"}) != ["unexpected.tmp"]:
        fail("Clean-root policy stopped rejecting real unexpected root files")
    print("[OK] Clean-root policy self-test (Git metadata accepted, clutter rejected)")


def check_clean_root() -> None:
    unexpected = unexpected_root_entries(path.name for path in ROOT.iterdir())
    if unexpected:
        fail("Unexpected files in clean project root: " + ", ".join(unexpected))
    print("[OK] Clean organized project root (repository metadata ignored)")


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


def check_text_control_characters() -> None:
    offenders: list[str] = []
    for path in iter_text_files():
        data = path.read_bytes()
        bad = sorted({byte for byte in data if byte < 32 and byte not in (9, 10, 13)})
        if bad:
            offenders.append(f"{path.relative_to(ROOT)}:{bad}")
    if offenders:
        fail("Unexpected control characters in text files: " + ", ".join(offenders[:20]))
    print("[OK] No corrupt control characters in text launchers or source files")


def check_login_fix() -> None:
    if EXPECTED_BUILD >= 174:
        require("flutter_app/lib/main.dart", [
            "Future<String?> login(String loginId, String password",
            "Future<String?> _loginLocal",
            "Future<String?> _registerLocal",
            "await _storeOfflineCredentials",
            "offlineLoggedIn",
            "Future<String?> loginWithSocialProvider",
        ])
        require("flutter_app/lib/v173_global.dart", ["warqnaOnlineOnlyV173 = false"])
        forbid("flutter_app/lib/main.dart", [
            "Future<String?> login(String login, String password",
            "التسجيل المحلي غير متاح في Warqna V173",
            "controller.isAuthenticated && !controller.serverConnected",
        ])
        print("[OK] v174 offline-capable login, registration and social-provider contract")
        return

    if EXPECTED_BUILD >= 173:
        require("flutter_app/lib/main.dart", [
            "Future<String?> login(String loginId, String password",
            "OnlineRequiredScreenV173",
        ])
        forbid("flutter_app/lib/main.dart", [
            "Future<String?> login(String login, String password",
            "return this.login(loginId, password, offline: true);",
            "await this.login(loginId, password, offline: true);",
        ])
        print("[OK] v173 login contract")
        return

    require("flutter_app/lib/main.dart", [
        "Future<String?> login(String loginId, String password",
        "return login(loginId, password, offline: true);",
        "final fallback = await login(loginId, password, offline: true);",
    ])
    forbid("flutter_app/lib/main.dart", [
        "Future<String?> login(String login, String password",
        "return this.login(loginId, password, offline: true);",
        "await this.login(loginId, password, offline: true);",
    ])
    print("[OK] Merge-safe and analyzer-clean login fallback")


def check_versions() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/verify_release_versions.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Release version contract failed: " + result.stdout.strip())
    print(result.stdout.strip())


def check_root_policy_ci_contract() -> None:
    workflow = read(".github/workflows/production-release-check.yml")
    for needle in [
        "python3 tools/test_clean_root_policy.py",
        "python3 tools/validate_release.py",
    ]:
        if needle not in workflow:
            fail(f"Production release gate root-policy contract missing: {needle}")
    require("tools/test_clean_root_policy.py", [
        '".git"',
        '"rogue.txt"',
        "unexpected_root_entries",
    ])
    print("[OK] GitHub Actions clean-root regression contract")


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
        "name: warqna-v${{ steps.release.outputs.build }}-android",
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
        "MobilePushController::class",
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
        "assertCount(140,$service->tableSkins())",
        "assertCount(40,$service->cardBacks())",
    ])
    require("backend-laravel/tests/Feature/V132TarneebEngineAndLuxuryFixesTest.php", ["assertCount(140,$store->tableSkins())"])
    require("backend-laravel/tests/Feature/V134CriticalFixesTest.php", ["assertCount(140,$store->tableSkins())"])
    require("backend-laravel/tests/Feature/V172BrandTableCatalogTest.php", [
        "assertCount(140, $tables)",
        "assertCount(40, $reference)",
        "table_reference_40",
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
    print("[OK] Current 12-game, 140-table (50 legacy + 40 v172 + 50 v173), 40-card-back product contract")


def check_release_and_wallet_regressions() -> None:
    gate = read(".github/workflows/production-release-check.yml")
    if "WARQNA_APP_BUILD=158" in gate or "grep -q 'version:" in gate:
        fail("Stale hard-coded release gate returned")
    require(".github/workflows/production-release-check.yml", [
        "python3 tools/verify_release_versions.py",
    ])

    controller = "backend-laravel/app/Http/Controllers/MobileSocialController.php"
    require(controller, [
        "$freshWallet = $sender->wallet()->firstOrFail();",
        "'wallet' => $freshWallet",
    ])
    forbid(controller, ["wallet()->fresh()"])

    test = "backend-laravel/tests/Feature/V142MobileRealEnginesSocialEconomyTest.php"
    require(test, [
        "assertJsonPath('wallet.tokens', 900)",
        "$sender->wallet()->firstOrFail()->tokens",
        "$receiver->wallet()->firstOrFail()->tokens",
        "$admin->wallet()->firstOrFail()->tokens",
    ])
    forbid(test, ["wallet()->fresh()"])

    platform_test = "backend-laravel/tests/Feature/PlatformFoundationTest.php"
    require(platform_test, [
        "strtolower((string) $robots->headers->get('Content-Type'))",
        "text/plain; charset=utf-8",
    ])
    print("[OK] Dynamic release gate, Eloquent wallet relation and charset regression guards")


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


def check_v161_voice_social_progression() -> None:
    progression = read("backend-laravel/app/Services/Progression/ProgressionService.php")
    for needle in [
        "? 2.0 : 1.0",
        "'champion','winner','final_winner' => 1000",
        "'runner_up','finalist' => 600",
        "same_club_team",
        "event_key",
    ]:
        if needle not in progression:
            fail(f"v161 progression contract missing: {needle}")
    if "? 1.5 : 1.0" in progression:
        fail("Pasha multiplier regressed to x1.5")

    xp = read("backend-laravel/app/Services/Leveling/XpService.php")
    for needle in ["bool $applyMultipliers = true", "$applyMultipliers ?", "? 2.0 : 1.0"]:
        if needle not in xp:
            fail(f"XP multiplier guard missing: {needle}")
    if "false, false);" not in progression:
        fail("Progression must bypass the second XP multiplier application")

    countries_php = read("backend-laravel/config/countries.php")
    countries_dart = read("flutter_app/lib/data/countries.dart")
    if len(re.findall(r"""['"]flag['"]\s*=>""", countries_php)) < 240 or countries_dart.count("CountryInfo('") < 240:
        fail("All-country flag catalog is incomplete")
    for needle in ["country_code", "country_name", "flag_url", "round_points"]:
        if needle not in read("backend-laravel/app/Http/Controllers/MobileGameController.php"):
            fail(f"Room player profile field missing: {needle}")

    voice = read("flutter_app/lib/services/voice_room_service.dart")
    for needle in ["Uri.base.scheme != 'https'", "_pendingCandidates", "_reconnectPeer", "hasTurnServer", "Duration(milliseconds: 900)"]:
        if needle not in voice:
            fail(f"Voice recovery contract missing: {needle}")
    if "restartIce()" in voice or re.search(r"}\s*else\s*{\s*}\s*else\s*{", voice):
        fail("Voice service contains a compatibility or duplicate-else regression")

    main = read("flutter_app/lib/main.dart")
    for needle in ["runZonedGuarded", "addPostFrameCallback", "RewardedAds.show()", "vipDays = math.max(vipDays, 3650)", "recordRoundProgress", "showCountryPicker", "final completedRound = engine.round"]:
        if needle not in main:
            fail(f"Flutter startup/profile contract missing: {needle}")

    clubs = read("backend-laravel/app/Http/Controllers/ClubController.php")
    club_view = read("backend-laravel/resources/views/clubs/show.blade.php")
    for needle in ["create_announcements", "create_tournaments", "announcementStore", "announcementDelete", "required|in:accepted,rejected"]:
        if needle not in clubs + club_view:
            fail(f"Club moderator contract missing: {needle}")

    admin = read("backend-laravel/app/Http/Controllers/AdminController.php")
    store = read("backend-laravel/resources/views/store/index.blade.php")
    for needle in ["table_image", "card_back_image", "asset_url"]:
        if needle not in admin or needle not in store:
            fail(f"Admin-uploaded cosmetic contract missing: {needle}")

    social = read("backend-laravel/app/Http/Controllers/SocialAuthController.php")
    bootstrap = read("backend-laravel/bootstrap/app.php")
    for needle in ["accounts.google.com", "facebook.com", "appleid.apple.com", "issuer is invalid", "audience is invalid"]:
        if needle not in social:
            fail(f"Social OAuth contract missing: {needle}")
    if "validateCsrfTokens(except: ['auth/social/*/callback'])" not in bootstrap:
        fail("Apple form_post callback CSRF exception is missing")

    tournament_model = read("backend-laravel/app/Models/Tournament.php")
    club_model = read("backend-laravel/app/Models/Club.php")
    social_session = read("backend-laravel/app/Models/SocialAuthSession.php")
    room_controller = read("backend-laravel/app/Http/Controllers/RoomController.php")
    for required in ["prize_distribution", "leaderboard_points", "reward_multiplier", "sponsored"]:
        if required not in tournament_model:
            fail(f"Tournament persistence contract missing: {required}")
    for required in ["capacity", "league_tier", "total_points"]:
        if required not in club_model:
            fail(f"Club persistence contract missing: {required}")
    if "'one_time_token'=>'encrypted'" not in social_session:
        fail("Social one-time bearer token is not encrypted at rest")
    if "array_key_exists('winner_team',$state)" not in room_controller:
        fail("winner_team=0 final-round scoring guard is missing")

    migration = read("backend-laravel/database/migrations/2026_07_11_000161_voice_mobile_social_progression.php")
    for needle in ["progression_events", "club_announcements", "social_accounts", "social_auth_sessions", "3650"]:
        if needle not in migration:
            fail(f"v161 migration contract missing: {needle}")
    print("[OK] v161 voice, Android startup, countries, progression, clubs, designer and social OAuth contracts")


def check_v162_account_cancellation_and_analyzer() -> None:
    service = read("backend-laravel/app/Services/Account/AccountCancellationService.php")
    for needle in [
        "return max(30,",
        "$user->tokens()->delete();",
        "public function reactivate(User $user): bool",
        "where('status', 'pending')",
        "where('scheduled_for', '<=', now())",
    ]:
        if needle not in service:
            fail(f"v162 account cancellation lifecycle missing: {needle}")
    for forbidden in ["where('last_seen_at'", "subDays($days)"]:
        if forbidden in service:
            fail(f"Ordinary inactivity must never trigger account deletion: {forbidden}")

    command = read("backend-laravel/app/Console/Commands/PurgeCancelledAccounts.php")
    schedule = read("backend-laravel/routes/console.php")
    for needle in ["warqna:purge-cancelled-accounts", "purgeDue()"]:
        if needle not in command + schedule:
            fail(f"Cancelled-account purge contract missing: {needle}")
    if "warqna:purge-inactive-accounts --days=" in schedule:
        fail("Dangerous broad inactivity purge returned to the scheduler")

    mobile = read("backend-laravel/app/Http/Controllers/MobileApiController.php")
    account = read("backend-laravel/app/Http/Controllers/MobileAccountController.php")
    auth = read("backend-laravel/app/Http/Controllers/AuthController.php")
    social = read("backend-laravel/app/Http/Controllers/SocialAuthController.php")
    for needle in [
        "$cancellation->reactivate($user)",
        "'account_reactivated' => $reactivated",
        "تم إلغاء الحساب وتسجيل الخروج",
        "$cancellation->reactivate(auth()->user())",
        "$cancellation->reactivate($user);",
    ]:
        if needle not in mobile + account + auth + social:
            fail(f"Account reopening/reactivation contract missing: {needle}")

    routes = read("backend-laravel/routes/api.php")
    if "Route::delete('/account', [MobileApiController::class, 'deleteAccount'])" in routes:
        fail("Immediate destructive account-delete route returned")
    require("backend-laravel/routes/api.php", [
        "Route::post('/account/deletion-request'",
        "Route::delete('/account', [MobileAccountController::class, 'requestDeletion'])",
    ])

    main = read("flutter_app/lib/main.dart")
    production = read("flutter_app/lib/production_v153.dart")
    for needle in [
        "Future<String?> cancelAccount",
        "هل أنت متأكد أنك سوف تلغي الحساب؟",
        "لمدة 30 يوماً",
        "showCancelAccountDialog",
        "تمت استعادة الحساب",
    ]:
        if needle not in main + production:
            fail(f"Flutter account-cancellation UX missing: {needle}")
    forbid("flutter_app/lib/main.dart", ["showDeleteAccountDialog", "حذف الحساب نهائياً"])

    analyzer = read("tools/flutter_analyze_ci.sh")
    for needle in [
        "flutter analyze --no-fatal-infos --no-fatal-warnings",
        "error|warning",
        "informational lints only",
    ]:
        if needle not in analyzer:
            fail(f"Flutter analyzer wrapper incomplete: {needle}")
    for rel in [
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
    ]:
        require(rel, ["bash ../tools/flutter_analyze_ci.sh"])
    for dart in (ROOT / "flutter_app/lib").rglob("*.dart"):
        text = dart.read_text(encoding="utf-8")
        if ".withOpacity(" in text:
            fail(f"Deprecated Color.withOpacity returned in {dart.relative_to(ROOT)}")
    print("[OK] v162 30-day account cancellation/reactivation and analyzer-only-info handling")



def check_v163_ci_regressions() -> None:
    main = read("flutter_app/lib/main.dart")
    start = main.find("class _StorePageState")
    end = main.find("\nclass ", start + 20)
    if start < 0:
        fail("Store page state is missing")
    store = main[start:] if end < 0 else main[start:end]
    for needle in [
        "widget.controller.level",
        "widget.controller.xp",
        "widget.controller.levelProgress",
        "widget.controller.roundPoints",
        "widget.controller.tournamentPoints",
        "widget.controller.clubPoints",
    ]:
        if needle not in store:
            fail(f"v163 StorePage controller binding missing: {needle}")
    if re.search(r"(?<!widget\.)\bcontroller\.(?:level|xp|xpNext|levelProgress|pointsToNextLevel|roundPoints|tournamentPoints|clubPoints|vipDays|activeXpMultiplier)", store):
        fail("Unqualified StorePage controller reference returned")

    api = read("flutter_app/lib/services/api_client.dart")
    if "'confirmation': true" not in api:
        fail("Flutter cancellation request no longer sends explicit confirmation")

    for rel in [
        "backend-laravel/app/Http/Controllers/MobileAccountController.php",
        "backend-laravel/app/Http/Controllers/MobileApiController.php",
    ]:
        text = read(rel)
        if "'confirmation' => 'sometimes|accepted'" not in text:
            fail(f"Backward-compatible accepted confirmation rule missing: {rel}")
        admin_guard = text.find("abort_if($user->is_admin, 403")
        validation = text.find("$request->validate([", max(0, admin_guard - 200))
        if admin_guard < 0 or validation < 0 or admin_guard > validation:
            fail(f"Admin cancellation guard must run before validation: {rel}")

    print("[OK] v163 StorePage controller and account-cancellation validation regressions")


def check_v164_android_startup_safety() -> None:
    main = read("flutter_app/lib/main.dart")
    for needle in [
        "void main()",
        "runZonedGuarded(() {",
        "WidgetsFlutterBinding.ensureInitialized();",
        "runApp(const WarqnaApp());",
        "controller = AppController();",
        "unawaited(controller.load());",
        "Future<void> _loadUnsafe() async",
        "Safe boot ignored incompatible local state",
        ".map(int.tryParse)",
        ".whereType<int>()",
    ]:
        if needle not in main:
            fail(f"v164 safe Flutter boot contract missing: {needle}")
    if "RewardedAds.initialize()" in main:
        fail("AdMob initialization returned to the application startup path")
    binding = main.find("WidgetsFlutterBinding.ensureInitialized();")
    zone = main.find("runZonedGuarded(() {")
    app = main.find("runApp(const WarqnaApp());")
    if zone < 0 or binding < zone or app < binding:
        fail("Flutter binding and runApp are not in the same guarded startup zone")

    configure = read("tools/configure_android_startup.py")
    verify = read("tools/verify_android_startup.py")
    workflow = read(".github/workflows/flutter-android.yml")
    for needle in [
        "SAMPLE_APP_ID = \"ca-app-pub-3940256099942544~3347511713\"",
        "APP_ID_RE = re.compile",
        "mode == \"safe-apk\"",
        "production variable absent or invalid",
        "android.permission.ACCESS_NETWORK_STATE",
        "android.permission.RECORD_AUDIO",
    ]:
        if needle not in configure:
            fail(f"Android manifest sanitizer contract missing: {needle}")
    for needle in [
        "AdMob application ID is absent or malformed",
        "MainActivity is missing or not exported",
        "Android minSdk is not 24",
        "Android compileSdk is not 36",
    ]:
        if needle not in verify:
            fail(f"Android startup verifier contract missing: {needle}")
    for needle in [
        "--mode safe-apk",
        "warqna-v${WARQNA_APP_BUILD}-safe.apk",
        "--mode production-aab",
        "^ca-app-pub-[0-9]{16}/[0-9]{10}$",
        "apksigner\" verify --verbose",
        "zipalign\" -c -P 16",
    ]:
        if needle not in workflow:
            fail(f"Android safe-build workflow contract missing: {needle}")
    if 'app_id = os.environ.get(\'ADMOB_APP_ID\'' in workflow:
        fail("Unvalidated direct AdMob App ID injection returned")
    print("[OK] v164 first-frame boot, AdMob manifest sanitization and safe APK contracts")


def check_v165_android_workmanager_boot_guard() -> None:
    configure = read("tools/configure_android_workmanager_guard.py")
    verify = read("tools/verify_android_startup.py")
    workflow = read(".github/workflows/flutter-android.yml")
    for needle in [
        "androidx.work.WorkManagerInitializer",
        "tools:node=merge",
        "tools:node=remove",
        "WarqnaApplication.java",
        "androidx.work:work-runtime",
        "isMinifyEnabled = false",
        "isShrinkResources = false",
        "WorkManager startup guard installed",
    ]:
        if needle not in configure:
            fail(f"v165 WorkManager guard contract missing: {needle}")
    for needle in [
        "WarqnaApplication is not registered",
        "WorkManagerInitializer is not removed",
        "Direct WorkManager runtime dependency is missing",
        "Release minification is not explicitly disabled",
        "Release resource shrinking is not explicitly disabled",
    ]:
        if needle not in verify:
            fail(f"v165 WorkManager verifier contract missing: {needle}")
    if workflow.count("configure_android_workmanager_guard.py") < 2:
        fail("Android workflow must apply the WorkManager guard to both safe APK and production AAB")
    if "FATAL EXCEPTION" not in read("docs/ar/troubleshooting/ANDROID_WORKMANAGER_BOOT_FIX_V165_AR.md"):
        fail("v165 report must document the logcat crash signature")
    print("[OK] v165 Android WorkManager pre-Flutter boot guard")


def check_v166_global_polish() -> None:
    pubspec = read("flutter_app/pubspec.yaml")
    for needle in [
        "permission_handler: ^12.0.3",
        "audioplayers: ^6.8.1",
        "flutter_local_notifications: ^22.0.1",
        "firebase_core: ^4.11.0",
        "firebase_messaging: ^16.4.1",
        "assets/images/games/",
        "assets/sounds/",
    ]:
        if needle not in pubspec:
            fail(f"v166 Flutter dependency/asset contract missing: {needle}")

    game_ids = [
        "hand", "trix", "tarneeb", "basra", "baloot", "banakil",
        "trix_complex", "syrian_tarneeb", "tarneeb_400",
        "trix_partner", "hand_partner", "saudi_hand",
    ]
    for game_id in game_ids:
        path = ROOT / f"flutter_app/assets/images/games/{game_id}.png"
        if not path.is_file() or path.stat().st_size < 10_000:
            fail(f"v166 game artwork missing or too small: {path.relative_to(ROOT)}")
    sound_files = list((ROOT / "flutter_app/assets/sounds").glob("*.wav"))
    if len(sound_files) < 18:
        fail(f"v166 requires at least 18 game sound cues; found {len(sound_files)}")

    main = read("flutter_app/lib/main.dart")
    polish = read("flutter_app/lib/v166_polish.dart")
    voice = read("flutter_app/lib/services/voice_room_service.dart")
    notifications = read("flutter_app/lib/services/app_notifications.dart")
    api = read("flutter_app/lib/services/api_client.dart")
    for needle in [
        "part 'v166_polish.dart';",
        "PushNotifications.registerBackgroundHandler();",
        "ActiveGameBanner",
        "rememberActiveRoom",
        "onDoubleTap:",
        "onVerticalDragEnd:",
        "_playLocalCard",
        "_quickPlayCard",
        "_trickSeatWidgets()",
        "showRoundRewardReport",
        "showV166EmojiPicker",
        "SegmentedButton<int>",
        "changeFontFamily",
        "adjustFontScale",
        "customTableBackgroundData",
        "customCardBackData",
        "showPrivacyPolicy",
        "Preview",
    ]:
        if needle not in main and needle not in polish:
            fail(f"v166 UI/gameplay contract missing: {needle}")
    for needle in [
        "Permission.microphone.request()",
        "Helper.setSpeakerphoneOn",
        "_pendingCandidates",
        "_reconnectPeer",
        "hasTurnServer",
        "diagnostics",
    ]:
        if needle not in voice:
            fail(f"v166 Android voice contract missing: {needle}")
    for needle in [
        "FirebaseMessaging.onBackgroundMessage",
        "AndroidNotificationDetails",
        "requestNotificationsPermission",
        "if (kIsWeb)",
        "onTokenRefresh",
        "static String? get currentToken",
    ]:
        if needle not in notifications:
            fail(f"v166 notification contract missing: {needle}")
    for needle in ["registerPushDevice", "removePushDevice", "Future<Map<String, dynamic>> health()"]:
        if needle not in api:
            fail(f"v166 API client contract missing: {needle}")

    require("backend-laravel/routes/api.php", [
        "Route::post('/push/devices'",
        "Route::delete('/push/devices'",
    ])
    require("backend-laravel/app/Models/PushDevice.php", [
        "'token' => 'encrypted'",
        "protected $hidden = ['token']",
    ])
    require("backend-laravel/app/Services/Notifications/FirebasePushService.php", [
        "https://fcm.googleapis.com/v1/projects/",
        "openssl_sign",
        "sendToUser",
        "UNREGISTERED",
    ])
    require("backend-laravel/config/push.php", [
        "FIREBASE_SERVICE_ACCOUNT_B64",
        "PUSH_NOTIFICATIONS_ENABLED",
    ])
    require("backend-laravel/app/Http/Controllers/MobileSocialController.php", [
        "FirebasePushService",
        "'type' => 'private_message'",
        "friend-chat:",
    ])
    require("backend-laravel/app/Http/Controllers/MobileGameController.php", [
        "FirebasePushService",
        "'type' => 'room_message'",
        "'route' => 'room:'",
    ])
    require("backend-laravel/.env.production.example", [
        "FIREBASE_SERVICE_ACCOUNT_B64=",
        "FIREBASE_PROJECT_ID=",
    ])
    require("tools/configure_android_startup.py", [
        "android.permission.POST_NOTIFICATIONS",
        "android.permission.RECORD_AUDIO",
        "android.permission.MODIFY_AUDIO_SETTINGS",
        "android.permission.BLUETOOTH_CONNECT",
    ])
    require("tools/configure_android_workmanager_guard.py", [
        "desugar_jdk_libs:2.1.4",
        "isCoreLibraryDesugaringEnabled = true",
        "multiDexEnabled = true",
        "JavaVersion.VERSION_17",
    ])
    require("tools/verify_android_startup.py", [
        "Core library desugaring is not enabled",
        "Android multidex is not enabled",
    ])
    workflow = read(".github/workflows/flutter-android.yml")
    for needle in [
        "FIREBASE_API_KEY",
        "FIREBASE_APP_ID",
        "FIREBASE_MESSAGING_SENDER_ID",
        "FIREBASE_PROJECT_ID",
        "configure_android_workmanager_guard.py",
    ]:
        if needle not in workflow:
            fail(f"v166 Android workflow contract missing: {needle}")

    legacy_palestine_label = "الأراضي" + " الفلسطينية"
    for path in iter_text_files():
        if path.suffix.lower() in {".md", ".txt"} or path == ROOT / "tools/validate_release.py":
            continue
        text = path.read_text(encoding="utf-8", errors="ignore")
        if legacy_palestine_label in text:
            fail(f"Legacy Palestine label remains in {path.relative_to(ROOT)}")

    play_assets = {
        "icon-512.png": (512, 512),
        "feature-graphic-1024x500.png": (1024, 500),
        "screenshot-01-games.png": None,
        "screenshot-02-voice.png": None,
        "screenshot-03-social.png": None,
    }
    try:
        from PIL import Image
        for name, expected in play_assets.items():
            path = ROOT / "assets/play-store" / name
            if not path.is_file():
                fail(f"Play Store asset missing: {name}")
            with Image.open(path) as image:
                if expected is not None and image.size != expected:
                    fail(f"Play Store asset has wrong dimensions: {name}={image.size}, expected={expected}")
    except ImportError:
        for name in play_assets:
            if not (ROOT / "assets/play-store" / name).is_file():
                fail(f"Play Store asset missing: {name}")

    print("[OK] v166 voice, gameplay, social, accessibility, store, push and Play Store polish contracts")



def check_v169_flutter_ci_regressions() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_flutter_ci_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Flutter v169 API regression contract failed: " + result.stdout.strip())

    notifications = read("flutter_app/lib/services/app_notifications.dart")
    for needle in [
        "settings: const InitializationSettings(",
        "id: DateTime.now().millisecondsSinceEpoch",
        "notificationDetails: details",
    ]:
        if needle not in notifications:
            fail(f"flutter_local_notifications v22 named API contract missing: {needle}")

    main = read("flutter_app/lib/main.dart")
    for needle in [
        "L.t(localeCode, 'activeGame')",
        "L.t(localeCode, 'friendsChat')",
        "void addNotice(AppNotice notice)",
        "widget.controller.addNotice(AppNotice(",
    ]:
        if needle not in main:
            fail(f"v169 Flutter analyzer regression guard missing: {needle}")
    for forbidden in [
        "v166Text(",
        "widget.controller.notifyListeners()",
        "import 'dart:typed_data';",
        "points[number]",
    ]:
        if forbidden in main:
            fail(f"v169 Flutter analyzer regression returned: {forbidden}")

    for rel in [
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
    ]:
        require(rel, ["python3 ../tools/test_flutter_ci_contract.py", "bash ../tools/flutter_analyze_ci.sh"])

    print(result.stdout.strip())
    print("[OK] v169 Flutter localization, notification API and ChangeNotifier CI regressions")


def check_v170_responsive_gameplay_security() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v170_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v170 regression contract failed: " + result.stdout.strip())

    require("backend-laravel/tests/Feature/V170ResponsiveGameplaySecurityContractTest.php", [
        "test_progressive_xp_curve_matches_the_v170_contract",
        "test_public_profile_contains_progress_and_country_but_never_private_tokens",
        "test_v170_authoritative_game_and_security_contracts_are_present",
    ])
    require("flutter_app/lib/services/connection_diagnostics.dart", [
        "رابط خادم الهاتف",
        "loopbackApi",
    ])
    require(".github/workflows/flutter-android.yml", [
        "--obfuscate",
        "--split-debug-info=",
        "flutter_app/build/symbols/**",
    ])
    print(result.stdout.strip())
    print("[OK] v170 responsive UI, public profiles, XP curve, mobile voice diagnostics, room controls and security contracts")

def check_v171_controller_reference_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v171_controller_references.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v171 controller-reference contract failed: " + result.stdout.strip())

    workflow = read(".github/workflows/flutter-web-pages.yml")
    if "Incorrect controller references in lib/main.dart" in workflow:
        fail("Brittle literal controller-reference check returned in Flutter Web workflow")
    for rel in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(rel, ["test_v171_controller_references.py"])

    print(result.stdout.strip())
    print("[OK] v171 semantic AppController reference contract")


def check_v172_brand_table_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v172_brand_table_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v172 brand/table contract failed: " + result.stdout.strip())

    for rel in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(rel, ["test_v172_brand_table_contract.py"])
    require(".github/workflows/flutter-android.yml", ["apply_brand_assets.py"])
    require("flutter_app/pubspec.yaml", ["assets/images/brand/", "assets/images/tables/"])
    print(result.stdout.strip())
    print("[OK] v172 additive Warqna brand, 40-table HD collection, and legacy CI compatibility")



def check_v173_online_engagement_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v173_online_engagement_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v173 online engagement contract failed: " + result.stdout.strip())

    for rel in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(rel, ["test_v173_online_engagement_contract.py"])
    require("flutter_app/pubspec.yaml", ["assets/images/pasha/v173/", "assets/images/tables/v173/royal/", "assets/images/tables/v173/showcase/"])
    print(result.stdout.strip())
    print("[OK] inherited v173 engagement assets, server-authoritative economy, Pasha colors, tables, tickets, packs and universal designer")

def check_v174_offline_progression_navigation_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v174_offline_progression_navigation_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v174 offline/progression/navigation contract failed: " + result.stdout.strip())
    print(result.stdout.strip())
    print("[OK] v174 offline access, fixed orientation, direct-room navigation, visible XP and requested level curve")



def check_v175_xp_challenges_pasha_designer_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v175_xp_challenges_pasha_designer_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v175 XP/challenges/Pasha/designer contract failed: " + result.stdout.strip())
    for rel in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(rel, ["test_v175_xp_challenges_pasha_designer_contract.py"])
    require("backend-laravel/tests/Feature/V174DirectInviteOrientationXpTest.php", [
        "queueNavigationRoute",
        "openPendingNavigationRoute",
        "_prepareDirectInviteTransfer",
    ])
    require("backend-laravel/tests/Feature/V175XpChallengesDesignerTest.php", [
        "test_all_excel_xp_values_are_exact",
        "test_challenge_can_activate_progress_and_claim_once",
        "test_v175_ui_contract_hides_pasha_colors_and_keeps_full_pasha",
    ])
    print(result.stdout.strip())
    print("[OK] v175 exact Excel XP, web fallback login, full Pasha, premium challenges/packs and universal designer")

def check_v176_daily_pack_inventory_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v176_daily_pack_inventory_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna v176 daily-pack/inventory contract failed: " + result.stdout.strip())
    for rel in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(rel, ["test_v176_daily_pack_inventory_contract.py"])
    print(result.stdout.strip())
    print("[OK] v176 analyzer fixes, animated pack reveal, server inventory and timed expiry")


def check_v02_daily_prize_boxes_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v02_daily_prize_boxes_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna V0.2 daily prize boxes contract failed: " + result.stdout.strip())
    print(result.stdout.strip())
    print("[OK] V0.2 dedicated prize-box page, 4-win limit, front-opening animation, ticket art and translated rewards")


def check_v022_economy_rooms_clubs_engines_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v022_economy_rooms_clubs_engines_contract.py")],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna V0.2.2 economy/rooms/clubs/engines contract failed: " + result.stdout.strip())
    print(result.stdout.strip())
    print("[OK] V0.2.2 server economy, public rooms, club permissions, delegated admin and rummy engine contracts")


def check_v03_platform_contract() -> None:
    result = subprocess.run(
        [sys.executable, str(ROOT / "tools/test_v03_platform_contract.py")],
        cwd=ROOT, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("Warqna V0.3 platform contract failed: " + result.stdout.strip())
    print(result.stdout.strip())
    print("[OK] V0.3 balanced premium deal, 3-turn absence, challenge road, level rewards, localized bots, owner designer and test ads")


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
    check_clean_root_policy_self_test()
    check_clean_root()
    check_conflicts()
    check_text_control_characters()
    check_login_fix()
    check_versions()
    check_root_policy_ci_contract()
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
    check_release_and_wallet_regressions()
    check_android_ci_order()
    check_v161_voice_social_progression()
    check_v162_account_cancellation_and_analyzer()
    check_v163_ci_regressions()
    check_v164_android_startup_safety()
    check_v165_android_workmanager_boot_guard()
    check_v166_global_polish()
    check_v169_flutter_ci_regressions()
    check_v170_responsive_gameplay_security()
    check_v171_controller_reference_contract()
    check_v172_brand_table_contract()
    check_v173_online_engagement_contract()
    check_v174_offline_progression_navigation_contract()
    check_v175_xp_challenges_pasha_designer_contract()
    check_v176_daily_pack_inventory_contract()
    check_v02_daily_prize_boxes_contract()
    check_v022_economy_rooms_clubs_engines_contract()
    check_v03_platform_contract()
    check_secrets()
    check_dart_structure()
    print(f"[PASS] Warqna v{EXPECTED_BUILD} source-package preflight completed successfully")


if __name__ == "__main__":
    main()
