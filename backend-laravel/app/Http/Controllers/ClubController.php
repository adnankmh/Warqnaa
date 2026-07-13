<?php
namespace App\Http\Controllers;

use App\Models\{Club,ClubMember,ClubJoinRequest,ClubAnnouncement,Notification,Tournament};
use App\Services\Wallet\WalletService;
use App\Services\Clubs\ClubActivityService;
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
        $club->load('owner.profile','members.user.profile','joinRequests.user.profile','announcements.author.profile','tournaments.game');
        return view('clubs.show',[
            'club'=>$club,
            'clubCaps'=>$this->clubCaps,
            'clubLeagues'=>$this->clubLeagues,
            'canManage'=>$this->can($club,'manage_members'),
            'canAccept'=>$this->can($club,'accept_members'),
            'canKick'=>$this->can($club,'kick_members'),
            'canTournament'=>$this->can($club,'create_tournaments'),
            'canAnnounce'=>$this->can($club,'create_announcements'),
        ]);
    }

    public function store(Request $r, WalletService $wallet, ClubActivityService $activity)
    {
        abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0 || auth()->user()->is_admin,403,'إنشاء المجموعات ميزة لأعضاء الباشا فقط، ويجب توفر التوكنز الكافية.');
        abort_if(ClubMember::where('user_id',auth()->id())->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل إنشاء نادي جديد.');
        $data=$r->validate(['name'=>'required|string|max:120|unique:clubs,name']);
        try { $wallet->debit(auth()->user(),5000,'club_create',['name'=>$data['name']]); }
        catch(RuntimeException $e){ return back()->withErrors(['msg'=>'لا تملك توكنز كافية لإنشاء النادي.']); }
        $club=Club::create(['owner_id'=>auth()->id(),'name'=>$data['name'],'level'=>1,'treasury'=>0,'capacity'=>20,'league_tier'=>'bronze']);
        ClubMember::create(['club_id'=>$club->id,'user_id'=>auth()->id(),'role'=>'owner','permissions'=>['all'=>true]]);
        $activity->record($club, auth()->user(), 'members', 'club.created', 'تم إنشاء النادي وتعيين المالك.', ['level'=>1], auth()->user());
        return redirect()->route('clubs.show',$club)->with('ok','تم إنشاء النادي');
    }

    public function requestJoin(Club $club, ClubActivityService $activity)
    {
        abort_if(ClubMember::where('user_id',auth()->id())->where('club_id','!=',$club->id)->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل الانضمام إلى نادي جديد.');
        abort_if(ClubJoinRequest::where('user_id',auth()->id())->where('club_id','!=',$club->id)->where('status','pending')->exists(),403,'لديك طلب انضمام معلق لنادي آخر. ألغِ أو انتظر الرد قبل إرسال طلب جديد.');
        abort_if($club->members()->where('user_id',auth()->id())->exists(),409,'أنت عضو في النادي بالفعل');
        abort_if($club->members()->count() >= $this->cap($club),422,'النادي ممتلئ حسب مستوى النادي الحالي');
        ClubJoinRequest::firstOrCreate(['club_id'=>$club->id,'user_id'=>auth()->id()],['status'=>'pending']);
        $activity->record($club, auth()->user(), 'members', 'member.join.requested', 'تم إرسال طلب انضمام إلى النادي.', [], auth()->user());
        Notification::create(['user_id'=>$club->owner_id,'type'=>'club_join','title'=>['ar'=>'طلب انضمام لنادي'],'body'=>['ar'=>auth()->user()->username.' طلب الانضمام إلى '.$club->name],'url'=>route('clubs.show',$club)]);
        return back()->with('ok','تم إرسال طلب الانضمام');
    }

    public function leave(Club $club, ClubActivityService $activity)
    {
        $member=$club->members()->where('user_id',auth()->id())->first();
        abort_unless($member,404,'أنت لست عضوًا في هذا النادي');
        abort_if($member->role==='owner' && $club->members()->count()>1,422,'لا يمكن للمالك مغادرة النادي قبل حذف النادي أو نقل الملكية.');
        if($member->role==='owner' && $club->members()->count()===1){ $club->delete(); return redirect()->route('clubs')->with('ok','تم حذف النادي لأنه لم يعد يحتوي أعضاء.'); }
        $activity->record($club, auth()->user(), 'members', 'member.left', 'غادر عضو النادي.', ['role'=>$member->role], auth()->user());
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

    public function memberAction(Club $club, ClubMember $member, Request $r, ClubActivityService $activity)
    {
        abort_unless($member->club_id===$club->id,404);
        $action=$r->input('action');
        if($action==='kick'){
            abort_unless($this->can($club,'kick_members'),403);
            abort_if($member->role==='owner',422,'لا يمكن طرد مالك النادي');
            $target=$member->user;
            $before=['role'=>$member->role,'permissions'=>$member->permissions];
            $activity->record($club, auth()->user(), 'members', 'member.kicked', 'تم إخراج عضو من النادي.', $before, $target);
            $member->delete();
            return back()->with('ok','تم طرد اللاعب من النادي');
        }
        if($action==='moderator'){
            abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403);
            abort_if($member->role==='owner',422,'المالك لديه كل الصلاحيات أصلًا');
            $allowed=[
                'manage_members','accept_members','kick_members','promote_members','manage_roles',
                'create_tournaments','manage_tournaments','create_challenges','manage_challenges',
                'manage_chat','create_announcements','manage_club_profile','view_audit_log','manage_treasury',
            ];
            $requested=$r->input('permissions', []);
            if(!is_array($requested)) $requested=[];
            // Keep compatibility with the older checkbox names used by the web form.
            foreach($allowed as $permission) if($r->boolean($permission)) $requested[]=$permission;
            $perms=array_fill_keys(array_values(array_unique(array_intersect($requested,$allowed))),true);
            $before=['role'=>$member->role,'permissions'=>$member->permissions];
            $member->update(['role'=>'moderator','permissions'=>$perms]);
            $activity->record($club, auth()->user(), 'members', 'member.promoted', 'تم تعيين مشرف وتحديد صلاحياته.', ['before'=>$before,'after'=>['role'=>'moderator','permissions'=>$perms]], $member->user);
            return back()->with('ok','تم تعيين المشرف وتحديد صلاحياته');
        }
        if($action==='member'){
            abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403);
            abort_if($member->role==='owner',422,'لا يمكن تعديل المالك');
            $before=['role'=>$member->role,'permissions'=>$member->permissions];
            $member->update(['role'=>'member','permissions'=>[]]);
            $activity->record($club, auth()->user(), 'members', 'member.demoted', 'تم إرجاع المشرف إلى عضو عادي.', ['before'=>$before], $member->user);
            return back()->with('ok','تم إرجاع اللاعب عضوًا عاديًا');
        }
        abort(422,'إجراء غير معروف');
    }


    public function announcementStore(Club $club, Request $request, ClubActivityService $activity)
    {
        abort_unless($this->can($club,'create_announcements'),403,'ليس لديك صلاحية نشر إعلانات النادي.');
        $data=$request->validate(['title'=>'required|string|max:140','body'=>'required|string|max:2000','pinned'=>'nullable|boolean']);
        $announcement=ClubAnnouncement::create(['club_id'=>$club->id,'author_id'=>auth()->id(),'title'=>$data['title'],'body'=>$data['body'],'pinned'=>$request->boolean('pinned')]);
        $activity->record($club, auth()->user(), 'announcements', 'announcement.created', 'تم نشر إعلان جديد في النادي.', ['announcement_id'=>$announcement->id,'title'=>$announcement->title]);
        return back()->with('ok','تم نشر إعلان النادي.');
    }

    public function announcementDelete(Club $club, ClubAnnouncement $announcement, ClubActivityService $activity)
    {
        abort_unless($announcement->club_id===$club->id,404);
        abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin || $announcement->author_id===auth()->id(),403);
        $activity->record($club, auth()->user(), 'announcements', 'announcement.deleted', 'تم حذف إعلان من النادي.', ['announcement_id'=>$announcement->id,'title'=>$announcement->title]);
        $announcement->delete();
        return back()->with('ok','تم حذف الإعلان.');
    }

    public function createTournament(Club $club, Request $request, ClubActivityService $activity)
    {
        abort_unless($this->can($club,'create_tournaments'),403,'ليس لديك صلاحية إنشاء مسابقات النادي.');
        $request->merge(['club_id'=>$club->id]);
        $activity->record($club, auth()->user(), 'competitions', 'competition.created', 'تم إنشاء منافسة جديدة للنادي.', ['name'=>$request->input('name'),'game_id'=>$request->input('game_id')]);
        return app(TournamentController::class)->store($request, app(\App\Services\Wallet\WalletService::class));
    }

    public function respond(ClubJoinRequest $request, Request $r, ClubActivityService $activity)
    {
        $club=$request->club;
        abort_unless($this->can($club,'accept_members'),403);
        $data=$r->validate(['status'=>'required|in:accepted,rejected']);
        $status=$data['status'];
        $request->update(['status'=>$status]);
        if($status==='accepted'){
            abort_if($club->members()->count() >= $this->cap($club),422,'النادي ممتلئ حسب مستوى النادي الحالي');
            ClubMember::firstOrCreate(['club_id'=>$club->id,'user_id'=>$request->user_id],['role'=>'member','permissions'=>[]]);
        }
        $activity->record($club, auth()->user(), 'members', 'member.join.'.$status, $status==='accepted'?'تم قبول عضو جديد في النادي.':'تم رفض طلب الانضمام.', ['request_id'=>$request->id], $request->user);
        return back()->with('ok','تم تحديث طلب الانضمام');
    }
}
