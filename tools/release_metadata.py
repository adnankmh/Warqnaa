#!/usr/bin/env python3
"""Read the single source of truth for Warqna release metadata."""
from __future__ import annotations

import argparse
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
META_PATH = ROOT / "RELEASE_VERSION.json"


def load_metadata() -> dict[str, object]:
    data = json.loads(META_PATH.read_text(encoding="utf-8"))
    version = str(data.get("version", "")).strip()
    build = data.get("build")
    full = str(data.get("full", "")).strip()
    release = str(data.get("release", "")).strip()
    if not version or not isinstance(build, int) or build < 1:
        raise SystemExit("RELEASE_VERSION.json must contain a semantic version and positive integer build.")
    expected_full = f"{version}+{build}"
    expected_release = f"v{build}"
    if full != expected_full:
        raise SystemExit(f"Invalid full version: expected {expected_full}, found {full!r}.")
    if release != expected_release:
        raise SystemExit(f"Invalid release label: expected {expected_release}, found {release!r}.")
    return {"version": version, "build": build, "full": full, "release": release}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--github-output", action="store_true")
    parser.add_argument("--shell", action="store_true")
    parser.add_argument("--json", action="store_true")
    args = parser.parse_args()
    meta = load_metadata()

    if args.github_output:
        print(f"version={meta['version']}")
        print(f"build={meta['build']}")
        print(f"full={meta['full']}")
        print(f"release={meta['release']}")
    elif args.shell:
        print(f"WARQNA_APP_VERSION={meta['version']}")
        print(f"WARQNA_APP_BUILD={meta['build']}")
        print(f"WARQNA_FULL_VERSION={meta['full']}")
        print(f"WARQNA_RELEASE={meta['release']}")
    elif args.json:
        print(json.dumps(meta, ensure_ascii=False, sort_keys=True))
    else:
        print(meta["full"])


if __name__ == "__main__":
    main()
