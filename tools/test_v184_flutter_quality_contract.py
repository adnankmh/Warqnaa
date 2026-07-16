#!/usr/bin/env python3
"""Regression contract for the V184 local-engine analyzer fix and medium-quality booster assets."""
from __future__ import annotations
import re
import struct
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def webp_dimensions(path: Path) -> tuple[int, int]:
    """Read WebP dimensions using only the Python standard library."""
    data = path.read_bytes()
    if len(data) < 30 or data[:4] != b"RIFF" or data[8:12] != b"WEBP":
        fail(f"invalid WebP file: {path.relative_to(ROOT)}")
    chunk = data[12:16]
    if chunk == b"VP8X":
        width = 1 + int.from_bytes(data[24:27], "little")
        height = 1 + int.from_bytes(data[27:30], "little")
        return width, height
    if chunk == b"VP8L":
        if data[20] != 0x2F:
            fail(f"invalid lossless WebP header: {path.relative_to(ROOT)}")
        b0, b1, b2, b3 = data[21:25]
        width = 1 + (b0 | ((b1 & 0x3F) << 8))
        height = 1 + ((b1 >> 6) | (b2 << 2) | ((b3 & 0x0F) << 10))
        return width, height
    if chunk == b"VP8 ":
        signature = data.find(b"\x9d\x01\x2a", 20, 40)
        if signature < 0 or signature + 7 > len(data):
            fail(f"invalid lossy WebP header: {path.relative_to(ROOT)}")
        width = struct.unpack_from("<H", data, signature + 3)[0] & 0x3FFF
        height = struct.unpack_from("<H", data, signature + 5)[0] & 0x3FFF
        return width, height
    fail(f"unsupported WebP chunk {chunk!r}: {path.relative_to(ROOT)}")


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
    full_size = webp_dimensions(full)
    thumb_size = webp_dimensions(thumb)
    if full_size != (960, 960):
        fail(f'{color} booster must be 960x960, got {full_size}')
    if thumb_size != (360, 360):
        fail(f'{color} booster thumbnail must be 360x360, got {thumb_size}')
    if not 70_000 <= full.stat().st_size <= 180_000:
        fail(f'{color} booster quality/size is outside the medium-quality target')


# Room-creation and admin-quality guards added after the CI hotfix.
level_options = (ROOT / 'flutter_app/lib/v170_global.dart').read_text(encoding='utf-8')
if 'List<int>.generate(ceiling, (index) => index + 1' not in level_options:
    fail('room minimum-level selector must expose every level from 1 to the creator level')

room_options = (ROOT / 'flutter_app/lib/models/room_launch_options.dart').read_text(encoding='utf-8')
for needle in ('final int botCount;', 'final List<int> inviteeIds;', 'this.botCount = 3', 'this.inviteeIds = const <int>[]'):
    if needle not in room_options:
        fail(f'missing room participant option: {needle}')

main_text = (ROOT / 'flutter_app/lib/main.dart').read_text(encoding='utf-8')
for needle in (
    'اختيار المشاركين قبل إنشاء الغرفة',
    'selectedInviteeIds',
    'inviteFriendToRoom(userId, createdCode)',
    'widget.options.botCount.clamp',
    'adminUpdateGame(id, active: value)',
):
    if needle not in main_text:
        fail(f'missing room/admin integration: {needle}')


# The mobile/web catalog exposes eighteen playable entries and keeps Chess hidden.
catalog_match = re.search(r"const gamesCatalog = \[(.*?)\n\];", main_text, re.S)
if not catalog_match:
    fail('gamesCatalog block is missing')
catalog_block = catalog_match.group(1)
if len(re.findall(r"GameInfo\(", catalog_block)) != 18:
    fail('Flutter catalog must expose exactly 18 games')
if "GameInfo('chess'" in catalog_block:
    fail('Chess must remain hidden from the 18-game player catalog')
for key in ('tarneeb_41','tarneeb_61','pinochle','solitaire_multiplayer','domino','backgammon'):
    if f"GameInfo('{key}'" not in catalog_block:
        fail(f'missing activated server game: {key}')

api_text = (ROOT / 'flutter_app/lib/services/api_client.dart').read_text(encoding='utf-8')
if "patch('/admin/games/$gameId'" not in api_text:
    fail('mobile admin game visibility API is missing')


# Hand-family regression: the starter receives one extra card and must discard
# before the normal draw/meld/discard cycle begins.
engine_test = (ROOT / 'flutter_app/test/local_game_engine_test.dart').read_text(encoding='utf-8')
for needle in (
    "Hand family starts with 15 cards, discards once, then draws normally",
    "expect((initial['hand'] as List).length, 15",
    "expect(initial['phase'], 'discard'",
    "expect((afterStarterDiscard['hand'] as List).length, 14",
    "expect(afterStarterDiscard['phase'], 'draw'",
    "Banakil starts with 19 cards, discards once, then returns with 18",
):
    if needle not in engine_test:
        fail(f'missing Hand/Banakil regression assertion: {needle}')
if "Hand family deals 14 cards and requires a draw" in engine_test:
    fail('obsolete Hand opening test is still present')

print('[PASS] V184 Flutter analyzer, dependency-free image validation, room participants, full level selector and admin game visibility contract')
