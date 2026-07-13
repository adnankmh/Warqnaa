#!/usr/bin/env python3
from pathlib import Path
import json

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def require(rel: str, *needles: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"Missing {rel}")
    text = path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text:
            fail(f"Missing {needle!r} in {rel}")
    return text


def forbid(rel: str, *needles: str) -> None:
    text = require(rel)
    for needle in needles:
        if needle in text:
            fail(f"Forbidden regression {needle!r} in {rel}")


def main() -> None:
    meta = json.loads((ROOT / "RELEASE_VERSION.json").read_text(encoding="utf-8"))
    if int(meta.get("build", 0)) < 176:
        fail("metadata build is older than v176")

    main_dart = require(
        "flutter_app/lib/main.dart",
        "part 'v176_release.dart';",
        "packInventoryExpiriesV176",
        "dailyPackHistoryV176",
        "syncPackInventoryV176(data['inventory'])",
        "final navigationContext = warqnaNavigatorKey.currentContext;",
        "('inventory', 'مقتنياتي')",
    )
    if "_openingRoomRouteV174" in main_dart:
        fail("unused _openingRoomRouteV174 field returned")
    if "PackInventoryStripV176(" in main_dart or "DailyPackCardV176(" in main_dart:
        fail("legacy daily-pack UI returned to the store instead of the dedicated V0.2 prize-box page")

    release = require(
        "flutter_app/lib/v176_release.dart",
        "class DailyPackOpeningDialogV176",
        "animationController.repeat()",
        "applyDailyPackRewardV176",
        "syncPackInventoryV176",
        "purgeExpiredPackInventoryV176",
        "remainingForProductV176",
        "تمت إضافة الجائزة إلى مقتنياتك في المتجر",
        "جائزة ${rarityLabel(rarity)}",
    )
    if "final active = item['active'] == true" not in release:
        fail("server inventory active-state protection is missing")

    forbid("flutter_app/lib/v175_release.dart", "'${activated?'متابعة':'تفعيل'}'")
    require(
        "backend-laravel/app/Services/WarqnaPro/DailyPackService.php",
        "ensureRewardStoreItem",
        "InventoryItem",
        "'expires_at'=>$expires",
        "'store_item_key'",
        "daily_pack_name_gold_24h_v176",
        "daily_pack_xp_15x_6h_v176",
    )
    require(
        "backend-laravel/app/Http/Controllers/MobileEngagementController.php",
        "'inventory'=>$request->user()->inventoryItems()->with('storeItem')",
    )
    require(
        "backend-laravel/tests/Feature/V176DailyPackInventoryTest.php",
        "test_timed_pack_reward_is_added_to_store_inventory_with_expiry",
        "test_permanent_balance_reward_does_not_create_fake_inventory_item",
    )
    print("[PASS] v176 analyzer regression, animated daily pack, timed store inventory and expiry contract")


if __name__ == "__main__":
    main()
