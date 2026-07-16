#!/usr/bin/env python3
"""Warqnaa V0.3.3 build 184 responsive web, store, boosters and engines contract."""
from __future__ import annotations
import json
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def fail(msg:str): raise SystemExit('[FAIL] '+msg)
def text(rel:str,*needles:str)->str:
 p=ROOT/rel
 if not p.is_file(): fail('missing '+rel)
 data=p.read_text(encoding='utf-8')
 for needle in needles:
  if needle not in data: fail(f'missing {needle!r} in {rel}')
 return data

def main():
 meta=json.loads((ROOT/'RELEASE_VERSION.json').read_text())
 if meta.get('full')!='0.3.3+184': fail('release must be 0.3.3+184')
 text('flutter_app/pubspec.yaml','version: 0.3.3+184','assets/images/boosters/v183/')
 overhaul=text('flutter_app/lib/v183_overhaul.dart','DesktopShellNavigationV183','AdaptiveTablePreviewV183','CompetitionTicketPreviewV183','BoosterPreviewV183','DesignerQuickControlsV183','HomeDashboardV183','raisedStorePriceV183')
 main=text('flutter_app/lib/main.dart',"part 'v183_overhaul.dart';",'isDesktopWebV183(MediaQuery.sizeOf(context).width)','table_reference_','BoosterPreviewV183','CompetitionTicketPreviewV183')
 if "collection: 'reference_1'" in main or "collection: 'reference_2'" in main:
  # Data may remain for compatibility, but filters must hide all rows and tabs.
  for required in ["!product.id.startsWith('table_reference_')", "product.id.startsWith('table_reference_')"]:
   if required not in main: fail('reference tables are not hidden from customer UI')
 prices=[('yellow',1.25,4000,7),('green',1.5,8000,8),('red',2.0,15000,9),('blue',2.5,25000,10),('black',3.0,40000,11),('silver',4.0,60000,12),('gold',5.0,100000,14)]
 for color,mult,price,days in prices:
  asset=ROOT/f'flutter_app/assets/images/boosters/v183/booster_{color}.webp'
  if not asset.is_file() or asset.stat().st_size>150_000: fail(f'booster image invalid or too large: {color}')
  for needle in [f'booster_{color}_v183',f'price: {price}',f'multiplier: {mult}']:
   if needle not in main: fail(f'missing {color} booster contract: {needle}')
  if f"'booster_{color}_v183' => {days}" not in overhaul:
   fail(f'missing {color} booster validity contract: {days} days')
 sounds=list((ROOT/'flutter_app/assets/sounds').glob('*'))
 if len(sounds)<55: fail('expanded audio cue library is missing')
 designer=text('flutter_app/lib/v173_global.dart',"'xp_booster'","'emoji_pack'","'audio'","'preview_layout'","'game_rules'")
 catalog=text('backend-laravel/app/Services/WarqnaPro/StoreCatalogService.php','v183Price','table_reference_%','booster_yellow_v183','booster_gold_v183',"'image_asset'=>'assets/images/boosters/v183/booster_'.$color.'.webp'")
 rules=text('backend-laravel/app/Services/GameEngine/GlobalCardEngineRules.php','meld_many','pass_trix','playerCountFor(string $key, int $requested)','trix_board')
 core=text('backend-laravel/app/Services/GameEngine/GlobalEngines/GlobalCardEngineCore.php','tarneeb400MinimumBid','tarneeb400BidPoints','scoreBanakilRound','starterDiscardPending','playTrixCard')
 local=text('flutter_app/lib/engines/local_game_engine.dart','_playTrixCard','_completeTrixContract','meld_many','layoff','requestedPlayerCount.clamp(2, 5)','_tarneeb400MinimumBidLocal','_tarneeb400BidPointsLocal','_tarneeb400Bids')
 print('[PASS] V183 responsive web, adaptive previews, store economy, boosters, designer, media and game-engine contracts')
if __name__=='__main__': main()
