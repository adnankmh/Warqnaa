#!/usr/bin/env python3
"""Warqnaa V0.3 professional-release source contract."""
from __future__ import annotations
import json, re
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]

def fail(message: str) -> None:
    print('[FAIL] '+message)
    raise SystemExit(1)

def req(rel: str, *needles: str) -> str:
    p=ROOT/rel
    if not p.is_file(): fail('Missing '+rel)
    text=p.read_text(encoding='utf-8')
    for needle in needles:
        if needle not in text: fail(f'Missing {needle!r} in {rel}')
    return text

def main() -> None:
    meta=json.loads((ROOT/'RELEASE_VERSION.json').read_text(encoding='utf-8'))
    if meta.get('version')!='0.3.1' or meta.get('build')!=182 or meta.get('display_release')!='V0.3.1':
        fail('Release metadata is not Warqnaa V0.3.1 build 182')
    req('flutter_app/pubspec.yaml','version: 0.3.1+182')

    premium=req('flutter_app/lib/premium_v151.dart','const List<(String, String, Color)> v151ThemeOptions')
    theme_block=premium[premium.index('const List<(String, String, Color)> v151ThemeOptions'):premium.index('const List<String> v151AccentColors')]
    if len(re.findall(r"^\s*\('[^']+',\s*'[^']+',\s*Color\(",theme_block,re.M))!=9:
        fail('Exactly nine built-in themes are required')

    main=req('flutter_app/lib/main.dart',
        "final List<String> homeGameIds = <String>['tarneeb', 'hand', 'trix'];",
        'Future<String?> updateHomeGames',
        'if (raw.toSet().length > 4)',
        'showHomeGamesSelector',
        'coins -= BigInt.from(price);',
        'lastStoreError = e.message;',
        'if (amount < 10)',
        'final fee = (amount * .10).ceil();',
        'challengeRoadTotal = const <int>{10, 12, 15}.contains(totalStages)',
        'challengeRoadAttempts = 5',
        "bool get isPrimaryAdmin => isAdmin && username.trim().toLowerCase() == 'adnan';",
        'Web/GitHub Pages preview: keep the temporary rewarded-ad feature testable',
        '_grantLocalLevelReward',
    )
    create_room=main[main.index('void showCreateRoom'):main.index('class OpenRoomsSheet') if 'class OpenRoomsSheet' in main else len(main)]
    if "DropdownMenuItem(value: 'friends'" in create_room: fail('Friends-only option remains in create-room UI')

    pasha=req('flutter_app/lib/v173_global.dart',"(key == 'black' ? 'red' : key)")
    if "PashaStyleV173('black'" in pasha: fail('Black Pasha style remains active')

    rooms=req('backend-laravel/app/Http/Controllers/MobileGameController.php',
        "->where('visibility', 'public')",
        'Keep every public active room discoverable',
        "'avatars' =>",
    )
    if "where('connected', true)" in rooms[rooms.index('public function rooms'):rooms.index('public function join')]:
        fail('Open-room discovery still requires an open/connected game screen')

    req('backend-laravel/app/Http/Controllers/MobileAdminController.php',
        'guardPrimaryDesigner',
        "strtolower((string) $request->user()?->username) === 'adnan'",
    )
    req('backend-laravel/app/Http/Controllers/ClubController.php','ClubActivityLog','manage_club','updateSettings')
    req('backend-laravel/database/migrations/2026_07_14_000300_create_club_activity_logs.php',"Schema::create('club_activity_logs'")
    req('backend-laravel/app/Services/Leveling/XpService.php','level_rewards','pasha_days','ticket_200','prize_box')

    banakil=req('backend-laravel/app/Services/GameEngine/GlobalEngines/BanakilEngine.php',
        "'cardsEach' => 18",
        "'starterExtraCard' => true",
        "'starterMustDiscard' => true",
        "'opening' => 0",
        "'partnership' => true",
    )
    core=req('backend-laravel/app/Services/GameEngine/GlobalEngines/GlobalCardEngineCore.php',
        'removeOneCard',
        'recycleDiscard',
        'protected function layoff',
        "unset($view['deck'], $view['seed']);",
        "'dealCommitment'=>$dealCommitment",
    )
    req('backend-laravel/app/Http/Controllers/RoomController.php',"$copy['_global_engine']","$copy['plain_room_password']")

    seeder=req('backend-laravel/database/seeders/DatabaseSeeder.php',"['Kareem'","['Yazan'",'نخبة ورقنا','manage_club')
    demo_block=seeder[seeder.index('$demoUsers = ['):seeder.index('];', seeder.index('$demoUsers = ['))+2]
    expected_demo_names={'Kareem','Rami','Lina','Samar','Layla','Jameel','Nour','Omar','Sara','Basel','Hala','Yazan'}
    found_demo_names=set(re.findall(r"^\s*\['([^']+)'", demo_block, re.M))
    if found_demo_names!=expected_demo_names:
        fail(f'Expected 12 non-admin server demo users, found {sorted(found_demo_names)}')
    req('docs/DEMO_ACCOUNTS_V0.3_AR.md','Adnan123','Kareem123','Yazan12345')

    req('flutter_app/web/index.html','application/ld+json','VideoGame','og:title','twitter:card')
    req('flutter_app/web/robots.txt','Sitemap:')
    req('flutter_app/web/sitemap.xml','<urlset')
    print('[PASS] Warqnaa V0.3 store, home games, rooms, challenges, clubs, rewards, Banakil, anti-cheat, demo accounts and SEO contract')

if __name__=='__main__': main()
