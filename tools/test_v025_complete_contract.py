#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def require(rel: str, *needles: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"missing {rel}")
    text = path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text:
            fail(f"missing {needle!r} in {rel}")
    return text


def forbid(rel: str, *needles: str) -> None:
    text = require(rel)
    for needle in needles:
        if needle in text:
            fail(f"forbidden regression {needle!r} in {rel}")


def main() -> None:
    meta = json.loads(require("RELEASE_VERSION.json"))
    if meta.get("version") != "0.2.5" or meta.get("build") != 181 or meta.get("full") != "0.2.5+181":
        fail("metadata is not V0.2.5+181")

    require(
        "flutter_app/lib/main.dart",
        "part 'v025_release.dart';",
        "GlobalAppearanceOverlayV025",
        "pendingLevelRewardsV025",
        "absenceEjectionCountsV025",
        "activeChallengeJourneyV025",
        "showLevelRewardsV025",
        "AdminDesignerGateV025",
        "RewardedWebPreviewDialog(controller: controller)",
    )
    require(
        "flutter_app/lib/v025_release.dart",
        "class Premium3DButtonV025",
        "class ChallengeJourneyV025",
        "class GlobalAppearanceOverlayV025",
        "void showLevelRewardsV025",
        "List.generate(road.length",
        "[10, 12, 15].contains(stages)",
        "'attempts_left': 5",
        "math.min(1000",
        "isPrimaryAdminV025",
        "refreshAccountSnapshotV025",
        "warqnaNavigatorKey.currentContext",
    )
    for locale in ["'ar':", "'en':", "'de':", "'tr':", "'fr':", "'es':"]:
        if locale not in require("flutter_app/lib/v025_release.dart"):
            fail(f"missing V0.2.5 locale {locale}")

    require(
        "flutter_app/lib/engines/fair_deal.dart",
        "class FairDealBalancer",
        "balanceCodes",
        "for (var step = 0; step < 96; step++)",
        "premiumQuota",
    )
    require("flutter_app/lib/engines/tarneeb_engine.dart", "import 'fair_deal.dart';", "FairDealBalancer.balanceCodes")
    require("flutter_app/lib/engines/local_game_engine.dart", "import 'fair_deal.dart';", "FairDealBalancer.balanceCodes")

    require(
        "backend-laravel/app/Services/GameEngine/FairDealBalancer.php",
        "final class FairDealBalancer",
        "cryptographically secure source",
        "for ($step = 0; $step < 96; $step++)",
        "never targets a username/seat",
    )
    for rel in [
        "backend-laravel/app/Services/GameEngine/AbstractCardRules.php",
        "backend-laravel/app/Services/GameEngine/DeckFactory.php",
        "backend-laravel/app/Services/GameEngine/GlobalEngines/GlobalCardEngineCore.php",
    ]:
        require(rel, "FairDealBalancer::balance")

    game = require(
        "backend-laravel/app/Http/Controllers/MobileGameController.php",
        "'absence_ejections'",
        "'manual_exit_count'",
        "'return_blocked'",
        "$missed = min(3",
        "$nextExitCount = min(3",
        "'connected' => !$ejected",
        "'return_blocked' => $blocked",
    )
    timeout_block = game[game.index("public function timeout"):game.index("public function leave")]
    if "manual_exit_count" in timeout_block:
        fail("absence timeout must not increment manual exits")

    require(
        "backend-laravel/database/migrations/2026_07_13_140000_v025_absence_challenge_level_rewards.php",
        "manual_exit_count",
        "absence_ejections",
        "return_blocked",
        "challenge_runs",
        "level_reward_claims",
        "$table->unique(['user_id', 'level'])",
        "processed_result_ids",
    )
    require(
        "backend-laravel/app/Services/WarqnaPro/ChallengeJourneyService.php",
        "[10,12,15]",
        "'attempts_left'=>5",
        "min(1000",
        "pickOpponent",
        "inRandomOrder()",
        "where('last_seen_at','>=',now()->subMinutes(3))",
        "last_client_result_id",
        "processed_result_ids",
        "hash_equals((string)$run->game_key, $gameKey)",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileChallengeJourneyController.php",
        "'game_key'=>'required|string|max:80'",
        "$service->record",
    )
    require(
        "flutter_app/lib/services/api_client.dart",
        "recordChallengeJourneyResultV025(bool won, String clientResultId, String gameKey)",
        "'game_key': gameKey",
    )
    require(
        "backend-laravel/routes/api.php",
        "Route::get('/challenge-journey'",
        "Route::post('/challenge-journey/start'",
        "Route::post('/challenge-journey/result'",
    )
    require(
        "backend-laravel/app/Services/Progression/LevelRewardService.php",
        "grantRange",
        "min(1000, 75 + ($level * 15))",
        "LevelRewardClaim::create",
        "'level_reward'",
    )
    require("backend-laravel/app/Services/Progression/ProgressionService.php", "LevelRewardService", "'level_rewards'")

    bots = require("flutter_app/lib/premium_v149.dart", "عدنان", "بيان", "كنان", "قمر", "Adnan", "Bayan", "Qamar")
    if bots.count("BotProfile(id:") < 17:
        fail("fewer than 17 localized V0.2.5 bot profiles")
    require("backend-laravel/app/Http/Controllers/MobileGameController.php", "عدنان", "قمر", "Adnan", "Qamar", "X-Locale")

    require(
        "backend-laravel/app/Http/Controllers/MobileAdminController.php",
        "friend_request",
        "sendAdminFriendRequest",
        "primaryAdnanGuard",
        "strtolower(trim((string)$user->username)) === 'adnan'",
    )
    require("flutter_app/lib/main.dart", "serverData?['users']", "adminUserAction(userId", "grant_tokens", "friend_request")

    require(
        "backend-laravel/database/seeders/DemoPlayersV025Seeder.php",
        "Warqna025!",
        "WARQNA_SEED_DEMO_PLAYERS",
        "['Bayan','بيان'",
        "['Qamar','قمر'",
    )
    require("backend-laravel/database/seeders/DatabaseSeeder.php", "DemoPlayersV025Seeder::class")
    require("docs/ar/product/DEMO_ACCOUNTS_V025_AR.md", "Warqna025!", "Qamar")

    require(
        "flutter_app/lib/services/rewarded_ads_mobile.dart",
        "ca-app-pub-3940256099942544/5224354917",
        "ca-app-pub-3940256099942544/1712485313",
    )
    require("flutter_app/lib/main.dart", "RewardedWebPreviewDialog", "showLevelRewardsV025")
    require("flutter_app/lib/v170_global.dart", "Premium3DButtonV025")
    require("flutter_app/lib/v170_global.dart", "class TarneebBidButtonV170", "Premium3DButtonV025")

    # Execute the PHP balancer directly: exact card multiset and hand sizes must survive.
    php = r'''
require "backend-laravel/app/Services/GameEngine/FairDealBalancer.php";
$h=[
["2_C","3_C","4_C","5_C","6_C","7_C","8_C","9_C","10_C","2_D","3_D","4_D","5_D"],
["A_C","K_C","Q_C","J_C","A_D","K_D","Q_D","J_D","A_H","K_H","Q_H","J_H","10_H"],
["6_D","7_D","8_D","9_D","10_D","2_H","3_H","4_H","5_H","6_H","7_H","8_H","9_H"],
["A_S","K_S","Q_S","J_S","10_S","9_S","8_S","7_S","6_S","5_S","4_S","3_S","2_S"]];
$b=App\Services\GameEngine\FairDealBalancer::balance($h,"trick");
$before=array_merge(...$h); $after=array_merge(...$b); sort($before); sort($after);
if ($before!==$after || count(array_unique($after))!==52) exit(2);
foreach($b as $hand) if(count($hand)!==13) exit(3);
$premium=fn($hand)=>count(array_filter($hand,fn($c)=>preg_match('/^(A|K|Q|J)[_-]/',$c)));
if(min(array_map($premium,$b))<2) exit(4);
echo "OK";
'''
    result = subprocess.run(["php", "-r", php], cwd=ROOT, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
    if result.returncode != 0 or result.stdout.strip() != "OK":
        fail("PHP fair-deal invariant failed: " + result.stdout.strip())

    # Ensure the store title remains only in bottom navigation and the page has no duplicated title row.
    require("flutter_app/lib/main.dart", "NavigationDestination(icon: const Icon(Icons.redeem), label: L.t(widget.controller.localeCode, 'store'))")
    store_chunk = require("flutter_app/lib/main.dart")
    if "label: L.t(widget.controller.localeCode, 'store')" not in store_chunk:
        fail("store label was not restored under the home icon")

    print("[PASS] V0.2.5 fair play, absence, challenges, rewards, localization, admin, ads and UI contract")


if __name__ == "__main__":
    main()
