<?php
namespace App\Http\Controllers;

use App\Models\{PresenceSession,Room,Message};
use Illuminate\Http\Request;

class RealtimeController
{
 public function heartbeat(Request $r)
 {
  $data=$r->validate(['scope'=>'nullable|string|max:40','room_code'=>'nullable|string|max:20','meta'=>'nullable|array']);
  $scope=$data['scope'] ?? 'site';
  PresenceSession::updateOrCreate(
   ['user_id'=>auth()->id(),'scope'=>$scope,'room_code'=>$data['room_code'] ?? null],
   ['last_seen_at'=>now(),'meta'=>$data['meta'] ?? []]
  );
  return response()->json(['ok'=>true,'online'=>$this->onlinePayload($data['room_code'] ?? null)]);
 }

 public function room(Room $room)
 {
  $online=$this->onlinePayload($room->code);
  $messages=Message::with('sender.profile')->where('room_id',$room->id)->latest()->limit(25)->get()->reverse()->values()->map(fn($m)=>[
   'id'=>$m->id,'sender_id'=>$m->sender_id,'name'=>$m->sender?->username,'avatar'=>$m->sender?->profile?->avatar ?: '/assets/avatars/default.svg',
   'body'=>$m->body,'color'=>$m->sender?->profile?->chat_color ?: '#fff','time'=>$m->created_at?->format('H:i')
  ]);
  return response()->json(['ok'=>true,'online'=>$online,'messages'=>$messages,'server_time'=>now()->toIso8601String()]);
 }

 private function onlinePayload(?string $roomCode=null): array
 {
  $q=PresenceSession::with('user.profile')->where('last_seen_at','>=',now()->subMinutes(3));
  if($roomCode) $q->where('room_code',$roomCode);
  return $q->latest('last_seen_at')->limit(50)->get()->map(fn($s)=>[
   'id'=>$s->user_id,'name'=>$s->user?->username,'avatar'=>$s->user?->profile?->avatar ?: '/assets/avatars/default.svg',
   'level'=>$s->user?->profile?->level ?? 1,'scope'=>$s->scope,'room_code'=>$s->room_code,'last_seen'=>$s->last_seen_at?->diffForHumans()
  ])->values()->all();
 }
}
