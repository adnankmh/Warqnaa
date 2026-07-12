#!/usr/bin/env python3
"""Warqna v174 offline access, XP progression, orientation and direct-room contract."""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def require(rel: str, needles: list[str]) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"Missing file: {rel}")
    text = path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text:
            fail(f"Missing v174 contract in {rel}: {needle}")
    return text


def forbid(rel: str, needles: list[str]) -> None:
    text = (ROOT / rel).read_text(encoding="utf-8")
    for needle in needles:
        if needle in text:
            fail(f"Forbidden v174 regression in {rel}: {needle}")


def main() -> None:
    meta = json.loads((ROOT / "RELEASE_VERSION.json").read_text(encoding="utf-8"))
    if meta.get("version") != "1.74.0" or meta.get("build") != 174:
        fail("Release metadata is not 1.74.0+174")

    main_dart = require("flutter_app/lib/main.dart", [
        "final GlobalKey<NavigatorState> warqnaNavigatorKey",
        "Future<String?> _loginLocal",
        "Future<String?> _registerLocal",
        "Future<String?> registerOffline",
        "إنشاء حساب محلي أوفلاين",
        "دخول أوفلاين بالحساب المحفوظ",
        "await _storeOfflineCredentials",
        "offlineLoggedIn",
        "Future<String?> loginWithSocialProvider",
        "Future<void> openRoomFromNotificationV174",
        "WidgetsBinding.instance.addPostFrameCallback",
        "Future<void> _applyPreferredOrientationV174",
        "SystemChrome.setPreferredOrientations",
        "class RoundXpBannerV174",
        "applyServerRoundProgressV174",
        "progression_popup",
        "player['bot'] == true",
        "multiplier = 1.20",
        "multiplier = 1.30",
        "multiplier = 1.50",
        "multiplier = 1.80",
        "multiplier = 2.20",
        "multiplier = 6.00",
        "await openGameRoom(navigationContext, controller, game, options: options);",
    ])
    if "controller.isAuthenticated && !controller.serverConnected" in main_dart:
        fail("The application home is still blocked by an online-only gate")
    forbid("flutter_app/lib/main.dart", [
        "التسجيل المحلي غير متاح في Warqna V173",
        "await controller.setLandscapeMode(true);",
        "final multiplier = switch (safe)",
    ])

    require("flutter_app/lib/v173_global.dart", [
        "warqnaOnlineOnlyV173 = false",
        "يمكنك متابعة اللعب محلياً دون إنترنت",
        "if (!serverConnected) return 'يلزم اتصال فعّال بالإنترنت لفتح الحزمة.';",
        "if (!serverConnected) return 'المنافسات تعمل عبر الإنترنت فقط.';",
        "refreshUi()",
    ])
    forbid("flutter_app/lib/v173_global.dart", ["notifyListeners();"])

    require("backend-laravel/app/Services/Leveling/XpService.php", [
        "$safe >= 40 && $safe <= 50 => 1.20",
        "$safe >= 51 && $safe <= 59 => 1.30",
        "$safe >= 60 && $safe <= 69 => 1.50",
        "$safe >= 70 && $safe <= 79 => 1.80",
        "$safe >= 80 && $safe <= 89 => 2.20",
        "$safe >= 90 && $safe <= 100 => 6.00",
    ])
    require("backend-laravel/app/Services/Progression/ProgressionService.php", [
        "'user_id'=>(int)$user->id",
        "'profile'=>$this->profileSnapshot($user)",
        "'xp_progress'=>max(0, $totalXp - $before)",
        "'xp_next'=>$this->xpService->requiredXp($level)",
    ])
    require("backend-laravel/app/Http/Controllers/MobileGameController.php", [
        "progression_popup",
        "'player_key'=>$key",
        "'player_name'=>$player->user->profile?->display_name",
        "'xp_next' => app(\\App\\Services\\Leveling\\XpService::class)->requiredXp",
    ])
    require("backend-laravel/app/Models/DailyPackClaim.php", [
        "use Illuminate\\Database\\Eloquent\\Casts\\Attribute;",
        "protected function claimDate(): Attribute",
        "Carbon::parse($value)->toDateString()",
    ])
    require("backend-laravel/tests/Feature/V173OnlineEngagementTest.php", ["Game::firstOrCreate"])
    for rel in [
        "backend-laravel/tests/Feature/V128StoreGameplayNavTest.php",
        "backend-laravel/tests/Feature/V132TarneebEngineAndLuxuryFixesTest.php",
        "backend-laravel/tests/Feature/V134CriticalFixesTest.php",
        "backend-laravel/tests/Feature/V172BrandTableCatalogTest.php",
    ]:
        require(rel, ["assertCount(140"])

    require("backend-laravel/app/Http/Controllers/MobileApiController.php", ["'online_only' => false", "'offline_login' => true", "'offline_gameplay' => true"])
    require("backend-laravel/app/Http/Controllers/MobileEngagementController.php", ["'online_only'=>false"])
    require("backend-laravel/database/migrations/2026_07_13_000174_offline_progression_navigation.php", [
        "'enabled'=>false",
        "'offline_login'=>true",
        "'offline_gameplay'=>true",
        "'revision'=>174",
    ])
    require("backend-laravel/tests/Feature/V174OfflineProgressionNavigationTest.php", [
        "test_v174_progressive_xp_curve_matches_all_requested_ranges",
        "test_v174_catalog_remains_additive_and_unique",
        "test_daily_pack_claim_date_is_stored_as_plain_date",
    ])

    for workflow in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require(workflow, ["test_v174_offline_progression_navigation_contract.py"])

    print("[PASS] v174 contract: offline access, fixed orientation, direct room navigation, visible human-player XP, and requested level curve")


if __name__ == "__main__":
    main()
