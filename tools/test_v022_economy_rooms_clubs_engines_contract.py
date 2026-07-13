#!/usr/bin/env python3
from __future__ import annotations

import json
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
    try:
        build = int(meta.get('build', 0))
    except (TypeError, ValueError):
        build = 0
    if build < 178:
        fail('metadata predates the V0.2.2 inherited contract')

    main_dart = require(
        "flutter_app/lib/main.dart",
        "part 'v022_patch.dart';",
        "lastPurchaseErrorV022",
        "homeGameIdsV022",
        "delegatedAdminPermissionsV022",
        "final walletData = await api.wallet()",
        "selectedPashaStyle = 'red';",
    )
    if "🎩" in main_dart:
        fail("black-hat symbol returned to main.dart")

    require(
        "flutter_app/lib/v022_patch.dart",
        "freeThemeCodesV022",
        "showHomeGamePickerV022",
        "AdminDelegationsV022",
        "ClubManagementV022",
        "ClubIdentityV022",
        "showLeaderboardV022",
    )
    require(
        "flutter_app/lib/v170_global.dart",
        "rawAvatars",
        "showPublicPlayerProfileV170",
        "_PashaColorAvatarV170",
    )
    forbid("flutter_app/lib/v170_global.dart", "🎩")

    require(
        "backend-laravel/app/Http/Controllers/MobileSocialController.php",
        "min:10",
        "fee_percent",
        "transfer_fee",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileApiController.php",
        "app(StoreCatalogService::class)->sync()",
        "charged_price",
        "$timedReusable",
        "رصيد التوكنز على الخادم غير كافٍ",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileGameController.php",
        "whereIn('status', ['waiting', 'bidding', 'playing'])",
        "where('visibility', '!=', 'private')",
        "'avatars' =>",
        "'empty_seats' =>",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileClubController.php",
        "available_permissions",
        "updateMember",
        "view_audit_log",
        "ClubActivityService",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileAdminController.php",
        "AdminDelegation",
        "delegations",
        "guard",
    )
    require(
        "backend-laravel/app/Models/User.php",
        "clubMembership",
        "adminDelegation",
        "'admin_permissions'",
    )
    require(
        "backend-laravel/app/Services/GameEngine/GlobalEngines/GlobalCardEngineCore.php",
        "'meld_batch'",
        "'lay_off'",
        "suggestOpeningBatch",
        "isValidMeld",
        "meldBatch",
    )
    require(
        "backend-laravel/app/Services/GameEngine/GlobalEngines/BanakilEngine.php",
        "'opening' => 51",
        "'wildTwos' => true",
        "'firstExtra' => 1",
    )
    require(
        "backend-laravel/app/Services/GameEngine/GlobalEngines/HandPartnershipEngine.php",
        "'opening' => 51",
    )
    require(
        "backend-laravel/app/Services/GameEngine/GlobalEngines/SaudiHandEngine.php",
        "'opening' => 51",
    )
    require(
        "backend-laravel/routes/api.php",
        "Route::get('/clubs/{club}/activity'",
        "Route::patch('/clubs/{club}/members/{member}'",
        "Route::get('/admin/delegations'",
    )
    require(
        "backend-laravel/tests/Feature/V022EconomyRoomsClubsEnginesTest.php",
        "test_purchase_uses_the_authoritative_wallet_and_returns_the_updated_balance",
        "test_registry_and_rummy_adapter_support_real_51_opening_and_lay_offs",
        "test_public_room_and_admin_contracts_are_present",
    )

    print("[PASS] V0.2.2 economy, public rooms, clubs, delegated admin, themes and 51-meld engine contract")


if __name__ == "__main__":
    main()
