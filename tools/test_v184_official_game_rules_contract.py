#!/usr/bin/env python3
"""Warqnaa V184 official-rules and release-packaging regression contract."""
from __future__ import annotations

import struct
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    raise SystemExit('[FAIL] ' + message)


def read(rel: str, *needles: str) -> str:
    path = ROOT / rel
    if not path.is_file():
        fail('missing ' + rel)
    data = path.read_text(encoding='utf-8')
    for needle in needles:
        if needle not in data:
            fail(f'missing {needle!r} in {rel}')
    return data


def png_size(path: Path) -> tuple[int, int]:
    with path.open('rb') as handle:
        signature = handle.read(24)
    if len(signature) < 24 or signature[:8] != b'\x89PNG\r\n\x1a\n' or signature[12:16] != b'IHDR':
        fail('invalid PNG header: ' + path.relative_to(ROOT).as_posix())
    return struct.unpack('>II', signature[16:24])


def main() -> None:
    if (ROOT / 'MANIFEST_SHA256.txt').exists():
        fail('MANIFEST_SHA256.txt must not be committed in the clean project root')

    reference_dir = ROOT / 'flutter_app/assets/images/tables/reference'
    images = sorted(reference_dir.glob('table_reference_*.png'))
    if len(images) != 40:
        fail(f'expected 40 reference tables, found {len(images)}')
    for image in images:
        width, height = png_size(image)
        if width < 1024 or height < 600:
            fail(f'reference table below HD storefront size: {image.relative_to(ROOT)} ({width}x{height})')

    read(
        'backend-laravel/app/Services/GameEngine/GlobalEngines/SyrianTarneebEngine.php',
        "'mode' => 'syrian41'",
        "'minBid' => 2",
        "'targetScore' => 41",
        "'fixedTrumpFromDeal' => true",
    )
    core = read(
        'backend-laravel/app/Services/GameEngine/GlobalEngines/GlobalCardEngineCore.php',
        "['trick','trick400','syrian41'",
        'oppositeSameColorSuit',
        'teamOpeningThresholds',
        'recordRummyOpening',
    )
    if "['trick400','syrian41']" not in core:
        fail('independent trick-bid modes are not protected together')

    read(
        'backend-laravel/app/Services/GameEngine/GlobalEngines/HandPartnershipEngine.php',
        "'teamOpening' => true",
        "'openingEscalates' => true",
    )
    read(
        'flutter_app/lib/engines/local_game_engine.dart',
        "gameId == 'syrian_tarneeb'",
        '_oppositeSameColorSuitLocal',
        '_teamOpeningThresholds',
        '_recordLocalRummyOpening',
    )
    read(
        'backend-laravel/tools/test-v184-official-rules-audit.php',
        'Syrian Tarneeb 41',
        'Partnership Hand',
        'Banakil',
        'Trix Complex',
        'Baloot',
    )
    read(
        'backend-laravel/tools/test-v184-engine-stress.php',
        'engine stress scenarios completed',
        "'syrian_tarneeb'",
        "'hand_partner'",
        "'baloot'",
        "'jackaroo'",
        "'chess'",
    )

    workflow_needles = {
        '.github/workflows/backend-ci.yml': [
            'php tools/test-v142-rule-cores.php',
            'php tools/test-v184-official-rules-audit.php',
            'php tools/test-v184-engine-stress.php',
        ],
        '.github/workflows/production-release-check.yml': [
            'python3 tools/test_v184_official_game_rules_contract.py',
            'php backend-laravel/tools/test-v142-rule-cores.php',
            'php backend-laravel/tools/test-v184-official-rules-audit.php',
            'php backend-laravel/tools/test-v184-engine-stress.php',
        ],
        '.github/workflows/flutter-web-pages.yml': [
            'python3 ../tools/test_v184_official_game_rules_contract.py',
        ],
        '.github/workflows/flutter-android.yml': [
            'python3 ../tools/test_v184_official_game_rules_contract.py',
        ],
        '.github/workflows/flutter-ios.yml': [
            'python3 ../tools/test_v184_official_game_rules_contract.py',
        ],
    }
    for rel, needles in workflow_needles.items():
        read(rel, *needles)

    print('[PASS] V184 clean-root, HD reference tables, Syrian 41, partnership Hand and official-rules CI contract')


if __name__ == '__main__':
    main()
