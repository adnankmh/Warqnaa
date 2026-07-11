#!/usr/bin/env python3
"""Prevent Android WorkManager auto-start crashes before Flutter paints.

Manifest merger contract: tools:node=merge on the provider and tools:node=remove on WorkManagerInitializer.

Some devices can crash while AndroidX Startup creates WorkManager's Room-backed
WorkDatabase before MainActivity or Dart code begins. Warqna does not require
background workers at process start, so this script removes the eager
WorkManagerInitializer from the merged startup provider, installs a lightweight
Application-level Configuration.Provider for any later lazy WorkManager use, and
turns off release shrink/minification that can strip generated Room classes.
"""
from __future__ import annotations

import argparse
import re
import xml.etree.ElementTree as ET
from pathlib import Path

ANDROID_NS = "http://schemas.android.com/apk/res/android"
TOOLS_NS = "http://schemas.android.com/tools"
A = f"{{{ANDROID_NS}}}"
T = f"{{{TOOLS_NS}}}"
ET.register_namespace("android", ANDROID_NS)
ET.register_namespace("tools", TOOLS_NS)

WORK_PROVIDER = "androidx.startup.InitializationProvider"
WORK_INITIALIZER = "androidx.work.WorkManagerInitializer"

JAVA_TEMPLATE = '''package {package_name};

import android.app.Application;
import android.util.Log;
import androidx.work.Configuration;

/** Launch-safe WorkManager configuration for Warqna.
 *
 * AndroidX Startup is prevented from eagerly creating WorkManager's internal
 * WorkDatabase at process bind time. If a plugin later asks for WorkManager,
 * AndroidX will lazily initialize it using this conservative configuration.
 */
public final class WarqnaApplication extends Application implements Configuration.Provider {{
    @Override
    public Configuration getWorkManagerConfiguration() {{
        return new Configuration.Builder()
                .setMinimumLoggingLevel(Log.ERROR)
                .build();
    }}
}}
'''

PROGUARD_RULES = '''
# Warqna v165 Android startup safety.
# Keep WorkManager / Room generated implementation classes intact in release builds.
-keep class androidx.work.** { *; }
-keep class androidx.room.** { *; }
-keep class androidx.sqlite.** { *; }
-keep class * extends androidx.room.RoomDatabase { *; }
-keep class **_Impl { *; }
-keep class **_*Dao_Impl { *; }
-dontwarn androidx.work.**
-dontwarn androidx.room.**
-dontwarn androidx.sqlite.**
'''.lstrip()


def fail(message: str) -> None:
    raise SystemExit(message)


def package_from_main_activity(android_dir: Path) -> str:
    candidates = list((android_dir / "app" / "src" / "main").rglob("MainActivity.kt"))
    candidates += list((android_dir / "app" / "src" / "main").rglob("MainActivity.java"))
    for path in candidates:
        text = path.read_text(encoding="utf-8", errors="ignore")
        match = re.search(r"^\s*package\s+([A-Za-z_][\w]*(?:\.[A-Za-z_][\w]*)+)\s*;?", text, re.M)
        if match:
            return match.group(1)
    fail("Unable to determine Android package from generated MainActivity")


def ensure_workmanager_manifest(manifest: Path) -> None:
    tree = ET.parse(manifest)
    root = tree.getroot()
    application = root.find("application")
    if application is None:
        fail("Android manifest has no application element")

    application.set(A + "name", ".WarqnaApplication")

    provider = None
    for node in application.findall("provider"):
        if node.get(A + "name") == WORK_PROVIDER:
            provider = node
            break
    if provider is None:
        provider = ET.SubElement(application, "provider")
        provider.set(A + "name", WORK_PROVIDER)
        provider.set(A + "authorities", "${applicationId}.androidx-startup")
        provider.set(A + "exported", "false")
    provider.set(T + "node", "merge")

    metadata = None
    for node in provider.findall("meta-data"):
        if node.get(A + "name") == WORK_INITIALIZER:
            metadata = node
            break
    if metadata is None:
        metadata = ET.SubElement(provider, "meta-data")
        metadata.set(A + "name", WORK_INITIALIZER)
    metadata.set(A + "value", "androidx.startup")
    metadata.set(T + "node", "remove")

    try:
        ET.indent(tree, space="    ")
    except AttributeError:
        pass
    tree.write(manifest, encoding="utf-8", xml_declaration=True)


def write_application(android_dir: Path, package_name: str) -> Path:
    java_dir = android_dir / "app" / "src" / "main" / "java" / Path(*package_name.split('.'))
    java_dir.mkdir(parents=True, exist_ok=True)
    target = java_dir / "WarqnaApplication.java"
    target.write_text(JAVA_TEMPLATE.format(package_name=package_name), encoding="utf-8")
    return target


def add_dependency_block_if_missing(text: str) -> str:
    kotlin = 'plugins {' in text and 'id("' in text
    required = [
        ("androidx.work:work-runtime", 'implementation("androidx.work:work-runtime:2.9.1")' if kotlin else "implementation 'androidx.work:work-runtime:2.9.1'"),
        ("com.android.tools:desugar_jdk_libs", 'coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.1.4")' if kotlin else "coreLibraryDesugaring 'com.android.tools:desugar_jdk_libs:2.1.4'"),
        ("androidx.window:window:", 'implementation("androidx.window:window:1.0.0")' if kotlin else "implementation 'androidx.window:window:1.0.0'"),
        ("androidx.window:window-java", 'implementation("androidx.window:window-java:1.0.0")' if kotlin else "implementation 'androidx.window:window-java:1.0.0'"),
    ]
    missing = [line for key, line in required if key not in text]
    if not missing:
        return text
    indented = '\n'.join('    ' + line for line in missing)
    if re.search(r"(?m)^dependencies\s*\{", text):
        return re.sub(r"(?m)^dependencies\s*\{", "dependencies {\n" + indented, text, count=1)
    return text.rstrip() + "\n\ndependencies {\n" + indented + "\n}\n"


def ensure_notification_gradle(text: str, kotlin: bool) -> str:
    """Apply flutter_local_notifications Android requirements to generated Gradle."""
    if kotlin:
        text = re.sub(r'sourceCompatibility\s*=\s*JavaVersion\.VERSION_\d+', 'sourceCompatibility = JavaVersion.VERSION_17', text)
        text = re.sub(r'targetCompatibility\s*=\s*JavaVersion\.VERSION_\d+', 'targetCompatibility = JavaVersion.VERSION_17', text)
        text = re.sub(r'jvmTarget\s*=\s*JavaVersion\.VERSION_\d+\.toString\(\)', 'jvmTarget = JavaVersion.VERSION_17.toString()', text)
        text = re.sub(r'jvmTarget\s*=\s*"\d+"', 'jvmTarget = "17"', text)
        if 'isCoreLibraryDesugaringEnabled = true' not in text:
            match = re.search(r'compileOptions\s*\{', text)
            if match:
                text = text[:match.end()] + '\n        isCoreLibraryDesugaringEnabled = true' + text[match.end():]
            else:
                android = re.search(r'android\s*\{', text)
                if android:
                    block = '\n    compileOptions {\n        isCoreLibraryDesugaringEnabled = true\n        sourceCompatibility = JavaVersion.VERSION_17\n        targetCompatibility = JavaVersion.VERSION_17\n    }\n'
                    text = text[:android.end()] + block + text[android.end():]
        if 'multiDexEnabled = true' not in text:
            match = re.search(r'defaultConfig\s*\{', text)
            if match:
                text = text[:match.end()] + '\n        multiDexEnabled = true' + text[match.end():]
    else:
        text = re.sub(r'sourceCompatibility\s+JavaVersion\.VERSION_\d+', 'sourceCompatibility JavaVersion.VERSION_17', text)
        text = re.sub(r'targetCompatibility\s+JavaVersion\.VERSION_\d+', 'targetCompatibility JavaVersion.VERSION_17', text)
        text = re.sub(r'jvmTarget\s*=\s*["\']\d+["\']', "jvmTarget = '17'", text)
        if 'coreLibraryDesugaringEnabled true' not in text:
            match = re.search(r'compileOptions\s*\{', text)
            if match:
                text = text[:match.end()] + '\n        coreLibraryDesugaringEnabled true' + text[match.end():]
            else:
                android = re.search(r'android\s*\{', text)
                if android:
                    block = '\n    compileOptions {\n        coreLibraryDesugaringEnabled true\n        sourceCompatibility JavaVersion.VERSION_17\n        targetCompatibility JavaVersion.VERSION_17\n    }\n'
                    text = text[:android.end()] + block + text[android.end():]
        if 'multiDexEnabled true' not in text:
            match = re.search(r'defaultConfig\s*\{', text)
            if match:
                text = text[:match.end()] + '\n        multiDexEnabled true' + text[match.end():]
    return text

def ensure_release_flags(text: str, kotlin: bool) -> str:
    minify = "            isMinifyEnabled = false" if kotlin else "            minifyEnabled false"
    shrink = "            isShrinkResources = false" if kotlin else "            shrinkResources false"
    if "isMinifyEnabled = false" in text or "minifyEnabled false" in text:
        has_minify = True
    else:
        has_minify = False
    if "isShrinkResources = false" in text or "shrinkResources false" in text:
        has_shrink = True
    else:
        has_shrink = False

    additions = []
    if not has_minify:
        additions.append(minify)
    if not has_shrink:
        additions.append(shrink)
    if not additions:
        return text

    m = re.search(r"release\s*\{", text)
    if m:
        insert_at = m.end()
        return text[:insert_at] + "\n" + "\n".join(additions) + text[insert_at:]

    block = "\n    buildTypes {\n        release {\n" + "\n".join(additions) + "\n        }\n    }\n"
    # Insert before the final closing brace of the android block; generated Flutter files
    # end with the android block before dependencies, so using the last brace is stable enough.
    idx = text.rfind("}\n")
    if idx == -1:
        return text + block
    return text[:idx] + block + text[idx:]


def configure_gradle(gradle: Path) -> None:
    text = gradle.read_text(encoding="utf-8")
    kotlin = gradle.name.endswith(".kts")
    text = add_dependency_block_if_missing(text)
    text = ensure_notification_gradle(text, kotlin=kotlin)
    text = ensure_release_flags(text, kotlin=kotlin)
    gradle.write_text(text, encoding="utf-8")

    rules = gradle.parent / "proguard-rules.pro"
    existing = rules.read_text(encoding="utf-8") if rules.exists() else ""
    if "Warqna v165 Android startup safety" not in existing:
        rules.write_text(existing.rstrip() + "\n\n" + PROGUARD_RULES, encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--manifest", required=True)
    parser.add_argument("--gradle", required=True)
    args = parser.parse_args()

    manifest = Path(args.manifest)
    gradle = Path(args.gradle)
    android_dir = manifest.parents[3]
    package_name = package_from_main_activity(android_dir)
    ensure_workmanager_manifest(manifest)
    app_path = write_application(android_dir, package_name)
    configure_gradle(gradle)
    print(f"[PASS] WorkManager startup guard installed for {package_name}: {app_path}")


if __name__ == "__main__":
    main()
