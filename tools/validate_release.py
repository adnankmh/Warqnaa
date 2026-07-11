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
        "backend-laravel/app/Http/Controllers/SocialAuthController.php",
        "VOICE_AND_SOCIAL_SETUP_V161_AR.md",
        "backend-laravel/app/Services/Account/AccountCancellationService.php",
        "backend-laravel/app/Console/Commands/PurgeCancelledAccounts.php",
        "backend-laravel/tests/Feature/V162AccountCancellationLifecycleTest.php",
        "tools/flutter_analyze_ci.sh",
        ".github/workflows/backend-ci.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
        "tools/verify_flutter_lock.py",
        f"START_HERE_V{EXPECTED_BUILD}_AR.md",
        f"GITHUB_UPLOAD_V{EXPECTED_BUILD}_AR.md",
        f"RELEASE_MANIFEST_V{EXPECTED_BUILD}.json",
        f"QUALITY_REPORT_V{EXPECTED_BUILD}_AR.md",
        f"CHECK_V{EXPECTED_BUILD}_WINDOWS.bat",
        f"check-v{EXPECTED_BUILD}.sh",
        f"START_WARQNA_V{EXPECTED_BUILD}_WINDOWS.bat",
        "RELEASE_VERSION.json",
        "tools/release_metadata.py",
        "tools/verify_release_versions.py",
        "backend-laravel/app/Http/Controllers/Controller.php",
        "backend-laravel/tests/Unit/ControllerFoundationTest.php",
        "backend-laravel/tools/verify_http_foundation.php",
    ]
    missing = [item for item in required if not (ROOT / item).is_file()]
    if missing:
        fail("Missing release files: " + ", ".join(missing))
    print(f"[OK] Required v{EXPECTED_BUILD} release files")


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
    for needle in ["runZonedGuarded", "addPostFrameCallback", "RewardedAds.initialize()", "vipDays = math.max(vipDays, 3650)", "recordRoundProgress", "showCountryPicker", "final completedRound = engine.round"]:
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
    check_text_control_characters()
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
    check_release_and_wallet_regressions()
    check_android_ci_order()
    check_v161_voice_social_progression()
    check_v162_account_cancellation_and_analyzer()
    check_secrets()
    check_dart_structure()
    print(f"[PASS] Warqna v{EXPECTED_BUILD} source-package preflight completed successfully")


if __name__ == "__main__":
    main()
