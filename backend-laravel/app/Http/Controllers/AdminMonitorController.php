<?php
namespace App\Http\Controllers;

use App\Models\{User,Room,Message,Notification,Friendship,StoreItem,InventoryItem,SystemMetric,PresenceSession};
use Illuminate\Support\Facades\Schema;
use App\Services\Platform\PlatformHealthService;

class AdminMonitorController
{
 private function guard(){ abort_unless(auth()->user()?->is_admin,403); }

 public function snapshot(PlatformHealthService $health)
 {
  $this->guard();
  $data=[
   'users'=>User::count(),
   'online'=>Schema::hasTable('presence_sessions') ? PresenceSession::where('last_seen_at','>=',now()->subMinutes(3))->count() : 0,
   'open_rooms'=>Room::whereIn('status',['waiting','bidding','playing'])->count(),
   'messages_today'=>Message::where('created_at','>=',now()->startOfDay())->count(),
   'notifications_unread'=>Notification::where('read',false)->count(),
   'friendships'=>Friendship::where('status','accepted')->count(),
   'store_items'=>StoreItem::where('active',true)->count(),
   'active_inventory'=>InventoryItem::where('active',true)->count(),
   'health'=>$health->snapshot(),
   'time'=>now()->toIso8601String(),
  ];
  SystemMetric::create(['key'=>'admin_snapshot','value'=>json_encode($data,JSON_UNESCAPED_UNICODE),'meta'=>$data]);
  return response()->json(['ok'=>true,'data'=>$data]);
 }
}
