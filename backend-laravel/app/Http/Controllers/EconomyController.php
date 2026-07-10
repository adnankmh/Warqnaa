<?php
namespace App\Http\Controllers;

use App\Models\{EconomySeason,StoreOffer,RareCollectible,StoreItem};

class EconomyController
{
 public function index()
 {
  $season=EconomySeason::where('active',true)->latest()->first();
  $offers=StoreOffer::where('active',true)->latest()->get();
  $rares=RareCollectible::where('active',true)->latest()->get();
  $featured=StoreItem::where('active',true)->whereIn('category',['table','card_back','effect','xp_booster','emoji_pack'])->latest()->limit(16)->get();
  return view('economy.index',compact('season','offers','rares','featured'));
 }
}
