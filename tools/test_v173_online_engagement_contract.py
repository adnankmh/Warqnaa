#!/usr/bin/env python3
"""V0.3 regression contract for inherited V173 assets and the hybrid online/local economy."""
from __future__ import annotations
import json, re, struct
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TICKET_VALUES = [50,100,200,500,1000,2000,4000,5000,8000,10000,20000,30000,50000,100000]
PASHA_KEYS = ["yellow","red","crimson","blue","green","purple","bronze","gold","orange","pink","silver","platinum","navy","white"]

def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)

def png_size(path: Path) -> tuple[int,int]:
    data=path.read_bytes()[:24]
    if len(data)<24 or data[:8]!=b"\x89PNG\r\n\x1a\n": fail(f"Invalid PNG: {path.relative_to(ROOT)}")
    return struct.unpack(">II",data[16:24])

def require_text(rel: str, needles: list[str]) -> str:
    path=ROOT/rel
    if not path.is_file(): fail(f"Missing file: {rel}")
    text=path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text: fail(f"Missing contract in {rel}: {needle}")
    return text

def main() -> None:
    pasha_dir=ROOT/"flutter_app/assets/images/pasha/v173"
    for key in PASHA_KEYS:
        path=pasha_dir/f"pasha_{key}.png"
        if not path.is_file(): fail(f"Missing Pasha style asset: {key}")
        width,height=png_size(path)
        if width<220 or height<150: fail(f"Pasha crop too small: {key} ({width}x{height})")

    catalog=json.loads((ROOT/"backend-laravel/resources/data/v173_store_catalog.json").read_text(encoding="utf-8"))
    if any(item.get("key")=="pasha_style_black_v173" for item in catalog): fail("Black Pasha style remains in the active catalog")
    groups: dict[str,list[dict]]={}
    for item in catalog: groups.setdefault(item["category"],[]).append(item)
    if len(groups.get("pasha_style",[]))!=14 or len(groups.get("table",[]))!=50 or len(groups.get("competition_ticket",[]))!=14:
        fail("V0.3 inherited catalog counts are incorrect")
    tickets=sorted(groups["competition_ticket"],key=lambda item:item["payload"]["denomination"])
    if [item["payload"]["denomination"] for item in tickets]!=TICKET_VALUES: fail("Ticket denominations mismatch")

    flutter=require_text("flutter_app/lib/v173_global.dart",[
        "warqnaOnlineOnlyV173 = false",
        "competitionTicketValuesV173",
        "openDailyPackV173",
        "joinCompetitionV173",
        "(key == 'black' ? 'red' : key)",
    ])
    if len(re.findall(r"PashaStyleV173\('",flutter))!=14: fail("Flutter Pasha registry must contain 14 non-black color entries")
    if "PashaStyleV173('black'" in flutter: fail("Black Pasha registry entry remains")

    main_dart=require_text("flutter_app/lib/main.dart",[
        "coins -= BigInt.from(price);",
        "lastStoreError = e.message;",
        "Web/GitHub Pages preview: keep the temporary rewarded-ad feature testable",
        "coins += BigInt.from(tokens);",
        "Future<String?> transferLocal",
        "if (amount < 10)",
        "final fee = (amount * .10).ceil();",
    ])
    if "if (!serverConnected) return false;" in main_dart[main_dart.find("Future<bool> buy"):main_dart.find("int priceFor")]:
        fail("Store still rejects all offline purchases")

    require_text("backend-laravel/app/Services/WarqnaPro/StoreCatalogService.php",[
        "pasha_style_black_v173",
        "['active'=>false",
    ])
    require_text("flutter_app/lib/services/rewarded_ads_mobile.dart",[
        "ADMOB_REWARDED_ANDROID_ID","ADMOB_REWARDED_IOS_ID","MobileAds.instance.initialize()","RewardedAd.load",
    ])
    require_text("backend-laravel/routes/api.php",["/engagement/center","/packs/daily/open","/competitions/{competitionKey}/join","/admin/designer"])
    require_text("backend-laravel/app/Http/Controllers/MobileAdminController.php",["guardPrimaryDesigner","designerIndex","upsertDesigner","deleteDesigner"])
    print("[PASS] V0.3 inherited V173 assets, non-black Pasha registry, hybrid store/ads, competitions and protected designer contract")

if __name__=="__main__": main()
