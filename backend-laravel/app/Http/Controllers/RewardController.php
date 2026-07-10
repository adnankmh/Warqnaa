<?php
namespace App\Http\Controllers;

use App\Models\{DailyRewardClaim,Wallet};
use App\Services\WarqnaPro\DailyRewardService;
use Illuminate\Http\Request;

class RewardController
{
 public function index()
 {
  $today=DailyRewardClaim::where('user_id',auth()->id())->whereDate('claim_date',today())->first();
  $claims=DailyRewardClaim::where('user_id',auth()->id())->latest()->limit(14)->get();
  return view('rewards.index',compact('today','claims'));
 }

 public function claim(DailyRewardService $svc, Request $r)
 {
  $last=DailyRewardClaim::where('user_id',auth()->id())->latest('claim_date')->first();
  $streak=$last && $last->claim_date?->isYesterday() ? $last->streak+1 : 1;
  $coins=$svc->rewardForStreak($streak);
  $claim=DailyRewardClaim::firstOrCreate(
   ['user_id'=>auth()->id(),'claim_date'=>today()],
   ['streak'=>$streak,'coins'=>$coins,'payload'=>$svc->spinPrize()]
  );
  if($claim->wasRecentlyCreated){
   $wallet=Wallet::firstOrCreate(['user_id'=>auth()->id()],['tokens'=>0,'gems'=>0]);
   $wallet->increment('tokens',$claim->coins);
  }
  if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>'تم استلام المكافأة اليومية','claim'=>$claim]);
  return back()->with('ok','تم استلام المكافأة اليومية');
 }
}
