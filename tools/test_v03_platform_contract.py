#!/usr/bin/env python3
from __future__ import annotations

import json
import subprocess
import sys
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


def main() -> None:
    meta = json.loads(require("RELEASE_VERSION.json"))
    if meta.get("version") != "0.3.0" or int(meta.get("build", 0)) != 179:
        fail("metadata is not 0.3.0+179")

    require(
        "flutter_app/lib/main.dart",
        "part 'v03_release.dart';",
        "GlobalSettingsDockV03",
        "reportChallengeRoadResultV03",
        "selectedPashaStyle = 'red';",
        "AdminStoreStudioV151",
        "const Tab(text:'المتجر')",
    )
    require(
        "flutter_app/lib/v03_release.dart",
        "class ChallengeRoadPageV03",
        "class Warqna3DButtonV03",
        "fiveLives",
        "refreshUi();",
        "Portrait by default",
    )
    require(
        "flutter_app/lib/engines/local_game_engine.dart",
        "_dealBalancedRummyV03",
        "_botNamesAr",
        "_botNamesEn",
        "assets/images/bots/",
    )
    avatars = sorted((ROOT / "flutter_app/assets/images/bots").glob("*.png"))
    if len(avatars) != 17:
        fail(f"expected 17 bot avatars, found {len(avatars)}")

    require(
        "backend-laravel/app/Models/User.php",
        "HasFactory",
        "use HasApiTokens, HasFactory, Notifiable",
    )
    require("backend-laravel/database/factories/UserFactory.php", "class UserFactory")
    require(
        "backend-laravel/database/migrations/2026_07_13_140000_v03_challenge_campaigns_level_rewards_and_room_presence.php",
        "visibility",
        "voluntary_leave_count",
        "challenge_campaigns",
        "level_reward_claims",
    )
    require(
        "backend-laravel/app/Services/GameEngine/GlobalEngines/BalancedDealV03.php",
        "class BalancedDealV03",
        "public static function trick",
        "public static function rummy",
    )
    require(
        "backend-laravel/app/Services/WarqnaPro/ChallengeCampaignService.php",
        "STAGE_OPTIONS = [10, 12, 15]",
        "STARTING_LIVES = 5",
        "randomOpponentId",
        "min(1000",
    )
    require(
        "backend-laravel/app/Services/Leveling/LevelUpRewardService.php",
        "pasha_days",
        "prize_box",
        "ticket",
        "writing_color",
        "min(1000",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileGameController.php",
        "voluntary_leave_count",
        "rejoin_blocked",
        "min(3",
        "max(0",
        "where('visibility', '!=', 'private')",
        "'avatars'",
    )
    admin = require(
        "backend-laravel/app/Http/Controllers/MobileAdminController.php",
        "admin.tokens.send",
        "admin.friend.request",
        "ownerGuard($request)",
        "username) === 'adnan'",
    )
    if "$this->guard($request, 'designer.manage');" in admin:
        fail("no-code designer is not owner-only")

    require(
        "flutter_app/lib/services/rewarded_ads_mobile.dart",
        "ca-app-pub-3940256099942544/5224354917",
        "ca-app-pub-3940256099942544/1712485313",
    )
    require("backend-laravel/database/seeders/V03DemoPlayersSeeder.php", "Warqna Global Club", "['Bayan', 91")

    result = subprocess.run(
        ["php", "backend-laravel/tools/test-engine-adapters.php"],
        cwd=ROOT,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        fail("standalone engine adapters failed: " + result.stdout.strip())

    print("[PASS] V0.3 balanced dealing, absence/rejoin, challenge road, level rewards, bot avatars, owner designer and test ads contract")


if __name__ == "__main__":
    main()
