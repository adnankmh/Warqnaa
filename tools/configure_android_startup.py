#!/usr/bin/env python3
"""Create a launch-safe Android manifest for Warqna.

The downloadable APK always uses Google's documented sample AdMob application
ID. The Play AAB may use a production application ID, but only when it matches
the documented app-ID shape. This prevents an ad-unit ID or arbitrary value in
a GitHub variable from crashing Android before Flutter paints its first frame.
"""
from __future__ import annotations

import argparse
import re
import xml.etree.ElementTree as ET
from pathlib import Path

ANDROID_NS = "http://schemas.android.com/apk/res/android"
A = f"{{{ANDROID_NS}}}"
ET.register_namespace("android", ANDROID_NS)

SAMPLE_APP_ID = "ca-app-pub-3940256099942544~3347511713"
APP_ID_RE = re.compile(r"^ca-app-pub-\d{16}~\d{10}$")

PERMISSIONS: tuple[tuple[str, str | None], ...] = (
    ("android.permission.INTERNET", None),
    ("android.permission.ACCESS_NETWORK_STATE", None),
    ("android.permission.CHANGE_NETWORK_STATE", None),
    ("android.permission.POST_NOTIFICATIONS", None),
    ("android.permission.RECORD_AUDIO", None),
    ("android.permission.MODIFY_AUDIO_SETTINGS", None),
    ("android.permission.BLUETOOTH", "30"),
    ("android.permission.BLUETOOTH_ADMIN", "30"),
    ("android.permission.BLUETOOTH_CONNECT", None),
)

FEATURES: tuple[tuple[str, bool], ...] = (
    ("android.hardware.microphone", False),
    ("android.hardware.camera", False),
    ("android.hardware.camera.autofocus", False),
)


def valid_app_id(value: str) -> bool:
    return bool(APP_ID_RE.fullmatch(value.strip()))


def select_app_id(candidate: str, mode: str) -> tuple[str, str]:
    candidate = candidate.strip()
    if mode == "safe-apk":
        return SAMPLE_APP_ID, "Google sample App ID (safe APK)"
    if valid_app_id(candidate):
        return candidate, "validated production App ID"
    return SAMPLE_APP_ID, "Google sample App ID (production variable absent or invalid)"


def ensure_permission(root: ET.Element, name: str, max_sdk: str | None) -> None:
    for node in root.findall("uses-permission"):
        if node.get(A + "name") == name:
            if max_sdk:
                node.set(A + "maxSdkVersion", max_sdk)
            return
    node = ET.Element("uses-permission", {A + "name": name})
    if max_sdk:
        node.set(A + "maxSdkVersion", max_sdk)
    application = root.find("application")
    index = list(root).index(application) if application is not None else len(root)
    root.insert(index, node)


def ensure_feature(root: ET.Element, name: str, required: bool) -> None:
    for node in root.findall("uses-feature"):
        if node.get(A + "name") == name:
            node.set(A + "required", "true" if required else "false")
            return
    node = ET.Element(
        "uses-feature",
        {A + "name": name, A + "required": "true" if required else "false"},
    )
    application = root.find("application")
    index = list(root).index(application) if application is not None else len(root)
    root.insert(index, node)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--manifest", required=True)
    parser.add_argument("--app-id", default="")
    parser.add_argument("--api-url", default="")
    parser.add_argument("--mode", choices=("safe-apk", "production-aab"), required=True)
    args = parser.parse_args()

    path = Path(args.manifest)
    tree = ET.parse(path)
    root = tree.getroot()
    application = root.find("application")
    if application is None:
        raise SystemExit("Android manifest has no <application> element")

    for name, max_sdk in PERMISSIONS:
        ensure_permission(root, name, max_sdk)
    for name, required in FEATURES:
        ensure_feature(root, name, required)

    application.set(A + "label", "Warqnaa")
    application.set(A + "hardwareAccelerated", "true")
    application.set(
        A + "usesCleartextTraffic",
        "true" if args.api_url.strip().lower().startswith("http://") else "false",
    )

    selected, reason = select_app_id(args.app_id, args.mode)
    metadata = None
    for node in application.findall("meta-data"):
        if node.get(A + "name") == "com.google.android.gms.ads.APPLICATION_ID":
            metadata = node
            break
    if metadata is None:
        metadata = ET.SubElement(application, "meta-data")
        metadata.set(A + "name", "com.google.android.gms.ads.APPLICATION_ID")
    metadata.set(A + "value", selected)

    activities = application.findall("activity")
    launcher = next(
        (node for node in activities if (node.get(A + "name") or "").endswith("MainActivity")),
        None,
    )
    if launcher is None:
        raise SystemExit("Generated manifest has no MainActivity")
    launcher.set(A + "exported", "true")

    try:
        ET.indent(tree, space="    ")
    except AttributeError:
        pass
    tree.write(path, encoding="utf-8", xml_declaration=True)
    print(f"Android manifest configured with {reason}: {selected}")


if __name__ == "__main__":
    main()
