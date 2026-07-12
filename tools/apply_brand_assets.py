#!/usr/bin/env python3
"""Apply Warqna brand icons after `flutter create` regenerates Android."""
from pathlib import Path
import shutil

ROOT = Path(__file__).resolve().parents[1]
APP = ROOT / "flutter_app"
SOURCE = APP / "assets/images/brand/android"
RES = APP / "android/app/src/main/res"
DENSITIES = ("mdpi", "hdpi", "xhdpi", "xxhdpi", "xxxhdpi")

if not SOURCE.is_dir():
    raise SystemExit(f"Brand icon source directory is missing: {SOURCE}")
if not RES.is_dir():
    raise SystemExit(f"Generated Android resources directory is missing: {RES}")

for density in DENSITIES:
    target_dir = RES / f"mipmap-{density}"
    target_dir.mkdir(parents=True, exist_ok=True)
    for stem in ("ic_launcher", "ic_launcher_round"):
        src = SOURCE / f"{stem}_{density}.png"
        if not src.is_file():
            raise SystemExit(f"Missing brand icon: {src}")
        shutil.copy2(src, target_dir / f"{stem}.png")

# Prefer the supplied PNG icons rather than generated adaptive placeholders.
anydpi = RES / "mipmap-anydpi-v26"
for name in ("ic_launcher.xml", "ic_launcher_round.xml"):
    candidate = anydpi / name
    if candidate.exists():
        candidate.unlink()

print("Warqna Android brand icons applied successfully.")
