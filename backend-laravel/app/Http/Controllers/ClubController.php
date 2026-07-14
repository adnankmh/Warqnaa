<?php
namespace App\Http\Controllers;

use App\Models\{Club,ClubMember,ClubJoinRequest,ClubAnnouncement,ClubActivityLog,Notification,Tournament};
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
    private function log(Club $club, string $eventType, string $description, array $meta=[]): void
    {
        ClubActivityLog::create([
            'club_id'=>$club->id,
            'actor_id'=>auth()->id(),
            'event_type'=>$eventType,
            'description'=>$description,
            'meta'=>$meta,
        ]);
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
        $club->load('owner.profile','members.user.profile','joinRequests.user.profile','announcements.author.profile','tournaments.game','activityLogs.actor.profile');
        return view('clubs.show',[
            'club'=>$club,
            'clubCaps'=>$this->clubCaps,
            'clubLeagues'=>$this->clubLeagues,
            'canManage'=>$this->can($club,'manage_members'),
            'canAccept'=>$this->can($club,'accept_members'),
            'canKick'=>$this->can($club,'kick_members'),
            'canTournament'=>$this->can($club,'create_tournaments'),
            'canAnnounce'=>$this->can($club,'create_announcements'),
            'canManageClub'=>$this->can($club,'manage_club'),
        ]);
    }

    public function store(Request $r, WalletService $wallet)
    {
        abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0 || auth()->user()->is_admin,403,'إنشاء المجموعات ميزة لأعضاء الباشا فقط، ويجب توفر التوكنز الكافية.');
        abort_if(ClubMember::where('user_id',auth()->id())->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل إنشاء نادي جديد.');
        $data=$r->validate([
            'name'=>'required|string|max:120|unique:clubs,name',
            'description'=>'nullable|string|max:1000',
            'logo'=>'nullable|string|max:500',
            'visibility'=>'nullable|in:public,request,private',
        ]);
        try { $wallet->debit(auth()->user(),5000,'club_create',['name'=>$data['name']]); }
        catch(RuntimeException $e){ return back()->withErrors(['msg'=>'لا تملك توكنز كافية لإنشاء النادي.']); }
        $club=Club::create([
            'owner_id'=>auth()->id(),'name'=>$data['name'],'description'=>$data['description']??null,
            'logo'=>$data['logo']??'👥','visibility'=>$data['visibility']??'public',
            'level'=>1,'treasury'=>0,'capacity'=>20,'league_tier'=>'bronze',
        ]);
        ClubMember::create(['club_id'=>$club->id,'user_id'=>auth()->id(),'role'=>'owner','permissions'=>['all'=>true]]);
        $this->log($club,'club.created','تم إنشاء النادي',['name'=>$club->name]);
        return redirect()->route('clubs.show',$club)->with('ok','تم إنشاء النادي');
    }

    public function requestJoin(Club $club)
    {
        abort_if($club->visibility==='private',403,'هذا النادي خاص ولا يقبل طلبات عامة.');
        abort_if(ClubMember::where('user_id',auth()->id())->where('club_id','!=',$club->id)->exists(),403,'أنت عضو في نادي آخر بالفعل. غادر النادي الحالي قبل الانضمام إلى نادي جديد.');
        abort_if(ClubJoinRequest::where('user_id',auth()->id())->where('club_id','!=',$club->id)->where('status','pending')->exists(),403,'لديك طلب انضمام معلق لنادي آخر. ألغِ أو انتظر الرد قبل إرسال طلب جديد.');
        abort_if($club->members()->where('user_id',auth()->id())->exists(),409,'أنت عضو في النادي بالفعل');
        abort_if($club->members()->count() >= $this->cap($club),422,'النادي ممتلئ حسب مستوى النادي الحالي');
        if($club->visibility==='public'){
            ClubMember::firstOrCreate(['club_id'=>$club->id,'user_id'=>auth()->id()],['role'=>'member','permissions'=>[]]);
            $this->log($club,'join.accepted','انضم '.auth()->user()->username.' إلى النادي العام.');
            return back()->with('ok','تم الانضمام إلى النادي.');
        }
        ClubJoinRequest::updateOrCreate(['club_id'=>$club->id,'user_id'=>auth()->id()],['status'=>'pending']);
        $this->log($club,'join.requested','طلب '.auth()->user()->username.' الانضمام إلى النادي.');
        Notification::create(['user_id'=>$club->owner_id,'type'=>'club_join','title'=>['ar'=>'طلب انضمام لنادي'],'body'=>['ar'=>auth()->user()->username.' طلب الانضمام إلى '.$club->name],'url'=>route('clubs.show',$club)]);
        return back()->with('ok','تم إرسال طلب الانضمام');
    }

    public function leave(Club $club)
    {
        $member=$club->members()->where('user_id',auth()->id())->first();
        abort_unless($member,404,'أنت لست عضوًا في هذا النادي');
        abort_if($member->role==='owner' && $club->members()->count()>1,422,'لا يمكن للمالك مغادرة النادي قبل حذف النادي أو نقل الملكية.');
        if($member->role==='owner' && $club->members()->count()===1){ $club->delete(); return redirect()->route('clubs')->with('ok','تم حذف النادي لأنه لم يعد يحتوي أعضاء.'); }
        $this->log($club,'member.left','غادر '.auth()->user()->username.' النادي.');
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
            $name=$member->user?->username ?? ('#'.$member->user_id);
            $this->log($club,'member.kicked','تم طرد '.$name.' من النادي.',['user_id'=>$member->user_id]);
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
                'create_announcements'=>$r->boolean('create_announcements'),
                'manage_club'=>$r->boolean('manage_club'),
            ];
            $member->update(['role'=>'moderator','permissions'=>$perms]);
            $this->log($club,'member.promoted','تمت ترقية '.$member->user?->username.' وتحديث صلاحياته.',['user_id'=>$member->user_id,'permissions'=>$perms]);
            return back()->with('ok','تم تعيين المشرف وتحديد صلاحياته');
        }
        if($action==='member'){
            abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin,403);
            abort_if($member->role==='owner',422,'لا يمكن تعديل المالك');
            $member->update(['role'=>'member','permissions'=>[]]);
            $this->log($club,'member.demoted','تمت إعادة '.$member->user?->username.' إلى عضو عادي.',['user_id'=>$member->user_id]);
            return back()->with('ok','تم إرجاع اللاعب عضوًا عاديًا');
        }
        abort(422,'إجراء غير معروف');
    }

    public function announcementStore(Club $club, Request $request)
    {
        abort_unless($this->can($club,'create_announcements'),403,'ليس لديك صلاحية نشر إعلانات النادي.');
        $data=$request->validate(['title'=>'required|string|max:140','body'=>'required|string|max:2000','pinned'=>'nullable|boolean']);
        ClubAnnouncement::create(['club_id'=>$club->id,'author_id'=>auth()->id(),'title'=>$data['title'],'body'=>$data['body'],'pinned'=>$request->boolean('pinned')]);
        $this->log($club,'announcement.created','تم نشر إعلان: '.$data['title']);
        return back()->with('ok','تم نشر إعلان النادي.');
    }

    public function announcementDelete(Club $club, ClubAnnouncement $announcement)
    {
        abort_unless($announcement->club_id===$club->id,404);
        abort_unless($club->owner_id===auth()->id() || auth()->user()->is_admin || $announcement->author_id===auth()->id(),403);
        $title=$announcement->title;
        $announcement->delete();
        $this->log($club,'announcement.deleted','تم حذف إعلان: '.$title);
        return back()->with('ok','تم حذف الإعلان.');
    }

    public function createTournament(Club $club, Request $request)
    {
        abort_unless($this->can($club,'create_tournaments'),403,'ليس لديك صلاحية إنشاء مسابقات النادي.');
        $request->merge(['club_id'=>$club->id]);
        $this->log($club,'tournament.created','تم طلب إنشاء مسابقة جديدة للنادي.',['game_id'=>$request->input('game_id'),'stages'=>$request->input('stages')]);
        return app(TournamentController::class)->store($request, app(\App\Services\Wallet\WalletService::class));
    }

    public function respond(ClubJoinRequest $request, Request $r)
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
        $name=$request->user?->username ?? ('#'.$request->user_id);
        $this->log($club,$status==='accepted'?'join.accepted':'join.rejected',($status==='accepted'?'تم قبول ':'تم رفض ').$name.'.',['user_id'=>$request->user_id]);
        return back()->with('ok','تم تحديث طلب الانضمام');
    }

    public function updateSettings(Club $club, Request $request)
    {
        abort_unless($this->can($club,'manage_club'),403,'ليس لديك صلاحية تعديل هوية النادي.');
        $data=$request->validate([
            'name'=>'required|string|max:120|unique:clubs,name,'.$club->id,
            'description'=>'nullable|string|max:1000',
            'logo'=>'nullable|string|max:500',
            'visibility'=>'required|in:public,request,private',
        ]);
        $before=$club->only(['name','description','logo','visibility']);
        $club->update($data);
        $this->log($club,'club.updated','تم تحديث اسم أو صورة أو إعدادات النادي.',['before'=>$before,'after'=>$club->only(['name','description','logo','visibility'])]);
        return back()->with('ok','تم تحديث هوية النادي وإعداداته.');
    }
}
