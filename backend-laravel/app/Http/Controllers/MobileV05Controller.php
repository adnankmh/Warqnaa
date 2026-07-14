<?php

namespace App\Http\Controllers;

use App\Models\{Club,ClubActivityLog,ClubMember,Game};
use App\Services\WarqnaPro\ChallengeRoadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileV05Controller extends Controller
{
    private const CLUB_PERMISSIONS = ['accept_members','kick_members','manage_members','create_tournaments','manage_chat','create_announcements','edit_identity','view_logs'];

    public function challenge(Request $request, ChallengeRoadService $road)
    {
        $run=$road->active($request->user());
        return response()->json(['ok'=>true,'run'=>$run ? $road->payload($run) : null,'stage_options'=>ChallengeRoadService::ALLOWED_STAGES,'lives'=>ChallengeRoadService::MAX_LIVES]);
    }

    public function startChallenge(Request $request, ChallengeRoadService $road)
    {
        $data=$request->validate(['game'=>'required|string|max:80','stages'=>'nullable|integer|in:10,12,15']);
        abort_unless(Game::where('key',$data['game'])->where('active',true)->exists(),422,'اللعبة غير متاحة.');
        $run=$road->start($request->user(),$data['game'],(int)($data['stages']??15));
        return response()->json(['ok'=>true,'message'=>'بدأ طريق التحدي بخمس محاولات.','run'=>$road->payload($run)],201);
    }

    public function clubs(Request $request)
    {
        $clubs=Club::with(['members.user.profile'])->withCount('members')->latest()->limit(100)->get()->map(fn(Club $club)=>$this->clubPayload($club,$request->user()->id));
        return response()->json(['ok'=>true,'clubs'=>$clubs]);
    }

    public function club(Request $request, Club $club)
    {
        $club->load(['owner.profile','members.user.profile','announcements','activityLogs.actor.profile']);
        return response()->json(['ok'=>true,'club'=>$this->clubPayload($club,$request->user()->id,true)]);
    }

    public function updateClub(Request $request, Club $club)
    {
        $member=$this->authorizeClub($request,$club,'edit_identity');
        $data=$request->validate(['name'=>'nullable|string|min:3|max:120|unique:clubs,name,'.$club->id,'description'=>'nullable|string|max:800','logo'=>'nullable|string|max:30','image_url'=>'nullable|string|max:200000','banner_url'=>'nullable|string|max:200000','visibility'=>'nullable|in:public,request,private']);
        $before=$club->only(['name','description','logo','image_url','banner_url','visibility']); $club->update($data);
        $this->log($club,$request->user()->id,'club_identity_updated',['before'=>$before,'after'=>$club->only(array_keys($before))]);
        return response()->json(['ok'=>true,'message'=>'تم تحديث صورة وهوية المجموعة.','club'=>$this->clubPayload($club->fresh('members.user.profile'),$request->user()->id,true)]);
    }

    public function updateMember(Request $request, Club $club, ClubMember $member)
    {
        abort_unless((int)$member->club_id===(int)$club->id,404);
        abort_unless((int)$club->owner_id===(int)$request->user()->id || $request->user()->hasAdminPermission('clubs'),403,'تعديل الصلاحيات للمالك أو إدارة المنصة فقط.');
        abort_if($member->role==='owner',422,'لا يمكن تعديل صلاحيات مالك المجموعة.');
        $data=$request->validate(['role'=>'required|in:member,moderator','permissions'=>'nullable|array']);
        $permissions=[]; foreach(self::CLUB_PERMISSIONS as $permission)$permissions[$permission]=!empty(($data['permissions']??[])[$permission]);
        $member->update(['role'=>$data['role'],'permissions'=>$data['role']==='moderator'?$permissions:[]]);
        $this->log($club,$request->user()->id,'member_permissions_updated',['member_id'=>$member->id,'user_id'=>$member->user_id,'role'=>$member->role,'permissions'=>$member->permissions]);
        return response()->json(['ok'=>true,'message'=>'تم حفظ الصلاحيات المتعددة للعضو.','member'=>$member->fresh('user.profile')]);
    }

    public function logs(Request $request, Club $club)
    {
        $this->authorizeClub($request,$club,'view_logs');
        return response()->json(['ok'=>true,'logs'=>$club->activityLogs()->with('actor.profile')->limit(200)->get()]);
    }

    private function authorizeClub(Request $request,Club $club,string $permission): ClubMember
    {
        $member=$club->members()->where('user_id',$request->user()->id)->firstOrFail();
        $allowed=(int)$club->owner_id===(int)$request->user()->id || $request->user()->hasAdminPermission('clubs');
        $allowed=$allowed || ($member->role==='moderator' && (!empty($member->permissions['all']) || !empty($member->permissions[$permission])));
        abort_unless($allowed,403,'لا تملك الصلاحية المطلوبة.'); return $member;
    }

    private function log(Club $club,?int $actor,string $event,array $payload=[]): void
    { ClubActivityLog::create(['club_id'=>$club->id,'actor_id'=>$actor,'event'=>$event,'payload'=>$payload]); }

    private function clubPayload(Club $club,int $viewer,bool $details=false): array
    {
        $membership=$club->members->firstWhere('user_id',$viewer);
        $base=['id'=>$club->id,'name'=>$club->name,'logo'=>$club->logo,'image_url'=>$club->image_url,'banner_url'=>$club->banner_url,'description'=>$club->description,'level'=>(int)$club->level,'league_tier'=>$club->league_tier,'visibility'=>$club->visibility,'members_count'=>(int)($club->members_count??$club->members->count()),'viewer_role'=>$membership?->role,'viewer_permissions'=>$membership?->permissions?:[]];
        if($details)$base['members']=$club->members->map(fn($m)=>['id'=>$m->id,'role'=>$m->role,'permissions'=>$m->permissions?:[],'user'=>$m->user?->publicProfile()])->values();
        return $base;
    }
}
