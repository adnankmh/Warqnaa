#!/usr/bin/env python3
"""Warqna v172 additive brand and 40-table collection contract."""
from __future__ import annotations

import json
import re
import struct
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MAIN = ROOT / "flutter_app/lib/main.dart"
PUBSPEC = ROOT / "flutter_app/pubspec.yaml"
BACKEND = ROOT / "backend-laravel/app/Services/WarqnaPro/StoreCatalogService.php"
CATALOG = ROOT / "flutter_app/assets/images/tables/reference/catalog.json"
LOGO = ROOT / "flutter_app/assets/images/brand/warqna_logo.png"
WORKFLOWS = [
    ROOT / ".github/workflows/flutter-web-pages.yml",
    ROOT / ".github/workflows/flutter-android.yml",
    ROOT / ".github/workflows/flutter-ios.yml",
    ROOT / ".github/workflows/production-release-check.yml",
]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def png_size(path: Path) -> tuple[int, int]:
    data = path.read_bytes()[:24]
    if len(data) != 24 or data[:8] != b"\x89PNG\r\n\x1a\n" or data[12:16] != b"IHDR":
        fail(f"Invalid PNG asset: {path.relative_to(ROOT)}")
    return struct.unpack(">II", data[16:24])


def main() -> None:
    source = MAIN.read_text(encoding="utf-8")
    compact = re.sub(r"\s+", " ", source)
    exact_required = [
        "onPressed: () => showFriends(context, controller)",
        "onTap: () => showFriends(context, widget.controller)",
    ]
    missing = [needle for needle in exact_required if needle not in compact]
    if missing:
        fail("Legacy and semantic controller compatibility is incomplete: " + repr(missing))

    old_ids = [f"table_premium_{i:02d}" for i in range(1, 51)]
    new_ids = [f"table_reference_{i:02d}" for i in range(1, 41)]
    for table_id in old_ids:
        if table_id not in source:
            fail(f"Legacy table was removed instead of preserved: {table_id}")
    for table_id in new_ids:
        if source.count(f'id: "{table_id}"') != 1:
            fail(f"New Flutter table ID must be declared exactly once: {table_id}")

    catalog = json.loads(CATALOG.read_text(encoding="utf-8"))
    if len(catalog) != 40:
        fail(f"Reference table catalog must contain 40 entries, found {len(catalog)}")
    if [entry.get("id") for entry in catalog] != new_ids:
        fail("Reference table catalog IDs are not continuous from 01 to 40")
    for entry in catalog:
        asset = ROOT / "flutter_app" / entry["asset"]
        if not asset.is_file():
            fail(f"Missing reference table image: {entry['asset']}")
        width, height = png_size(asset)
        if width < 1024 or height < 600:
            fail(f"Reference table image is below HD storefront size: {entry['asset']} ({width}x{height})")

    if not LOGO.is_file():
        fail("Warqna brand logo is missing")
    logo_width, logo_height = png_size(LOGO)
    if min(logo_width, logo_height) < 512:
        fail(f"Warqna brand logo is too small: {logo_width}x{logo_height}")

    pubspec = PUBSPEC.read_text(encoding="utf-8")
    for needle in ["assets/images/brand/", "assets/images/tables/"]:
        if needle not in pubspec:
            fail(f"Flutter assets registration is missing: {needle}")
    for needle in [
        "assets/images/brand/warqna_logo.png",
        "reference_1",
        "reference_4",
        "imageAsset",
    ]:
        if needle not in source:
            fail(f"Flutter v172 store/brand contract missing: {needle}")

    if "if (p.id.startsWith('table_reference_')) return false;" not in source:
        fail("V183 must hide all temporary reference tables from the customer store")

    topbar = (ROOT / "flutter_app/lib/v170_global.dart").read_text(encoding="utf-8")
    if "assets/images/brand/warqna_logo.png" not in topbar:
        fail("Top bar does not display the Warqna brand")
    web = (ROOT / "flutter_app/web/index.html").read_text(encoding="utf-8")
    if '<img src="icons/Icon-192.png" alt="Warqnaa">' not in web:
        fail("Web install card does not use the new brand icon")

    backend = BACKEND.read_text(encoding="utf-8")
    for table_id in old_ids + new_ids:
        if table_id not in backend:
            fail(f"Backend store catalog is missing table: {table_id}")
    for needle in ["array_merge($this->legacyTableSkins(), $this->referenceTableSkins())", "image_asset", "reference_4"]:
        if needle not in backend:
            fail(f"Backend additive table contract missing: {needle}")

    for workflow in WORKFLOWS:
        text = workflow.read_text(encoding="utf-8")
        if "test_v171_controller_references.py" not in text or "test_v172_brand_table_contract.py" not in text:
            fail(f"Workflow lacks semantic and v172 contracts: {workflow.name}")
        if "Incorrect controller references in lib/main.dart" in text:
            fail(f"Brittle inline controller check returned: {workflow.name}")
    android = (ROOT / ".github/workflows/flutter-android.yml").read_text(encoding="utf-8")
    if "apply_brand_assets.py" not in android:
        fail("Android workflow does not reapply the Warqna icons after flutter create")

    print("[PASS] v172 assets remain compatible while V183 hides the 40 temporary tables and removes their customer tabs")


if __name__ == "__main__":
    main()
