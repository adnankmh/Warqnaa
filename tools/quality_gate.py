#!/usr/bin/env python3
"""Cross-platform Warqna source quality gate.

Runs deterministic source contracts and PHP syntax checks. Flutter checks run when
Flutter is installed, or become mandatory with --require-flutter.
"""

from __future__ import annotations

import argparse
import os
from pathlib import Path
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]

CONTRACTS = [
    "tools/verify_release_versions.py",
    # validate_release.py already runs the complete inherited feature-contract suite.
    "tools/validate_release.py",
]
PHP_DIRS = [
    ROOT / "backend-laravel" / "app",
    ROOT / "backend-laravel" / "bootstrap",
    ROOT / "backend-laravel" / "config",
    ROOT / "backend-laravel" / "database",
    ROOT / "backend-laravel" / "routes",
    ROOT / "backend-laravel" / "tests",
]


def run(command: list[str], *, cwd: Path = ROOT) -> None:
    printable = " ".join(command)
    print(f"\n[RUN] {printable}", flush=True)
    subprocess.run(command, cwd=cwd, check=True)


def run_contracts() -> None:
    for relative in CONTRACTS:
        run([sys.executable, relative])


def lint_php(require_php: bool) -> None:
    php = shutil.which("php")
    if not php:
        message = "PHP was not found; PHP syntax audit was skipped."
        if require_php:
            raise RuntimeError(message)
        print(f"[SKIP] {message}")
        return

    files = sorted(
        path
        for directory in PHP_DIRS
        if directory.exists()
        for path in directory.rglob("*.php")
    )
    for index, path in enumerate(files, start=1):
        result = subprocess.run(
            [php, "-l", str(path)],
            cwd=ROOT,
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True,
        )
        if result.returncode != 0:
            print(result.stdout)
            raise RuntimeError(f"PHP syntax failed: {path.relative_to(ROOT)}")
        if index % 50 == 0 or index == len(files):
            print(f"[PASS] PHP syntax {index}/{len(files)}")


def verify_flutter_lock() -> None:
    lock = ROOT / "flutter_app" / "pubspec.lock"
    if not lock.exists():
        print("[INFO] pubspec.lock is absent; Flutter pub get will create it.")
        return
    run(
        [
            sys.executable,
            "tools/verify_flutter_lock.py",
            "flutter_app/pubspec.lock",
            "google_mobile_ads=7.0.0",
            "flutter_webrtc=1.4.0",
            "firebase_core=4.11.0",
            "firebase_messaging=16.4.1",
        ]
    )


def run_flutter(require_flutter: bool) -> None:
    flutter = shutil.which("flutter")
    if not flutter:
        message = "Flutter SDK was not found; analyze and tests were skipped."
        if require_flutter:
            raise RuntimeError(message)
        print(f"[SKIP] {message}")
        return

    app = ROOT / "flutter_app"
    run([flutter, "pub", "get"], cwd=app)
    verify_flutter_lock()
    if os.name == "nt":
        run([flutter, "analyze", "--no-fatal-infos", "--no-fatal-warnings"], cwd=app)
    else:
        run(["bash", "../tools/flutter_analyze_ci.sh"], cwd=app)
    run([flutter, "test"], cwd=app)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--require-flutter", action="store_true")
    parser.add_argument("--require-php", action="store_true")
    parser.add_argument("--skip-flutter", action="store_true")
    args = parser.parse_args()

    try:
        run_contracts()
        lint_php(args.require_php)
        if not args.skip_flutter:
            run_flutter(args.require_flutter)
    except (subprocess.CalledProcessError, RuntimeError) as exc:
        print(f"\n[FAIL] {exc}")
        return 1

    print("\n[PASS] Warqna source quality gate completed successfully.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
