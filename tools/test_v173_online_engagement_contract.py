#!/usr/bin/env python3
"""Inherited v173 engagement, cosmetics, assets, and online-economy contract.

V174 keeps store/reward/competition transactions server-authoritative while allowing
local login and local gameplay when connectivity is unavailable.
"""
from __future__ import annotations

import json
import re
import struct
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TICKET_VALUES = [50, 100, 200, 500, 1000, 2000, 4000, 5000, 8000, 10000, 20000, 30000, 50000, 100000]
PASHA_KEYS = ["yellow", "red", "blue", "green", "purple", "bronze", "gold", "orange", "pink", "silver", "platinum", "navy", "black", "white"]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def jpeg_size(path: Path) -> tuple[int, int]:
    data = path.read_bytes()
    if not data.startswith(b"\xff\xd8"):
        fail(f"Invalid JPEG: {path.relative_to(ROOT)}")
    i = 2
    while i + 9 < len(data):
        if data[i] != 0xFF:
            i += 1
            continue
        marker = data[i + 1]
        i += 2
        if marker in {0xD8, 0xD9}:
            continue
        if i + 2 > len(data):
            break
        length = struct.unpack(">H", data[i:i+2])[0]
        if marker in {0xC0, 0xC1, 0xC2, 0xC3, 0xC5, 0xC6, 0xC7, 0xC9, 0xCA, 0xCB, 0xCD, 0xCE, 0xCF}:
            height, width = struct.unpack(">HH", data[i+3:i+7])
            return width, height
        i += length
    fail(f"JPEG dimensions unavailable: {path.relative_to(ROOT)}")


def png_size(path: Path) -> tuple[int, int]:
    data = path.read_bytes()[:24]
    if len(data) < 24 or data[:8] != b"\x89PNG\r\n\x1a\n":
        fail(f"Invalid PNG: {path.relative_to(ROOT)}")
    return struct.unpack(">II", data[16:24])


def require_text(rel: str, needles: list[str]) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"Missing file: {rel}")
    text = path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text:
            fail(f"Missing v173 contract in {rel}: {needle}")
    return text


def main() -> None:
    pasha_dir = ROOT / "flutter_app/assets/images/pasha/v173"
    for key in PASHA_KEYS:
        path = pasha_dir / f"pasha_{key}.png"
        if not path.is_file():
            fail(f"Missing Pasha style: {key}")
        width, height = png_size(path)
        if width < 220 or height < 150:
            fail(f"Pasha crop too small: {path.relative_to(ROOT)} ({width}x{height})")

    royal = sorted((ROOT / "flutter_app/assets/images/tables/v173/royal").glob("table_v173_royal_*.jpg"))
    showcase = sorted((ROOT / "flutter_app/assets/images/tables/v173/showcase").glob("table_v173_showcase_*.jpg"))
    if len(royal) != 30 or len(showcase) != 20:
        fail(f"Expected 30 RAR tables and 20 collage tables, found {len(royal)} + {len(showcase)}")
    for path in royal + showcase:
        width, height = jpeg_size(path)
        if width < 1000 or height < 550:
            fail(f"V173 table below HD storefront dimensions: {path.relative_to(ROOT)} ({width}x{height})")

    catalog_path = ROOT / "backend-laravel/resources/data/v173_store_catalog.json"
    catalog = json.loads(catalog_path.read_text(encoding="utf-8"))
    if len(catalog) != 78:
        fail(f"V173 catalog should contain 78 items, found {len(catalog)}")
    groups: dict[str, list[dict]] = {}
    for item in catalog:
        groups.setdefault(item["category"], []).append(item)
    expected_counts = {"pasha_style": 14, "table": 50, "competition_ticket": 14}
    if {key: len(groups.get(key, [])) for key in expected_counts} != expected_counts:
        fail(f"Catalog category counts mismatch: { {key: len(groups.get(key, [])) for key in expected_counts} }")
    tickets = sorted(groups["competition_ticket"], key=lambda item: item["payload"]["denomination"])
    if [item["payload"]["denomination"] for item in tickets] != TICKET_VALUES:
        fail("Competition ticket denominations do not match the approved list")
    for item in tickets:
        denomination = int(item["payload"]["denomination"])
        if int(item["price"]) != round(denomination * 0.90):
            fail(f"Ticket discount is not exactly 10%: {denomination} -> {item['price']}")

    flutter = require_text("flutter_app/lib/v173_global.dart", [
        "warqnaOnlineOnlyV173 = false",
        "competitionTicketValuesV173",
        "openDailyPackV173",
        "joinCompetitionV173",
        "class OnlineRequiredScreenV173",
        "class UniversalDesignerV173",
        "class DesignerEntityManagerV173",
        "assets/images/tables/v173/royal/table_v173_royal_30.jpg",
        "assets/images/tables/v173/showcase/table_v173_showcase_20.jpg",
    ])
    if len(re.findall(r"PashaStyleV173\('", flutter)) != 14:
        fail("Flutter Pasha style registry must contain exactly 14 styles")

    main_dart = require_text("flutter_app/lib/main.dart", [
        "part 'v173_global.dart';",
        "unawaited(initializeWarqnaRewardedAdsAfterFirstFrame());",
        "if (!serverConnected) return false;",
        "Future<String?> _registerLocal",
        "Future<String?> _loginLocal",
    ])
    if "coins -= BigInt.from(priceFor(product));" in main_dart:
        fail("Offline store debit path still exists")
    for forbidden in [
        "coins += BigInt.from(tokens);",
        "coins += BigInt.from(100);",
        "controller.addCoins(200, 'شحن تجريبي')",
        "result = await widget.controller.transferLocal",
    ]:
        if forbidden in main_dart:
            fail(f"Offline economy fallback still exists: {forbidden}")
    for required in [
        "if (!serverConnected) return 'يلزم اتصال فعّال بالخادم لاعتماد مكافأة الإعلان.';",
        "if (!serverConnected) {\n      notices.insert(0, AppNotice('📡', 'المكافأة اليومية'",
        "boosterExpiresAtV173 != null",
        "temporaryTableExpiresAtV173 != null",
        "تحويل التوكنز متاح عبر الخادم فقط في النسخة V173",
    ]:
        if required not in main_dart:
            fail(f"Missing online-economy hardening contract: {required}")

    require_text("flutter_app/lib/services/rewarded_ads_mobile.dart", [
        "ADMOB_REWARDED_ANDROID_ID",
        "ADMOB_REWARDED_IOS_ID",
        "MobileAds.instance.initialize()",
        "RewardedAd.load",
    ])
    require_text("backend-laravel/routes/api.php", [
        "/engagement/center",
        "/packs/daily/open",
        "/competitions/{competitionKey}/join",
        "/admin/designer",
    ])
    require_text("backend-laravel/app/Services/WarqnaPro/DailyPackService.php", [
        "DailyPackClaim::create",
        "duration_hours",
        "CompetitionTicket::firstOrCreate",
    ])
    require_text("backend-laravel/app/Services/WarqnaPro/CompetitionService.php", [
        "where('denomination', '>=', (int)$tournament->entry_fee)",
        "orderBy('denomination')",
        "random_seating",
        "auto_accept",
    ])
    require_text("backend-laravel/app/Http/Controllers/MobileAdminController.php", [
        "designerIndex",
        "upsertDesigner",
        "deleteDesigner",
        "designer_entities",
    ])
    require_text("backend-laravel/database/migrations/2026_07_12_000173_online_competitions_tickets_packs_designer.php", [
        "competition_tickets",
        "daily_pack_claims",
        "challenge_definitions",
        "admin_designer_entities",
        "online_only",
    ])

    for workflow in [
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-ios.yml",
        ".github/workflows/production-release-check.yml",
    ]:
        require_text(workflow, ["test_v173_online_engagement_contract.py"])

    print("[PASS] inherited v173 contract: 14 Pasha styles, 50 HD tables, server-authoritative economy, ads, daily packs, competitions, tickets, and universal designer")


if __name__ == "__main__":
    main()
