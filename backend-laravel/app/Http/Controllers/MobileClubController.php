<?php

namespace App\Http\Controllers;

use App\Models\{Club,ClubMember,User};
use App\Services\Clubs\ClubActivityService;
use Illuminate\Http\Request;

class MobileClubController extends Controller
{
    private const PERMISSIONS = [
        'manage_members','accept_members','kick_members','promote_members','manage_roles',
        'create_tournaments','manage_tournaments','create_challenges','manage_challenges',
        'manage_chat','create_announcements','manage_club_profile','view_audit_log','manage_treasury',
    ];

    private function membership(Request $request, Club $club): ClubMember
    {
        return $club->members()->where('user_id',$request->user()->id)->firstOrFail();
    }

    private function can(Request $request, Club $club, string $permission): bool
    {
        if ((int)$club->owner_id === (int)$request->user()->id || $request->user()->is_admin) return true;
        $member = $club->members()->where('user_id',$request->user()->id)->first();
        if (!$member || $member->role !== 'moderator') return false;
        $permissions = $member->permissions ?: [];
        return !empty($permissions['all']) || !empty($permissions[$permission]) || in_array($permission,$permissions,true);
    }

    public function index(Request $request)
    {
        $clubs = Club::withCount('members')->with('owner.profile')->orderByDesc('weekly_points')->limit(100)->get();
        return response()->json(['ok'=>true,'clubs'=>$clubs]);
    }

    public function mine(Request $request)
    {
        $member = ClubMember::with(['club.owner.profile','club.members.user.profile'])->where('user_id',$request->user()->id)->first();
        if (!$member) return response()->json(['ok'=>true,'club'=>null]);
        $club = $member->club;
        return response()->json([
            'ok'=>true,
            'club'=>[
                'id'=>$club->id,'name'=>$club->name,'logo'=>$club->logo,'description'=>$club->description,
                'level'=>(int)$club->level,'weekly_points'=>(int)$club->weekly_points,'total_points'=>(int)$club->total_points,
                'treasury'=>(int)$club->treasury,'capacity'=>(int)$club->capacity,'role'=>$member->role,
                'permissions'=>$member->permissions ?: [],
                'members'=>$club->members->map(fn($m)=>[
                    'membership_id'=>$m->id,'user_id'=>$m->user_id,'name'=>$m->user?->profile?->display_name ?: $m->user?->username,
                    'username'=>$m->user?->username,'avatar'=>$m->user?->profile?->avatar,'role'=>$m->role,
                    'permissions'=>$m->permissions ?: [],'weekly_points'=>(int)$m->weekly_points,
                ])->values(),
            ],
            'available_permissions'=>self::PERMISSIONS,
        ]);
    }

    public function updateMember(Request $request, Club $club, ClubMember $member, ClubActivityService $activity)
    {
        abort_unless((int)$member->club_id === (int)$club->id, 404);
        abort_unless($this->can($request,$club,'manage_roles') || $this->can($request,$club,'promote_members'), 403, 'لا تملك صلاحية إدارة أدوار النادي.');
        abort_if($member->role === 'owner', 422, 'لا يمكن تعديل مالك النادي.');
        $data=$request->validate([
            'role'=>'required|in:member,moderator',
            'permissions'=>'nullable|array|max:30',
            'permissions.*'=>'string|in:'.implode(',',self::PERMISSIONS),
        ]);
        $permissions=$data['role']==='moderator' ? array_fill_keys(array_values(array_unique($data['permissions'] ?? [])),true) : [];
        $before=['role'=>$member->role,'permissions'=>$member->permissions];
        $member->update(['role'=>$data['role'],'permissions'=>$permissions]);
        $activity->record($club,$request->user(),'members','member.permissions.updated','تم تحديث دور وصلاحيات عضو النادي.',[
            'before'=>$before,'after'=>['role'=>$member->role,'permissions'=>$member->permissions],
        ],$member->user);
        return response()->json(['ok'=>true,'message'=>'تم تحديث صلاحيات العضو','member'=>$member->fresh('user.profile')]);
    }

    public function activity(Request $request, Club $club)
    {
        $this->membership($request,$club);
        abort_unless($this->can($request,$club,'view_audit_log') || (int)$club->owner_id===(int)$request->user()->id,403,'لا تملك صلاحية عرض سجل النادي.');
        $logs=$club->activityLogs()->with(['actor.profile','subject.profile'])->limit(250)->get();
        return response()->json(['ok'=>true,'logs'=>$logs]);
    }
}
