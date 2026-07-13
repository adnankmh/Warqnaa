#!/usr/bin/env python3
"""Inherited offline, orientation, direct-room and visible-XP contract for v174+."""
from __future__ import annotations
import json
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def fail(message:str)->None:
    print(f"[FAIL] {message}"); raise SystemExit(1)
def require(rel:str,needles:list[str])->str:
    path=ROOT/rel
    if not path.is_file(): fail(f"Missing file: {rel}")
    text=path.read_text(encoding="utf-8")
    for needle in needles:
        if needle not in text: fail(f"Missing inherited v174+ contract in {rel}: {needle}")
    return text
def forbid(rel:str,needles:list[str])->None:
    text=(ROOT/rel).read_text(encoding="utf-8")
    for needle in needles:
        if needle in text: fail(f"Forbidden regression in {rel}: {needle}")
def main()->None:
    meta=json.loads((ROOT/'RELEASE_VERSION.json').read_text(encoding='utf-8'))
    if int(meta.get('build',0)) < 174: fail('Release build must be 174 or newer')
    main_dart=require('flutter_app/lib/main.dart',[
        'final GlobalKey<NavigatorState> warqnaNavigatorKey','Future<String?> _loginLocal',
        'Future<String?> _registerLocal','Future<String?> registerOffline','_loginOrCreateLocalFallback',
        'offlineLoggedIn','Future<String?> loginWithSocialProvider','Future<void> _applyPreferredOrientationV174',
        'SystemChrome.setPreferredOrientations','DeviceOrientation.portraitUp','class RoundXpBannerV174',
        'applyServerRoundProgressV174','progression_popup',"player['bot'] == true",'queueNavigationRoute',
        'openPendingNavigationRoute','_prepareDirectInviteTransfer','await api.leaveGame(previousCode)',
        'await openGameRoom(navigationContext, controller, game, options: options);','xpRequirementsV175',
    ])
    if 'controller.isAuthenticated && !controller.serverConnected' in main_dart: fail('Home is blocked by an online-only gate')
    forbid('flutter_app/lib/main.dart',['التسجيل المحلي غير متاح في Warqna V173','await controller.setLandscapeMode(true);'])
    require('flutter_app/lib/v175_release.dart',['1: 80','40: 59371','80: 1000000','100: 8000000','showChallengesV175'])
    require('backend-laravel/config/warqna_xp_levels.php',['1 => 80','40 => 59371','80 => 1000000','100 => 8000000'])
    require('backend-laravel/app/Services/Leveling/XpService.php',["config('warqna_xp_levels.'.$safe)","config('warqna_xp_levels.100', 8000000)"])
    require('backend-laravel/app/Services/Progression/ProgressionService.php',["'profile'=>$this->profileSnapshot($user)","'xp_next'=>$this->xpService->requiredXp($level)"])
    require('backend-laravel/app/Http/Controllers/MobileGameController.php',['progression_popup',"'player_key'=>$key", "if ($player->is_bot || !$player->user) continue;"])
    require('backend-laravel/app/Models/DailyPackClaim.php',['protected function claimDate(): Attribute','Carbon::parse($value)->toDateString()'])
    for rel in ['backend-laravel/tests/Feature/V128StoreGameplayNavTest.php','backend-laravel/tests/Feature/V132TarneebEngineAndLuxuryFixesTest.php','backend-laravel/tests/Feature/V134CriticalFixesTest.php','backend-laravel/tests/Feature/V172BrandTableCatalogTest.php']:
        require(rel,['assertCount(140'])
    require('backend-laravel/app/Http/Controllers/MobileApiController.php',["'online_only' => false","'offline_login' => true","'offline_gameplay' => true"])
    require('backend-laravel/app/Http/Controllers/MobileEngagementController.php',["'online_only'=>false"])
    print('[PASS] inherited v174+ contract: offline access, fixed user-controlled orientation, direct room navigation, visible human-player XP, and exact XP table')
if __name__=='__main__': main()
