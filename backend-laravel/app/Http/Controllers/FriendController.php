<?php
namespace App\Http\Controllers;

use App\Models\{User,Friendship,Notification};
use Illuminate\Http\Request;

class FriendController
{
    private function relationWith(User $user): ?Friendship
    {
        return Friendship::where(function($q) use($user){
            $q->where('requester_id',auth()->id())->where('addressee_id',$user->id);
        })->orWhere(function($q) use($user){
            $q->where('requester_id',$user->id)->where('addressee_id',auth()->id());
        })->latest()->first();
    }

    public function index()
    {
        $friendships=Friendship::with(['requester.profile','addressee.profile'])->where(function($q){
            $q->where('requester_id',auth()->id())->orWhere('addressee_id',auth()->id());
        })->latest()->get();
        $incoming=$friendships->where('addressee_id',auth()->id())->where('status','pending');
        $outgoing=$friendships->where('requester_id',auth()->id())->where('status','pending');
        $accepted=$friendships->where('status','accepted');
        $blocked=$friendships->where('status','blocked');
        return view('friends.index',compact('incoming','outgoing','accepted','blocked'));
    }

    public function request(User $user)
    {
        abort_if($user->id===auth()->id(),422,'لا يمكنك إرسال طلب لنفسك.');
        $existing=$this->relationWith($user);
        if($existing){
            $msg = match($existing->status){
                'accepted'=>'هذا اللاعب موجود في قائمة أصدقائك بالفعل.',
                'pending'=>'تم إرسال الطلب مسبقًا، بانتظار موافقة اللاعب الآخر.',
                'blocked'=>'لا يمكن إرسال طلب صداقة بسبب الحظر.',
                default=>'يوجد طلب سابق لهذا اللاعب.'
            };
            if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>$existing->status!=='blocked','message'=>$msg,'status'=>$existing->status]);
            return back()->with($existing->status==='blocked'?'error':'ok',$msg);
        }
        $f=Friendship::create(['requester_id'=>auth()->id(),'addressee_id'=>$user->id,'status'=>'pending']);
        Notification::create(['user_id'=>$user->id,'type'=>'friend_request','title'=>['ar'=>'طلب صداقة','en'=>'Friend request'],'body'=>['ar'=>auth()->user()->username.' أرسل لك طلب صداقة'],'url'=>route('friends'),'meta'=>['friendship_id'=>$f->id,'from'=>auth()->id()]]);
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم إرسال طلب الصداقة','status'=>'pending']);
        return back()->with('ok','تم إرسال طلب الصداقة');
    }

    public function respond(Friendship $friendship, Request $r)
    {
        abort_unless($friendship->addressee_id===auth()->id(),403);
        $status=$r->input('status','accepted');
        abort_unless(in_array($status,['accepted','rejected'],true),422);
        if($status==='accepted') $friendship->update(['status'=>'accepted']);
        else { $friendship->delete(); }
        Notification::create(['user_id'=>$friendship->requester_id,'type'=>'friend_response','title'=>['ar'=>'رد على طلب الصداقة'],'body'=>['ar'=>auth()->user()->username.' '.($status==='accepted'?'قبل طلب الصداقة':'رفض طلب الصداقة')],'url'=>route('friends')]);
        if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>$status==='accepted'?'تم قبول طلب الصداقة':'تم رفض طلب الصداقة','status'=>$status]);
        return back()->with('ok','تم تحديث الطلب');
    }

    public function cancel(Friendship $friendship, Request $r)
    {
        abort_unless($friendship->requester_id===auth()->id() && $friendship->status==='pending',403);
        $friendship->delete();
        if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>'تم إلغاء طلب الصداقة','status'=>'cancelled']);
        return back()->with('ok','تم إلغاء طلب الصداقة');
    }

    public function unblock(User $user)
    {
        $existing=$this->relationWith($user);
        abort_unless($existing && $existing->status==='blocked' && $existing->requester_id===auth()->id(),403);
        $existing->delete();
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم إلغاء الحظر','status'=>'unblocked']);
        return back()->with('ok','تم إلغاء الحظر');
    }

    public function block(User $user)
    {
        abort_if($user->id===auth()->id(),422);
        $existing=$this->relationWith($user);
        if($existing) $existing->update(['status'=>'blocked','requester_id'=>auth()->id(),'addressee_id'=>$user->id]);
        else Friendship::create(['requester_id'=>auth()->id(),'addressee_id'=>$user->id,'status'=>'blocked']);
        if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم حظر اللاعب','status'=>'blocked']);
        return back()->with('ok','تم حظر اللاعب');
    }
}
