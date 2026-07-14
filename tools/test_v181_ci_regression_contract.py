#!/usr/bin/env python3
"""Regression guard for the Flutter analyzer and Laravel CI fixes reported after V0.3."""
from __future__ import annotations
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def require(condition: bool, message: str) -> None:
    if not condition:
        raise SystemExit(f"[FAIL] {message}")

def main() -> None:
    main_dart = (ROOT / "flutter_app/lib/main.dart").read_text(encoding="utf-8")
    engine = (ROOT / "flutter_app/lib/engines/tarneeb_engine.dart").read_text(encoding="utf-8")
    v175 = (ROOT / "flutter_app/lib/v175_release.dart").read_text(encoding="utf-8")
    composer = json.loads((ROOT / "backend-laravel/composer.json").read_text(encoding="utf-8"))
    catalog = json.loads((ROOT / "backend-laravel/resources/data/v173_store_catalog.json").read_text(encoding="utf-8"))

    require("if (challengeRoadGame != null) {" in main_dart, "leaveChallenge must use a braced if block")
    require("List<int> get teamScores => List<int>.unmodifiable(scores);" in engine, "Tarneeb teamScores compatibility getter missing")
    require("DropdownButtonFormField<String>(initialValue:selectedRoadGame" in v175, "Deprecated DropdownButtonFormField.value remains")
    require("DropdownButtonFormField<String>(value:selectedRoadGame" not in v175, "Deprecated DropdownButtonFormField.value remains")
    require(composer.get("autoload-dev", {}).get("psr-4", {}).get("Database\\Factories\\") == "database/factories/", "Database factory PSR-4 mapping missing")
    categories = {}
    for item in catalog:
        categories[item["category"]] = categories.get(item["category"], 0) + 1
    require(len(catalog) == 78, f"V173 catalog must have 78 items, found {len(catalog)}")
    require(categories.get("pasha_style") == 14, "V173 catalog must have 14 non-black Pasha styles")
    require(not any(item.get("key") == "pasha_style_black_v173" for item in catalog), "Black Pasha style must remain inactive/absent")
    require(any(item.get("key") == "pasha_style_crimson_v173" for item in catalog), "Crimson replacement Pasha style missing")
    print("[PASS] V181 Flutter analyzer, factory autoload and V173 catalog regression contract")

if __name__ == "__main__":
    main()
