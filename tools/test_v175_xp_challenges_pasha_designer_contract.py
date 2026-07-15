#!/usr/bin/env python3
from pathlib import Path
import json,re
ROOT=Path(__file__).resolve().parents[1]
def fail(m): print('[FAIL] '+m); raise SystemExit(1)
def req(rel,*needles):
    p=ROOT/rel
    if not p.is_file(): fail('Missing '+rel)
    t=p.read_text(encoding='utf-8')
    for n in needles:
        if n not in t: fail(f'Missing {n} in {rel}')
    return t
def main():
    meta=json.loads((ROOT/'RELEASE_VERSION.json').read_text())
    if int(meta.get('build', 0)) < 175: fail('metadata build is older than v175')
    dart=req('flutter_app/lib/v175_release.dart','const Map<int, int> xpRequirementsV175','1: 80','100: 8000000','class ChallengeCenterV175')
    entries=re.findall(r'^\s*(\d+):\s*(\d+),?$',dart,re.M)
    if len(entries)!=100 or len({int(k) for k,v in entries})!=100: fail('Dart XP table must contain 100 unique levels')
    main=req('flutter_app/lib/main.dart',"if (p.category == 'pasha_style') return false;",'_loginOrCreateLocalFallback','queueNavigationRoute','openPendingNavigationRoute')
    if "('pasha_style', 'ألوان الطربوش')" in main: fail('Pasha color tab still visible')
    v170=req('flutter_app/lib/v170_global.dart',"Image.asset('assets/images/pasha.png'",'final isPasha',"label:Text(owned?'تفعيل الباشا':'شراء الباشا')")
    req('flutter_app/lib/v173_global.dart',"Image.asset('assets/images/pasha.png'",'المصمم الشامل V0.3.2','level_xp','feature_flag','showChallengesV175')
    req('backend-laravel/app/Services/WarqnaPro/ChallengeService.php','function activate','function record','function claim')
    req('backend-laravel/app/Services/WarqnaPro/DailyPackService.php','public static function catalog','legendary','2500','5000')
    req('backend-laravel/routes/api.php',"/challenges/{challengeKey}/activate","/challenges/{challengeKey}/claim")
    if not (ROOT/'docs/reference/XPs_levels_1_to_100_source.xlsx').is_file(): fail('Missing XP source workbook')
    print('[PASS] v175 exact XP, challenges, packs, full Pasha, web fallback login and universal designer contract')
if __name__=='__main__': main()
