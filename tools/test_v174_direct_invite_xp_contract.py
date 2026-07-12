#!/usr/bin/env python3
"""Static release contract for Warqna V174."""
from pathlib import Path
import json, re, sys
ROOT = Path(__file__).resolve().parents[1]

def fail(message: str) -> None:
    print('[FAIL]', message)
    raise SystemExit(1)

def text(rel: str) -> str:
    path = ROOT / rel
    if not path.exists(): fail(f'Missing {rel}')
    return path.read_text(encoding='utf-8')

release = json.loads(text('RELEASE_VERSION.json'))
if release.get('full') != '1.74.0+174': fail('Release metadata is not V174')
if 'version: 1.74.0+174' not in text('flutter_app/pubspec.yaml'): fail('Flutter pubspec is not V174')
main = text('flutter_app/lib/main.dart')
api = text('flutter_app/lib/services/api_client.dart')
game = text('backend-laravel/app/Http/Controllers/MobileGameController.php')
progression = text('backend-laravel/app/Services/Progression/ProgressionService.php')
xp = text('backend-laravel/app/Services/Leveling/XpService.php')
routes = text('backend-laravel/routes/api.php')

required_main = [
    'warqnaNavigatorKey', 'queueNavigationRoute', 'openPendingNavigationRoute',
    'loginWithSocialProvider', 'consumeServerProgression',
    'DeviceOrientation.portraitUp', 'PushNotifications.onTap = queueNavigationRoute',
    '_prepareDirectInviteTransfer', 'await api.leaveGame(previousCode)',
    'final parentContext = context', 'final navigationContext = warqnaNavigatorKey.currentContext ?? parentContext',
    'await openGameRoom(navigationContext, controller, game, options: options)',
    "if (!registerMode) ...[",
]
for needle in required_main:
    if needle not in main: fail(f'Missing Flutter V174 contract: {needle}')
for forbidden in ['await controller.setLandscapeMode(true)', 'if (!registerMode && false)', '_openLocalRoom(']:
    if forbidden in main: fail(f'Forbidden obsolete behavior remains: {forbidden}')
for needle in ['gameSessionPreview', "defaultValue: '1.74.0'", 'defaultValue: 174']:
    if needle not in api: fail(f'Missing API V174 contract: {needle}')
for needle in ["'/games/session/{room:code}/preview'", "'progression_popup'", "if ($player->is_bot || !$player->user) continue;"]:
    if needle not in routes + game: fail(f'Missing server V174 contract: {needle}')
for needle in ["'won'=>(bool)($meta['won'] ?? false)", "'level'=>is_array($meta['level_result'] ?? null)"]:
    if needle not in progression: fail(f'Missing progression payload: {needle}')
for needle in ['=> 1.20', '=> 1.30', '=> 1.50', '=> 1.80', '=> 2.20', '=> 6.00']:
    if needle not in xp: fail(f'Missing XP multiplier {needle}')

# Ensure all former 90-table assertions were migrated to the additive 140-table catalog.
for rel in [
    'backend-laravel/tests/Feature/V128StoreGameplayNavTest.php',
    'backend-laravel/tests/Feature/V132TarneebEngineAndLuxuryFixesTest.php',
    'backend-laravel/tests/Feature/V134CriticalFixesTest.php',
    'backend-laravel/tests/Feature/V172BrandTableCatalogTest.php',
]:
    body = text(rel)
    if 'assertCount(90,' in body: fail(f'Legacy table count remains in {rel}')
    if 'assertCount(140,' not in body: fail(f'140-table contract missing in {rel}')

# Lightweight Dart source integrity checks for common accidental patch failures.
if "'${notice.body}\\nاضغط" not in main: fail('Actionable notification text is malformed')
if main.count('{') != main.count('}'): fail('main.dart braces are unbalanced')
if main.count('(') != main.count(')'): fail('main.dart parentheses are unbalanced')

print('[PASS] V174 direct invite, stable orientation, social login, authoritative per-round XP, and updated regression contracts')
