#!/usr/bin/env python3
"""Static release contract for Warqna V0.5 global upgrade."""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def text(rel: str) -> str:
    path = ROOT / rel
    assert path.is_file(), f"Missing required file: {rel}"
    return path.read_text(encoding="utf-8")


def contains(rel: str, *needles: str) -> None:
    body = text(rel)
    for needle in needles:
        assert needle in body, f"Missing V0.5 contract in {rel}: {needle}"


meta = json.loads(text("RELEASE_VERSION.json"))
assert meta["version"] == "0.5.0"
assert meta["build"] == 500
assert meta["full"] == "0.5.0+500"

contains("flutter_app/lib/main.dart", "part 'v05_release.dart';", "favoriteGameIdsV05", "V05GlobalControlsOverlay", "V05ClubsPage")
contains(
    "flutter_app/lib/v05_release.dart",
    "warqnaVersionV05 = '0.5.0'",
    "class PashaFezV05",
    "class V05GlobalControlsOverlay",
    "class FavoriteGamesSectionV05",
    "class ChallengeRoadCardV05",
    "class V05ClubsPage",
    "class V05ThreeDButton",
    "botNameV05",
    "Original red Pasha fez",
)
contains("flutter_app/lib/services/api_client.dart", "/v05/challenge-road/start", "/v05/clubs")
contains("flutter_app/lib/v173_global.dart", "assets/images/pasha.png")
contains("flutter_app/lib/premium_v151.dart", "green_light", "pink_light")
contains("flutter_app/web/index.html", "application/ld+json", "og:title", "canonical")
contains("flutter_app/web/manifest.json", '"orientation": "landscape-primary"')

contains(
    "backend-laravel/app/Http/Controllers/MobileApiController.php",
    "public function purchase",
    "insufficient_tokens",
    "store_revenue",
)
contains("backend-laravel/app/Models/User.php", "hasAdminPermission", "hasAnyAdminPermission", "strcasecmp($this->username, 'Adnan')", "'club'=>")
contains("backend-laravel/app/Http/Controllers/MobileAdminController.php", "set_admin_permissions", "send_friend_request", "$this->guard($request, 'store')", "$this->guard($request, 'designer')")
contains("backend-laravel/app/Http/Controllers/MobileV05Controller.php", "CLUB_PERMISSIONS", "startChallenge", "updateMember", "activityLogs")
contains("backend-laravel/app/Services/WarqnaPro/ChallengeRoadService.php", "MAX_LIVES = 5", "ALLOWED_STAGES = [10, 12, 15]", "min(1000")
contains("backend-laravel/app/Services/WarqnaPro/LevelRewardService.php", "grantRange", "rewardFor")
contains("backend-laravel/app/Services/GameEngine/DeckFactory.php", "random_int", "strength")
contains("backend-laravel/app/Services/GameEngine/HandRules.php", "firstMeldMinimum", "arrange_melds", "layoff")
contains("backend-laravel/routes/api.php", "/v05/challenge-road", "/v05/clubs")
contains("backend-laravel/database/migrations/2026_07_14_000500_warqna_v05_global_upgrade.php", "challenge_runs", "level_reward_claims", "club_activity_logs")
contains("docs/ar/releases/current/CHANGELOG_V0.5_AR.md", "ورقنا V0.5", "0.5.0+500")
contains("docs/ar/setup/DEMO_ACCOUNTS_V05_AR.md", "Warqna QA Club", "Warqna10!")
contains("backend-laravel/database/seeders/DatabaseSeeder.php", "V0.5 QA player group", "WARQNA_SEED_DEMO_USERS")

print("[PASS] Warqna V0.5 global store, gameplay, rooms, clubs, challenges, rewards, themes, admin and SEO contracts")
