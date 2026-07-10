<?php
namespace App\Http\Controllers;
use App\Models\{User,Friendship,Message,Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController {
 private function areFriends(User $user): bool {
  return Friendship::where('status','accepted')->where(function($q) use($user){
   $q->where(function($q) use($user){$q->where('requester_id',auth()->id())->where('addressee_id',$user->id);})
     ->orWhere(function($q) use($user){$q->where('requester_id',$user->id)->where('addressee_id',auth()->id());});
  })->exists();
 }
 public function privateMessage(User $user, Request $r){
  abort_if($user->id===auth()->id(),422);
  abort_unless($this->areFriends($user),403,'لا يمكن إرسال رسائل خاصة إلا للأصدقاء. دردشة الغرفة متاحة فقط داخل اللعبة.');
  $data=$r->validate(['body'=>'required|string|max:1000']);
  $msg=Message::create(['sender_id'=>auth()->id(),'receiver_id'=>$user->id,'body'=>$this->cleanChat($data['body'])]);
  Notification::create(['user_id'=>$user->id,'type'=>'private_message','title'=>['ar'=>'رسالة جديدة','en'=>'New message'],'body'=>['ar'=>auth()->user()->username.' أرسل لك رسالة'],'url'=>route('friends')]);
  if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>['id'=>$msg->id,'mine'=>true,'name'=>auth()->user()->username,'body'=>$msg->body,'color'=>auth()->user()->profile?->chat_color ?: '#fff','time'=>$msg->created_at?->format('H:i')]]);
  return back()->with('ok','تم إرسال الرسالة');
 }
 public function friends(){
  $ids=Friendship::where('status','accepted')->where(function($q){$q->where('requester_id',auth()->id())->orWhere('addressee_id',auth()->id());})->get()->map(fn($f)=>$f->requester_id==auth()->id()?$f->addressee_id:$f->requester_id)->values();
  $users=User::with('profile')->whereIn('id',$ids)->orderBy('username')->get()->map(fn($u)=>$this->userPayload($u));
  return response()->json(['ok'=>true,'friends'=>$users]);
 }
 public function search(Request $r){
  $q=trim((string)$r->query('q',''));
  $users=User::with('profile')->when($q,fn($query)=>$query->where('username','like','%'.$q.'%'))->where('id','!=',auth()->id())->limit(20)->get()->map(fn($u)=>$this->userPayload($u));
  return response()->json(['ok'=>true,'users'=>$users]);
 }
 public function thread(User $user){
  abort_unless($this->areFriends($user),403,'الرسائل الخاصة للأصدقاء فقط');
  $msgs=Message::with('sender.profile')->where(function($q) use($user){
   $q->where('sender_id',auth()->id())->where('receiver_id',$user->id);
  })->orWhere(function($q) use($user){
   $q->where('sender_id',$user->id)->where('receiver_id',auth()->id());
  })->latest()->limit(50)->get()->reverse()->values()->map(function($m){return ['id'=>$m->id,'mine'=>$m->sender_id==auth()->id(),'name'=>$m->sender?->username,'body'=>$m->body,'color'=>$m->sender?->profile?->chat_color ?: ($m->sender?->profile?->text_color ?: '#fff'),'time'=>$m->created_at?->format('H:i')];});
  Message::where('sender_id',$user->id)->where('receiver_id',auth()->id())->whereNull('read_at')->update(['read_at'=>now()]);
  return response()->json(['ok'=>true,'friend'=>$this->userPayload($user->load('profile')),'messages'=>$msgs]);
 }
 public function send(User $user, Request $r){
  abort_if($user->id===auth()->id(),422);
  abort_unless($this->areFriends($user),403,'لا يمكن إرسال رسائل خاصة إلا للأصدقاء.');
  $data=$r->validate(['body'=>'required|string|max:1000']);
  $msg=Message::create(['sender_id'=>auth()->id(),'receiver_id'=>$user->id,'body'=>$this->cleanChat($data['body'])]);
  Notification::create(['user_id'=>$user->id,'type'=>'private_message','title'=>['ar'=>'رسالة جديدة','en'=>'New message'],'body'=>['ar'=>auth()->user()->username.' أرسل لك رسالة'],'url'=>route('friends')]);
  return response()->json(['ok'=>true,'message'=>['id'=>$msg->id,'mine'=>true,'name'=>auth()->user()->username,'body'=>$msg->body,'color'=>auth()->user()->profile?->chat_color ?: '#fff','time'=>$msg->created_at?->format('H:i')]]);
 }
 private function cleanChat(string $body): string{ $bad=['كلب','حمار','حقير','قذر','تافه','لعنة','غبي','وسخ','اهبل','زبالة','خرا','شرموط','قحبة','كس','نيك','يلعن','fuck','shit','bitch','asshole']; $body=trim(strip_tags($body)); foreach($bad as $w) $body=preg_replace('/'.preg_quote($w,'/').'/iu','***',$body); return mb_substr($body,0,1000); }
 private function userPayload(User $u): array { $p=$u->profile; return ['id'=>$u->id,'username'=>$u->username,'avatar'=>$p?->avatar ?: '/assets/avatars/default.svg','level'=>$p?->level ?? 1,'country'=>($p?->country_name ?: strtoupper($p?->country_code ?? 'PS')),'flag_url'=>flag_url($p?->country_code ?? 'PS'),'flag'=>country_name($p?->country_code ?? 'PS'),'color'=>$p?->name_color ?: '#facc15']; }
}
