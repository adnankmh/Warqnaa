<?php
namespace App\Http\Controllers;

use App\Models\{GameRating,ThrowableEvent,DailyRewardClaim,ClubWar,PurchaseReceipt,SystemMetric};
use Illuminate\Support\Facades\Schema;

class ProAdminController
{
 private function guard(){ abort_unless(auth()->user()?->is_admin,403); }
 public function dashboard()
 {
  $this->guard();
  return response()->json([
   'ok'=>true,
   'v118'=>[
    'ratings'=>Schema::hasTable('game_ratings') ? GameRating::count() : 0,
    'throwables'=>Schema::hasTable('throwable_events') ? ThrowableEvent::count() : 0,
    'daily_rewards'=>Schema::hasTable('daily_reward_claims') ? DailyRewardClaim::count() : 0,
    'club_wars'=>Schema::hasTable('club_wars') ? ClubWar::count() : 0,
    'purchase_receipts'=>Schema::hasTable('purchase_receipts') ? PurchaseReceipt::count() : 0,
    'anti_cheat_flags'=>Schema::hasTable('system_metrics') ? SystemMetric::where('key','like','anti_cheat_%')->count() : 0,
   ],
   'games'=>config('warqna_games_matrix.supported'),
   'languages'=>config('warqna_languages'),
   'themes'=>config('warqna_design.themes'),
  ]);
 }
}
