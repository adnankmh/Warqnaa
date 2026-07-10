<?php
namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController
{
    public function index()
    {
        $notifications=auth()->user()->notifications()->latest()->paginate(30);
        return view('notifications.index',compact('notifications'));
    }

    public function counts()
    {
        $user = auth()->user();
        return response()->json([
            'club' => $user->notifications()->where('read',false)->where('type','like','%club%')->count(),
            'game' => $user->notifications()->where('read',false)->whereIn('type',['room_invite','game_invite'])->count(),
            'competition' => $user->notifications()->where('read',false)->where('type','like','%tournament%')->count(),
            'message' => $user->notifications()->where('read',false)->whereIn('type',['private_message','friend_request'])->count(),
            'total' => $user->notifications()->where('read',false)->count(),
        ]);
    }

    public function readAll()
    {
        auth()->user()->notifications()->update(['read'=>true]);
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم تعليم كل الإشعارات كمقروءة']);
        return back()->with('ok','تم تعليم كل الإشعارات كمقروءة');
    }

    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read'=>true]);
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم تعليم الإشعار كمقروء']);
        return back()->with('ok','تم تعليم الإشعار كمقروء');
    }

    public function delete(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->delete();
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم حذف الإشعار']);
        return back()->with('ok','تم حذف الإشعار');
    }

}
