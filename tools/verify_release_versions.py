#!/usr/bin/env python3
"""Verify every runtime and CI version against RELEASE_VERSION.json.

This replaces brittle hard-coded grep checks. A release bump now changes one metadata
file and the verifier reports every stale consumer with a precise filename.
"""
from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def read(rel: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail(f"Missing required file: {rel}")
    return path.read_text(encoding="utf-8")


def require(rel: str, needle: str) -> None:
    if needle not in read(rel):
        fail(f"Version contract mismatch in {rel}; missing: {needle}")


def forbid_regex(rel: str, pattern: str, message: str) -> None:
    if re.search(pattern, read(rel)):
        fail(f"{message}: {rel}")


def main() -> None:
    meta = json.loads(read("RELEASE_VERSION.json"))
    version = str(meta.get("version", "")).strip()
    build = meta.get("build")
    full = str(meta.get("full", "")).strip()
    release = str(meta.get("release", "")).strip()
    if not version or not isinstance(build, int):
        fail("RELEASE_VERSION.json is invalid")
    if full != f"{version}+{build}" or release != f"v{build}":
        fail("RELEASE_VERSION.json derived fields are inconsistent")

    require("flutter_app/pubspec.yaml", f"version: {full}")
    require("flutter_app/lib/services/api_client.dart", f"defaultValue: '{version}'")
    require("flutter_app/lib/services/api_client.dart", f"defaultValue: {build}")
    require("backend-laravel/config/warqna.php", f"env('WARQNA_VERSION', '{version}')")
    require("backend-laravel/config/warqna.php", f"env('WARQNA_BUILD', {build})")
    require("backend-laravel/.env.example", f"WARQNA_VERSION={version}")
    require("backend-laravel/.env.example", f"WARQNA_BUILD={build}")
    require("backend-laravel/.env.production.example", f"WARQNA_VERSION={version}")
    require("backend-laravel/.env.production.example", f"WARQNA_BUILD={build}")
    for rel in [
        "backend-laravel/app/Http/Controllers/MobilePlatformController.php",
        "backend-laravel/app/Http/Middleware/RequestContext.php",
        "backend-laravel/app/Services/Platform/PlatformHealthService.php",
        "backend-laravel/app/Services/Platform/ProductionConfigService.php",
    ]:
        require(rel, f"config('warqna.version', '{version}')")
    for rel in [
        "backend-laravel/app/Http/Controllers/MobilePlatformController.php",
        "backend-laravel/app/Services/Platform/PlatformHealthService.php",
        "backend-laravel/app/Services/Platform/ProductionConfigService.php",
    ]:
        require(rel, f"config('warqna.build', {build})")

    for rel in [
        ".github/workflows/flutter-android.yml",
        ".github/workflows/flutter-web-pages.yml",
        ".github/workflows/flutter-ios.yml",
    ]:
        require(rel, "python3 tools/release_metadata.py --github-output")
        require(rel, "steps.release.outputs.version")
        require(rel, "steps.release.outputs.build")
        forbid_regex(rel, r"WARQNA_APP_VERSION=\d+\.\d+\.\d+", "Hard-coded app version returned")
        forbid_regex(rel, r"WARQNA_APP_BUILD=\d+", "Hard-coded app build returned")

    require(".github/workflows/production-release-check.yml", "python3 tools/verify_release_versions.py")
    forbid_regex(
        ".github/workflows/production-release-check.yml",
        r"grep\s+-q\s+['\"](?:version:|WARQNA_APP_BUILD=)",
        "Brittle hard-coded release grep returned",
    )

    manifest_rel = f"releases/manifests/current/RELEASE_MANIFEST_V{build}.json"
    manifest = json.loads(read(manifest_rel))
    if manifest.get("version") != version or manifest.get("build") != build:
        fail(f"{manifest_rel} does not match RELEASE_VERSION.json")

    print(f"[PASS] Release version contract is consistent: {full}")


if __name__ == "__main__":
    main()
