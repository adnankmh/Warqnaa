#!/usr/bin/env python3
"""Warqnaa V0.3.1 build 182 lucky-wheel, rewards, admin and gameplay regression contract."""
from __future__ import annotations
import json
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]

def fail(message:str)->None:
    raise SystemExit('[FAIL] '+message)

def text(rel:str,*needles:str)->str:
    p=ROOT/rel
    if not p.is_file(): fail('missing '+rel)
    data=p.read_text(encoding='utf-8')
    for needle in needles:
        if needle not in data: fail(f'missing {needle!r} in {rel}')
    return data

def main()->None:
    meta=json.loads((ROOT/'RELEASE_VERSION.json').read_text(encoding='utf-8'))
    if int(meta.get('build',0)) < 182: fail('release must include build 182 rewards contracts')
    text('flutter_app/pubspec.yaml',f"version: {meta.get('full')}")
    main_dart=text('flutter_app/lib/main.dart',
        "part 'v182_rewards.dart';",
        'LuckyWheelHomeCardV182(controller: controller)',
        "brightness: isLight ? Brightness.light : Brightness.dark",
        'adminRevenueTokensV182 += BigInt.from(price)',
        "clubImageEmojiV182 = '🛡️'",
        "awardLocalPrizeBoxV02(gameId, won: true, mode: prizeMode)",
        "awardLocalPrizeBoxV02('tarneeb', won: false",
        'recordInactivityEjectionV182',
        'resetGameExitSessionV182',
        'انقطع الاتصال لثلاث لفات',
        'engine.playerNames[1]',
        "Positioned(right: 3",
    )
    away_slice=main_dart[main_dart.index('void setAwayMode(bool value)'):main_dart.index('bool joinCompetition',main_dart.index('void setAwayMode(bool value)'))]
    if 'vipDays' in away_slice or 'isPrimaryAdmin' in away_slice:
        fail('persistent away mode must not require Pasha/VIP/admin status')
    if main_dart.count('await widget.controller.recordInactivityEjectionV182();') < 3:
        fail('inactivity and network-disconnect ejection paths are incomplete')
    rewards=text('flutter_app/lib/v182_rewards.dart',
        'luckyWheelSegmentsV182',
        'Duration(seconds:4)',
        'spinLuckyWheelV182',
        "'ticket_500'",
        "'pasha_day'",
        'exitActiveGameV182',
        'syncDesignerOfflineEntitiesV182',
        'updateClubIdentityV182',
        'ClubManagementPageV182',
    )
    if rewards.count("'key':") < 10: fail('wheel must contain at least ten segments')
    text('flutter_app/lib/v166_polish.dart','الخروج من اللعبة','exitActiveGameV182','BoxFit.contain')
    designer=text('flutter_app/lib/v173_global.dart',
        'controller.isPrimaryAdmin',
        'ensureDesignerOfflineSeedV182',
        'upsertDesignerOfflineEntityV182',
        'ClubManagementPageV182',
        'فتح الإدارة الكاملة والمزامنة',
        "('دولاب الحظ', 'lucky_wheel', Icons.casino_rounded)",
    )
    if 'onPressed: controller.serverConnected' in designer[designer.index('class UniversalDesignerV173'):designer.index('class DesignerEntityManagerV173')]:
        fail('designer full-management button still depends on server connection')
    text('flutter_app/lib/v02_release.dart',
        "boxKey = 'diamond_phoenix'",
        "boxKey = 'royal_amethyst'",
        "['emerald_eagle','bronze_dragon']",
        "['crimson_lion','obsidian']",
        "legendaryBox ? '500' : '200'",
    )
    text('backend-laravel/app/Services/WarqnaPro/LuckyWheelService.php',
        'MAX_TOKEN_SPINS_PER_DAY = 5',
        'weightedSegment',
        'User::whereKey($user->id)->lockForUpdate()',
        'creditPrimaryAdminRevenue',
        "'ticket_500'",
    )
    text('backend-laravel/app/Services/WarqnaPro/PrizeBoxService.php',
        'awardForCompletedGame',
        "$tier = 'legendary';",
        "'diamond_phoenix'",
        "'royal_amethyst'",
        "'competition_complete'",
    )
    game=text('backend-laravel/app/Http/Controllers/MobileGameController.php',
        "'away_mode'",
        "'missed_turns'",
        'disconnected_replacements',
        'manual_exit_counts',
    )
    if '< 3' not in game and '>= 3' not in game: fail('three-turn inactivity threshold missing')
    room_controller=text('backend-laravel/app/Http/Controllers/RoomController.php',
        "['play_direction'] = 'counterclockwise'", 
        "['next_player_side'] = 'right'",
        'manual_exit_counts',
        'disconnected_replacements',
        'الانقطاع المؤقت لا يُحسب خروجًا يدويًا',
    )
    index_slice=room_controller[room_controller.index('public function index'):room_controller.index('public function store')]
    if "where('connected',true)" in index_slice or "where('connected', true)" in index_slice:
        fail('open-room index still hides rooms without a currently connected player')
    join_slice=room_controller[room_controller.index('public function join'):room_controller.index('public function addBot')]
    if "($displaced['returns'] ?? 0) < 3" in join_slice:
        fail('temporary disconnect returns are incorrectly capped at three')
    presence_slice=room_controller[room_controller.index('public function presence'):room_controller.index('public function invite')]
    if 'closeIfNoRealPlayers' in presence_slice:
        fail('temporary page/network disconnect still closes the room')
    text('backend-laravel/app/Services/Wallet/WalletService.php','creditPrimaryAdminRevenue')
    store=text('backend-laravel/app/Http/Controllers/StoreController.php','creditPrimaryAdminRevenue','DB::transaction','lockForUpdate')
    if store.index('creditPrimaryAdminRevenue') < store.index('DB::transaction'):
        fail('store revenue transfer must occur inside the atomic purchase transaction')
    text('backend-laravel/app/Http/Controllers/AdminController.php','guardPrimaryDesigner','Adnan','saveDesignerEntity','deleteDesignerEntity')
    text('backend-laravel/database/seeders/DatabaseSeeder.php',"'level'=>99")
    text('backend-laravel/database/migrations/2026_07_15_000182_lucky_wheel_rewards.php','lucky_wheel_spins','level',"'red'")
    routes=text('backend-laravel/routes/api.php',"/rewards/lucky-wheel","/rewards/lucky-wheel/spin")
    # No black top-hat glyph may remain in executable/catalog source.
    for base in (ROOT/'flutter_app/lib',ROOT/'backend-laravel/app',ROOT/'backend-laravel/resources',ROOT/'backend-laravel/public/assets'):
        for p in base.rglob('*'):
            if p.is_file() and p.suffix.lower() in {'.dart','.php','.json','.js','.css','.html'}:
                try: data=p.read_text(encoding='utf-8')
                except UnicodeDecodeError: continue
                if '🎩' in data: fail('black top-hat icon remains in '+str(p.relative_to(ROOT)))
    report=json.loads((ROOT/'docs/IMAGE_OPTIMIZATION_V0.3.1.json').read_text(encoding='utf-8'))
    if report.get('saved_bytes',0) < 20_000_000: fail('image optimization did not materially reduce bundle size')
    print('[PASS] V182 lucky wheel, outcome boxes, persistent away mode, admin revenue, clubs, designer sync, right-side turn order and image optimization contract')

if __name__=='__main__': main()
