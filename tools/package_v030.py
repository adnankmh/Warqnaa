#!/usr/bin/env python3
"""Create and verify the complementary two-part Warqnaa V0.3 source release."""
from __future__ import annotations

import hashlib
import json
import shutil
import tempfile
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT.parent
PART_PATHS = [OUT_DIR / 'Warqnaa-V0.3-PART-1.zip', OUT_DIR / 'Warqnaa-V0.3-PART-2.zip']
SPLIT_FILE = ROOT / 'releases/manifests/current/PACKAGE_SPLIT_V0.3.json'
MANIFEST_FILE = ROOT / 'releases/manifests/current/SOURCE_MANIFEST_V0.3.sha256'
SKIP_DIRS = {'.git', '__pycache__', '.dart_tool', 'build', 'vendor', 'node_modules'}
SKIP_SUFFIXES = {'.pyc', '.pyo'}


def should_include(path: Path) -> bool:
    if not path.is_file():
        return False
    rel = path.relative_to(ROOT)
    if any(part in SKIP_DIRS for part in rel.parts):
        return False
    if path.suffix.lower() in SKIP_SUFFIXES:
        return False
    return True


def sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open('rb') as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b''):
            h.update(chunk)
    return h.hexdigest()


def source_files(exclude_metadata: bool = False) -> list[Path]:
    files = []
    for path in ROOT.rglob('*'):
        if not should_include(path):
            continue
        if exclude_metadata and path in {SPLIT_FILE, MANIFEST_FILE}:
            continue
        files.append(path)
    return sorted(files, key=lambda p: p.relative_to(ROOT).as_posix())


def balanced_partition(files: list[Path]) -> tuple[list[Path], list[Path]]:
    bins: list[list[Path]] = [[], []]
    sizes = [0, 0]
    for path in sorted(files, key=lambda p: (-p.stat().st_size, p.relative_to(ROOT).as_posix())):
        idx = 0 if sizes[0] <= sizes[1] else 1
        bins[idx].append(path)
        sizes[idx] += path.stat().st_size
    for bucket in bins:
        bucket.sort(key=lambda p: p.relative_to(ROOT).as_posix())
    return bins[0], bins[1]


def write_metadata(part1: list[Path], part2: list[Path]) -> None:
    assignment = {p.relative_to(ROOT).as_posix(): 1 for p in part1}
    assignment.update({p.relative_to(ROOT).as_posix(): 2 for p in part2})
    assignment[SPLIT_FILE.relative_to(ROOT).as_posix()] = 1
    assignment[MANIFEST_FILE.relative_to(ROOT).as_posix()] = 1
    payload = {
        'release': 'Warqnaa V0.3',
        'version': '0.3.0+180',
        'type': 'complementary-two-part-source-package',
        'instructions_ar': 'فك الجزأين داخل المجلد نفسه. لا يعمل المشروع كاملًا من جزء واحد فقط.',
        'parts': {
            '1': {'archive': PART_PATHS[0].name},
            '2': {'archive': PART_PATHS[1].name},
        },
        'file_assignment': dict(sorted(assignment.items())),
    }
    SPLIT_FILE.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

    manifest_lines = []
    for path in source_files(exclude_metadata=False):
        if path == MANIFEST_FILE:
            continue
        manifest_lines.append(f'{sha256(path)}  {path.relative_to(ROOT).as_posix()}')
    MANIFEST_FILE.write_text('\n'.join(manifest_lines) + '\n', encoding='utf-8')


def create_zip(zip_path: Path, files: list[Path]) -> None:
    if zip_path.exists():
        zip_path.unlink()
    with zipfile.ZipFile(zip_path, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=6, allowZip64=True) as zf:
        for path in files:
            zf.write(path, path.relative_to(ROOT).as_posix())


def validate_archives(expected_files: list[Path]) -> dict[str, int]:
    member_sets: list[set[str]] = []
    for archive in PART_PATHS:
        with zipfile.ZipFile(archive) as zf:
            bad = zf.testzip()
            if bad:
                raise RuntimeError(f'Corrupt ZIP member in {archive.name}: {bad}')
            names = {n for n in zf.namelist() if not n.endswith('/')}
            if len(names) != len([n for n in zf.namelist() if not n.endswith('/')]):
                raise RuntimeError(f'Duplicate paths inside {archive.name}')
            member_sets.append(names)
    overlap = member_sets[0] & member_sets[1]
    if overlap:
        raise RuntimeError(f'Overlapping paths between archives: {sorted(overlap)[:5]}')
    expected_names = {p.relative_to(ROOT).as_posix() for p in expected_files}
    union = member_sets[0] | member_sets[1]
    if union != expected_names:
        missing = sorted(expected_names - union)[:10]
        extra = sorted(union - expected_names)[:10]
        raise RuntimeError(f'Archive union mismatch; missing={missing}, extra={extra}')

    with tempfile.TemporaryDirectory(prefix='warqnaa-v03-verify-') as tmp:
        target = Path(tmp)
        for archive in PART_PATHS:
            with zipfile.ZipFile(archive) as zf:
                zf.extractall(target)
        for source in expected_files:
            relative = source.relative_to(ROOT)
            extracted = target / relative
            if not extracted.is_file():
                raise RuntimeError(f'Missing extracted file: {relative.as_posix()}')
            if sha256(source) != sha256(extracted):
                raise RuntimeError(f'Checksum mismatch after extraction: {relative.as_posix()}')

    return {
        'part1_files': len(member_sets[0]),
        'part2_files': len(member_sets[1]),
        'total_files': len(union),
        'part1_bytes': PART_PATHS[0].stat().st_size,
        'part2_bytes': PART_PATHS[1].stat().st_size,
    }


def main() -> None:
    for metadata in (SPLIT_FILE, MANIFEST_FILE):
        metadata.unlink(missing_ok=True)
    base = source_files(exclude_metadata=True)
    part1, part2 = balanced_partition(base)
    write_metadata(part1, part2)
    part1 = sorted(part1 + [SPLIT_FILE, MANIFEST_FILE], key=lambda p: p.relative_to(ROOT).as_posix())
    all_expected = sorted(part1 + part2, key=lambda p: p.relative_to(ROOT).as_posix())
    create_zip(PART_PATHS[0], part1)
    create_zip(PART_PATHS[1], part2)
    report = validate_archives(all_expected)
    print('[PASS] Complementary two-part package created and fully verified')
    for key, value in report.items():
        print(f'{key}={value}')
    for path in PART_PATHS:
        print(f'{path.name}: {path.stat().st_size / (1024 * 1024):.2f} MiB sha256={sha256(path)}')


if __name__ == '__main__':
    main()
