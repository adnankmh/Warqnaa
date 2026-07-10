<?php
namespace App\Http\Controllers;

use App\Models\{Club,ClubMember,ClubJoinRequest,Notification};
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use RuntimeException;

class ClubController
{
    private array $clubCaps=[1=>20,2=>30,3=>40,4=>50,5=>70,6=>100];
    private array $clubLeagues=[1=>'برونزي',2=>'فضي',3=>'ذهبي',4=>'بلاتيني',5=>'ماسي',6=>'أسطوري'];

    private function cap(Club $club): int { return $this->clubCaps[min(6,max(1,(int)$club->level))] ?? 20; }
    private function member(Club $club): ?ClubMember { return $club->members()->where('user_id',auth()->id())->first(); }
    private function can(Club $club, string $permission): bool
    {
        if($club->owner_id===auth()->id()) return true;
        $m=$this->member($club);
        if(!$m || $m->role!=='moderator') return false;
        $perms=$m->permissions ?: [];
        return !empty($perms['all']) || !empty($perms[$permission]);
    }

    public function index()
    {
        return view('clubs.index',[
            'clubs'=>Club::with('owner.profile','members.user.profile','joinRequests.user.profile')->latest()->get(),
            'clubCaps'=>$this->clubCaps,
            'clubLeagues'=>$this->clubLeagues,
        ]);
    }

    public function show(Club $club)
    {
        $club->load('owner.profile','members.user.profile','joinRequests.user.profile');
        return view('clubs.show',[
            'club'=>$club,
            'clubCaps'=>$this->clubCaps,
            'clubLeagues'=>$this->clubLeagues,
            'canManage'=>$this->can($club,'manage_members'),
            'canAccept'=>$this->can($club,'accept_members'),
            'canKick'=>$this->can($club,'kick_members'),
            'canTournament'=>$this->can($club,'create_tournaments'),
        ]);
    }

    public function store(Request $r, WalletService $wallet)
    {
        abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0 || auth()->user()->is_admin,403,'إنشاء المجموعات ميزة لأعضاء الباشا فقط، ويجب توفر التوكنز الكافية.');
        abort_if(ClubMember::where('user_id',auth()->id())->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل إنشاء نادي جديد.');
        $data=$r->validate(['name'=>'required|string|max:120|unique:clubs,name']);
        try { $wallet->debit(auth()->user(),5000,'club_create',['name'=>$data['name']]); }
        catch(RuntimeException $e){ return back()->withErrors(['msg'=>'لا تملك توكنز كافية لإنشاء النادي.']); }
        $club=Club::create(['owner_id'=>auth()->id(),'name'=>$data['name'],'level'=>1,'treasury'=>0,'capacity'=>20,'league_tier'=>'bronze']);
        ClubMember::create(['club_id'=>$club->id,'user_id'=>auth()->id(),'role'=>'owner','permissions'=>['all'=>true]]);
        return redirect()->route('clubs.show',$club)->with('ok','تم إنشاء النادي');
    }

    public function requestJoin(Club $club)
    {
        abort_if(ClubMember::where('user_id',auth()->id())->where('club_id','!=',$club->id)->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل الانضمام إلى نادي جديد.');
        abort_if(ClubJoinRequest::where('user_id',auth()->id())->where('club_id','!=',$club->id)->where('status','pending')->exists(),403,'لديك طلب انضمام معلق لنادي آخر. ألغِ أو انتظر الرد قبل إرسال طلب جديد.');
        abort_if($club->members()->where('user_id',auth()->id())->exists(),409,'أنت عضو في النادي بالفعل');
        abort_if($club->members()->count() >= $this->cap($club),422,'النادي ممتلئ حسب مستوى النادي الحالي');
        ClubJoinRequest::firstOrCreate(['club_id'=>$club->id,'user_id'=>auth()->id()],['status'=>'pending']);
        Notification::create(['user_id'=>$club->owner_id,'type'=>'club_join','title'=>['ar'=>'طلب انضمام لنادي'],'body'=>['ar'=>auth()->user()->username.' طلب الانضمام إلى '.$club->name],'url'=>route('clubs.show',$club)]);
        return back()->with('ok','تم إرسال طلب الانضمام');
    }

    public function leave(Club $club)
    {
        $member=$club->members()->where('user_id',auth()->id())->first();
        abort_unless($member,404,'أنت لست عضوًا في هذا النادي');
        abort_if($member->role==='owner' && $club->members()->count()>1,422,'لا يمكن للمالك مغادرة النادي قبل حذف النادي أو نقل الملكية.');
        if($member->role==='owner' && $club->members()->count()===1){ $club->delete(); return redirect()->route('clubs')->with('ok','تم حذف النادي لأنه لم يعد يحتوي أعضاء.'); }
        $member->delete();
        $club->joinRequests()->where('user_id',auth()->id())->delete();
        return redirect()->route('clubs')->with('ok','تمت مغادرة النادي بنجاح');
    }

    public function delete(Club $club)
    {
        abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403,'الحذف للمالك أو الإدارة فقط');
        $club->delete();
        return redirect()->route('clubs')->with('ok','تم حذف النادي نهائيًا');
    }

    public function memberAction(Club $club, ClubMember $member, Request $r)
    {
        abort_unless($member->club_id===$club->id,404);
        $action=$r->input('action');
        if($action==='kick'){
            abort_unless($this->can($club,'kick_members'),403);
            abort_if($member->role==='owner',422,'لا يمكن طرد مالك النادي');
            $member->delete();
            return back()->with('ok','تم طرد اللاعب من النادي');
        }
        if($action==='moderator'){
            abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403);
            abort_if($member->role==='owner',422,'المالك لديه كل الصلاحيات أصلًا');
            $perms=[
                'accept_members'=>$r->boolean('accept_members'),
                'kick_members'=>$r->boolean('kick_members'),
                'create_tournaments'=>$r->boolean('create_tournaments'),
                'manage_chat'=>$r->boolean('manage_chat'),
            ];
            $member->update(['role'=>'moderator','permissions'=>$perms]);
            return back()->with('ok','تم تعيين المشرف وتحديد صلاحياته');
        }
        if($action==='member'){
            abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403);
            abort_if($member->role==='owner',422,'لا يمكن تعديل المالك');
            $member->update(['role'=>'member','permissions'=>[]]);
            return back()->with('ok','تم إرجاع اللاعب عضوًا عاديًا');
        }
        abort(422,'إجراء غير معروف');
    }

    public function respond(ClubJoinRequest $request, Request $r)
    {
        $club=$request->club;
        abort_unless($this->can($club,'accept_members'),403);
        $status=$r->input('status','accepted');
        $request->update(['status'=>$status]);
        if($status==='accepted'){
            abort_if($club->members()->count() >= $this->cap($club),422,'النادي ممتلئ حسب مستوى النادي الحالي');
            ClubMember::firstOrCreate(['club_id'=>$club->id,'user_id'=>$request->user_id],['role'=>'member','permissions'=>[]]);
        }
        return back()->with('ok','تم تحديث طلب الانضمام');
    }
}
