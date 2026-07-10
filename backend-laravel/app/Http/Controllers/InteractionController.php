<?php
namespace App\Http\Controllers;

use App\Models\{Room,User,ThrowableEvent,Wallet};
use App\Services\WarqnaPro\InteractionService;
use Illuminate\Http\Request;

class InteractionController
{
 public function throw(Room $room, User $target, Request $r, InteractionService $svc)
 {
  $data=$r->validate(['item_key'=>'required|string|max:40']);
  $cost=$svc->price($data['item_key']);
  $wallet=Wallet::firstOrCreate(['user_id'=>auth()->id()],['tokens'=>0,'gems'=>0]);
  if($cost>0 && $wallet->tokens < $cost) return response()->json(['ok'=>false,'message'=>'الرصيد غير كافٍ لهذا التفاعل'],422);
  if($cost>0) $wallet->decrement('tokens',$cost);
  $payload=$svc->payload($data['item_key'], auth()->user()->username, $target->username);
  ThrowableEvent::create(['from_user_id'=>auth()->id(),'to_user_id'=>$target->id,'room_id'=>$room->id,'item_key'=>$data['item_key'],'cost'=>$cost,'payload'=>$payload]);
  return response()->json(['ok'=>true,'message'=>'تم إرسال التفاعل','payload'=>$payload,'wallet_tokens'=>$wallet->fresh()->tokens]);
 }
}
