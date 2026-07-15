#!/usr/bin/env python3
from __future__ import annotations

import json
import re
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
    meta = json.loads((ROOT / "RELEASE_VERSION.json").read_text(encoding="utf-8"))
    version_parts = str(meta.get("version", "0.0.0")).split('.')
    if tuple(int(x) for x in version_parts[:2]) < (0, 2):
        fail("release metadata predates the inherited V0.2 prize-box contract")

    main_dart = require(
        "flutter_app/lib/main.dart",
        "part 'v02_release.dart';",
        "syncPrizeBoxesV02(data['prize_boxes'])",
        "CompetitionTicketPreviewV183(denomination: product.value!",
        "selectedPashaStyle = 'red';",
    )
    require("flutter_app/lib/v183_overhaul.dart", "PrizeBoxesHomeCardV02", "PrizeBoxesPageV02")

    if "DailyPackCardV176(" in main_dart or "PackInventoryStripV176(" in main_dart:
        fail("daily packs are still displayed inside the store")

    v02 = require(
        "flutter_app/lib/v02_release.dart",
        "prizeBoxDailyLimitV02 = 4",
        "class PrizeBoxOpeningDialogV02",
        "Duration(seconds: 5)",
        "rotateX(panel * 1.18)",
        "'pasha_day'",
        "'writing_color'",
        "'player_color'",
        "'profile_cover'",
        "'tokens'",
        "'ticket'",
    )
    for lang in ("ar", "en", "de", "tr", "fr", "es"):
        if f"'{lang}': <String, String>" not in v02:
            fail(f"missing {lang} prize-box translations")

    assets = ROOT / "flutter_app/assets/images/v02"
    box_keys = ["crimson_lion", "emerald_eagle", "bronze_dragon", "obsidian", "royal_amethyst", "diamond_phoenix"]
    for key in box_keys:
        for suffix in ("", "_lid", "_body", "_front_panel"):
            if not (assets / "prize_boxes" / f"{key}{suffix}.png").is_file():
                fail(f"missing box asset {key}{suffix}.png")

    ticket_values = [50,100,200,500,1000,2000,4000,5000,8000,10000,20000,30000,50000,100000]
    for value in ticket_values:
        path = assets / "tickets" / f"ticket_{value}.png"
        if not path.is_file() or path.stat().st_size < 10000:
            fail(f"missing/invalid ticket asset {value}")

    original_pasha = ROOT / "flutter_app/assets/images/pasha.png"
    reward_pasha = assets / "rewards/pasha_day.png"
    if not original_pasha.is_file() or not reward_pasha.is_file():
        fail("original red Pasha assets missing")

    require(
        "backend-laravel/app/Services/WarqnaPro/PrizeBoxService.php",
        "public const DAILY_LIMIT = 4",
        "awardForCompletedGame",
        "random_int(1, 20) * 50",
        "'value' => '200'",
        "pasha_style = 'red'",
    )
    require(
        "backend-laravel/routes/api.php",
        "Route::get('/prize-boxes'",
        "Route::post('/prize-boxes/{prizeBox}/open'",
    )
    require(
        "backend-laravel/app/Services/Progression/ProgressionService.php",
        "PrizeBoxService",
        "'prize_box'",
        "awardForCompletedGame",
    )
    require(
        "backend-laravel/database/migrations/2026_07_13_000200_create_v02_prize_boxes.php",
        "Schema::create('prize_boxes'",
        "prize_boxes_user_source_unique",
    )
    require("backend-laravel/tests/Feature/V02DailyPrizeBoxesTest.php", "test_one_varied_box_is_awarded_per_win_up_to_four_per_day")

    pubspec = require("flutter_app/pubspec.yaml", f"version: {meta['version']}+{meta['build']}", "assets/images/v02/prize_boxes/", "assets/images/v02/tickets/", "assets/images/v02/rewards/")
    if len(re.findall(r"ticket_\$value", v02)) < 1:
        fail("ticket asset helper is missing")

    print("[PASS] inherited V0.2 prize boxes, ticket art, original Pasha, translations and server economy contract")


if __name__ == "__main__":
    main()
