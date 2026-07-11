#!/usr/bin/env python3
"""Static launch-safety validation for the generated Android project."""
from __future__ import annotations

import argparse
import re
import xml.etree.ElementTree as ET
from pathlib import Path

ANDROID_NS = "http://schemas.android.com/apk/res/android"
TOOLS_NS = "http://schemas.android.com/tools"
A = f"{{{ANDROID_NS}}}"
T = f"{{{TOOLS_NS}}}"
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

    if application.get(A + "name") != ".WarqnaApplication":
        fail("WarqnaApplication is not registered as the Android application class")

    provider = next(
        (node for node in application.findall("provider") if node.get(A + "name") == "androidx.startup.InitializationProvider"),
        None,
    )
    if provider is None:
        fail("AndroidX Startup provider override is missing")
    if provider.get(T + "node") != "merge":
        fail("AndroidX Startup provider must use tools:node=merge")
    work_meta = next(
        (node for node in provider.findall("meta-data") if node.get(A + "name") == "androidx.work.WorkManagerInitializer"),
        None,
    )
    if work_meta is None or work_meta.get(T + "node") != "remove":
        fail("WorkManagerInitializer is not removed from AndroidX Startup")

    app_java = manifest.parent / "java"
    if not list(app_java.rglob("WarqnaApplication.java")):
        fail("WarqnaApplication.java is missing")

    gradle_text = gradle.read_text(encoding="utf-8")
    if not re.search(r"minSdk\s*=\s*24|minSdkVersion\s+24", gradle_text):
        fail("Android minSdk is not 24")
    if not re.search(r"compileSdk\s*=\s*36|compileSdkVersion\s+36", gradle_text):
        fail("Android compileSdk is not 36")
    if "androidx.work:work-runtime" not in gradle_text:
        fail("Direct WorkManager runtime dependency is missing")
    if "coreLibraryDesugaring" not in gradle_text or "desugar_jdk_libs:2.1.4" not in gradle_text:
        fail("flutter_local_notifications core library desugaring dependency is missing")
    if not re.search(r"isCoreLibraryDesugaringEnabled\s*=\s*true|coreLibraryDesugaringEnabled\s+true", gradle_text):
        fail("Core library desugaring is not enabled")
    if not re.search(r"multiDexEnabled\s*=\s*true|multiDexEnabled\s+true", gradle_text):
        fail("Android multidex is not enabled")
    if "JavaVersion.VERSION_17" not in gradle_text:
        fail("Android Java compatibility is not 17")
    if not re.search(r"isMinifyEnabled\s*=\s*false|minifyEnabled\s+false", gradle_text):
        fail("Release minification is not explicitly disabled")
    if not re.search(r"isShrinkResources\s*=\s*false|shrinkResources\s+false", gradle_text):
        fail("Release resource shrinking is not explicitly disabled")

    print(
        "[PASS] Android startup contract: valid App ID, launcher, permissions, "
        "SDK levels, notification desugaring and WorkManager pre-Flutter guard"
    )


if __name__ == "__main__":
    main()
