#!/usr/bin/env python3
"""Static regression contract for the v170 responsive/gameplay/security release."""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(rel: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        raise AssertionError(f"Missing required file: {rel}")
    return path.read_text(encoding="utf-8", errors="strict")


def require(rel: str, needles: list[str]) -> None:
    text = read(rel)
    missing = [needle for needle in needles if needle not in text]
    if missing:
        raise AssertionError(f"{rel} is missing: {missing}")


def forbid(rel: str, needles: list[str]) -> None:
    text = read(rel)
    present = [needle for needle in needles if needle in text]
    if present:
        raise AssertionError(f"{rel} contains forbidden regressions: {present}")


def main() -> int:
    meta = json.loads(read("RELEASE_VERSION.json"))
    assert int(meta["build"]) >= 170

    require("flutter_app/lib/main.dart", [
        "part 'v170_global.dart';",
        "ProductCardV170(controller: controller, product: product)",
        "showPublicPlayerProfileV170",
        "showChallengesV170",
        "GroupInnovationHubV170",
        "TarneebBidButtonV170",
        "serverAutoNextRoundTimer",
        "Duration(milliseconds: 1700)",
        "showRoundRewardReport",
        "onDoubleTap:",
        "onVerticalDragEnd:",
        "gameArtAsset(game.id)",
        "allowOwnerKick",
        "minLevel",
        "playerCount",
        "Future<void> logout()",
        "part 'v175_release.dart';",
        "final exact = xpRequirementsV175[safe];",
        "return (xpRequirementsV175[100]! * math.pow(1.12, extra)).round();",
    ])
    require("flutter_app/lib/v183_overhaul.dart", ["ResponsiveAccountStatsV170(controller: controller)", "HomeDashboardV183"])

    # The local Tarneeb page is not a lifecycle observer. The server room is.
    local_section = read("flutter_app/lib/main.dart").split("class _TarneebRoomPageState", 1)[1].split("class ServerEngineRoomPage", 1)[0]
    if "WidgetsBinding.instance.removeObserver(this);" in local_section:
        raise AssertionError("Local Tarneeb page still removes a WidgetsBinding observer it never registered")

    require("flutter_app/lib/v170_global.dart", [
        "buildV170TopBar",
        "class ResponsiveAccountStatsV170",
        "class ProductCardV170",
        "class _PashaColorAvatarV170",
        "showPublicPlayerProfileV170",
        "class OpenRoomCardV170",
        "class BotAvatarV170",
        "showChallengesV170",
        "class GroupInnovationHubV170",
        "class TarneebBidButtonV170",
    ])
    profile = read("flutter_app/lib/v170_global.dart").split("showPublicPlayerProfileV170", 1)[1].split("Future<void> inviteFriendV170", 1)[0]
    if "tokens" in profile.lower() or "coins" in profile.lower():
        raise AssertionError("Public player profile leaks private wallet/token data")

    require("flutter_app/lib/services/api_client.dart", [
        "void updateBaseUrl(String value)",
        "client_action_id",
        "state_revision",
        "inviteAllFriendsToRoom",
        "kickRoomPlayer",
        "Future<Map<String, dynamic>> gameSession",
    ])
    require("flutter_app/lib/services/voice_room_service.dart", [
        "Permission.microphone.request()",
        "Helper.setSpeakerphoneOn(true)",
        "echoCancellation",
        "noiseSuppression",
        "autoGainControl",
        "_pendingCandidates",
        "_reconnectPeer",
        "10.0.2.2",
        "رابط Laravel API منشوراً عبر HTTPS",
        "final intentionallyLocal = roomCode?.startsWith('LOCAL') ?? false;",
    ])
    require("flutter_app/lib/services/connection_diagnostics.dart", [
        "رابط خادم الهاتف",
        "loopbackApi",
        "استخدم HTTPS للخادم البعيد",
    ])
    require("flutter_app/lib/premium_v151.dart", [
        "inviteAllFriendsV170",
        "تحديد الكل",
    ])
    require("flutter_app/lib/engines/local_game_engine.dart", [
        "requestedPlayerCount",
        "return (seat + 1) % playerCount;",
    ])
    require("flutter_app/lib/engines/tarneeb_engine.dart", [
        "dealerSeat = (dealerSeat + 1) % 4;",
    ])
    forbid("flutter_app/lib/engines/local_game_engine.dart", [
        "currentSeat = (seat + playerCount - 1) % playerCount;",
    ])
    sound_files = list((ROOT / "flutter_app/assets/sounds").glob("*.wav"))
    if len(sound_files) < 30:
        raise AssertionError(f"Expected at least 30 sound cues; found {len(sound_files)}")

    require("backend-laravel/app/Models/User.php", [
        "public function publicProfile(): array",
        "'xp_next'=>(new \\App\\Services\\Leveling\\XpService())->requiredXp",
        "'country_code'",
        "'flag'",
        "'pasha_days'",
    ])
    public_profile = read("backend-laravel/app/Models/User.php").split("public function publicProfile", 1)[1]
    if "'tokens'" in public_profile or "'wallet'" in public_profile:
        raise AssertionError("Backend publicProfile leaks wallet or tokens")

    if int(meta["build"]) >= 175:
        require("flutter_app/lib/v175_release.dart", [
            "const Map<int, int> xpRequirementsV175",
            "1: 80",
            "40: 59371",
            "50: 150000",
            "80: 1000000",
            "100: 8000000",
        ])
        require("backend-laravel/app/Services/Leveling/XpService.php", [
            "config('warqna_xp_levels.'.$safe)",
            "config('warqna_xp_levels.100', 8000000)",
            "1.12 ** ($safe - 100)",
        ])
        require("backend-laravel/config/warqna_xp_levels.php", [
            "1 => 80",
            "40 => 59371",
            "50 => 150000",
            "80 => 1000000",
            "100 => 8000000",
        ])
    else:
        require("backend-laravel/app/Services/Leveling/XpService.php", [
            "[1=>100,2=>220,3=>360,4=>500,5=>650,6=>800,7=>1000]",
            "1000 + ($high * 220) + ($high * $high * 35)",
        ])
    require("backend-laravel/app/Http/Controllers/MobileGameController.php", [
        "'min_level' => 'nullable|integer|min:1|max:200'",
        "'allow_owner_kick' => 'nullable|boolean'",
        "kicked_user_ids",
        "client_action_id",
        "state_revision",
        "Cache::add",
        "unset($copy['hands']",
        "blockedWithParticipant",
        "لا يمكن دخول غرفة تضم لاعباً موجوداً في قائمة الحظر",
    ])
    require("backend-laravel/app/Http/Controllers/MobileSocialController.php", [
        "inviteAllToRoom",
        "transfer_fee",
        "fee_percent",
        "sendToUser",
    ])
    require("backend-laravel/app/Http/Controllers/MobileApiController.php", [
        "DailyRewardClaim::where('user_id', $user->id)->whereDate('claim_date', $today)->exists()",
        "تم استلام مكافأة اليوم مسبقاً",
    ])
    require("backend-laravel/app/Http/Controllers/TournamentController.php", [
        "event_log_with_final_hands",
        "blockedWithCreator",
        "blockedWithParticipant",
    ])
    require("backend-laravel/routes/api.php", [
        "room-invite-all",
        "kick",
    ])

    require(".github/workflows/flutter-android.yml", [
        "python3 ../tools/test_v170_contract.py",
        "--obfuscate",
        "--split-debug-info=",
        "flutter_app/build/symbols/**",
    ])
    for rel in [".github/workflows/flutter-web-pages.yml", ".github/workflows/flutter-ios.yml"]:
        require(rel, ["python3 ../tools/test_v170_contract.py"])
    require(".github/workflows/production-release-check.yml", ["python3 tools/test_v170_contract.py"])

    forbid("flutter_app/lib/services/voice_room_service.dart", [
        "final intentionallyLocal = !serverConnected",
    ])

    print("[PASS] Warqna v170 responsive UI, voice, gameplay, social, economy and anti-cheat contract")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except AssertionError as exc:
        print(f"[FAIL] {exc}")
        raise SystemExit(1)
