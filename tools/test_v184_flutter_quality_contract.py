#!/usr/bin/env python3
"""Regression contract for the V184 local-engine analyzer fix and medium-quality booster assets."""
from __future__ import annotations
import re
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]

def fail(message: str) -> None:
    raise SystemExit('[FAIL] ' + message)

engine_path = ROOT / 'flutter_app/lib/engines/local_game_engine.dart'
text = engine_path.read_text(encoding='utf-8')

play_start = text.index('void _playTrickCard')
play_end = text.index('void _autoBotsUntilHuman', play_start)
play_slice = text[play_start:play_end]
bad_return = "return currentSeat == seat ? _legalTrixCards(seat) : <String>[];"
if bad_return in play_slice:
    fail('void _playTrickCard still returns List<String>')

required = [
    "if (_isTrix && phase == 'trix_playing') {",
    "_playTrixCard(seat, card);",
    "for (var i = 0; i < playerCount; i++) {",
    "for (var seat = 0; seat < 4; seat++) {",
    "for (final hand in _hands) {",
    "for (final card in all) {",
    "for (final card in cards) {",
]
for needle in required:
    if needle not in text:
        fail(f'missing analyzer regression guard: {needle}')

for line_no, line in enumerate(text.splitlines(), 1):
    stripped = line.strip()
    if stripped.startswith('for (') and '{' not in stripped and stripped.endswith(';'):
        fail(f'for statement without block at local_game_engine.dart:{line_no}')

asset_dir = ROOT / 'flutter_app/assets/images/boosters/v183'
for color in ('yellow','green','red','blue','black','silver','gold'):
    full = asset_dir / f'booster_{color}.webp'
    thumb = asset_dir / f'booster_{color}_thumb.webp'
    if not full.is_file() or not thumb.is_file():
        fail(f'missing {color} booster images')
    with Image.open(full) as image:
        if image.size != (960, 960):
            fail(f'{color} booster must be 960x960, got {image.size}')
    with Image.open(thumb) as image:
        if image.size != (360, 360):
            fail(f'{color} booster thumbnail must be 360x360, got {image.size}')
    if not 70_000 <= full.stat().st_size <= 180_000:
        fail(f'{color} booster quality/size is outside the medium-quality target')

print('[PASS] V184 Flutter local-engine analyzer fix and medium-quality booster assets contract')
