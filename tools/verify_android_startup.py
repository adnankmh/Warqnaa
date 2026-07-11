#!/usr/bin/env python3
"""Static launch-safety validation for the generated Android project."""
from __future__ import annotations

import argparse
import re
import xml.etree.ElementTree as ET
from pathlib import Path

ANDROID_NS = "http://schemas.android.com/apk/res/android"
A = f"{{{ANDROID_NS}}}"
APP_ID_RE = re.compile(r"^ca-app-pub-\d{16}~\d{10}$")
REQUIRED_PERMISSIONS = {
    "android.permission.INTERNET",
    "android.permission.ACCESS_NETWORK_STATE",
    "android.permission.RECORD_AUDIO",
    "android.permission.MODIFY_AUDIO_SETTINGS",
}


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    raise SystemExit(1)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--manifest", required=True)
    parser.add_argument("--gradle", required=True)
    args = parser.parse_args()

    manifest = Path(args.manifest)
    gradle = Path(args.gradle)
    tree = ET.parse(manifest)
    root = tree.getroot()
    application = root.find("application")
    if application is None:
        fail("Missing application element")

    permissions = {node.get(A + "name") for node in root.findall("uses-permission")}
    missing = sorted(REQUIRED_PERMISSIONS - permissions)
    if missing:
        fail("Missing permissions: " + ", ".join(missing))

    app_id = None
    for node in application.findall("meta-data"):
        if node.get(A + "name") == "com.google.android.gms.ads.APPLICATION_ID":
            app_id = (node.get(A + "value") or "").strip()
            break
    if not app_id or not APP_ID_RE.fullmatch(app_id):
        fail("AdMob application ID is absent or malformed; Android can crash before Flutter starts")

    launcher = next(
        (node for node in application.findall("activity") if (node.get(A + "name") or "").endswith("MainActivity")),
        None,
    )
    if launcher is None or launcher.get(A + "exported") != "true":
        fail("MainActivity is missing or not exported")

    gradle_text = gradle.read_text(encoding="utf-8")
    if not re.search(r"minSdk\s*=\s*24|minSdkVersion\s+24", gradle_text):
        fail("Android minSdk is not 24")
    if not re.search(r"compileSdk\s*=\s*36|compileSdkVersion\s+36", gradle_text):
        fail("Android compileSdk is not 36")

    print(f"[PASS] Android startup contract: valid App ID {app_id}, launcher, permissions and SDK levels")


if __name__ == "__main__":
    main()
