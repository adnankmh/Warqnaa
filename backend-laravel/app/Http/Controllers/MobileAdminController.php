<?php

namespace App\Http\Controllers;

use App\Models\{AdminAuditLog,AdminDesignerEntity,AppRelease,FeatureFlag,Game,Room,StoreItem,User,UserReport};
use App\Services\GameEngine\EngineRegistry;
use App\Services\Platform\{AdminAuditService,ProductionConfigService};
use Illuminate\Http\Request;

class MobileAdminController extends Controller
{
    private function guard(Request $request, string $permission = 'dashboard'): void
    {
        abort_unless($request->user()?->hasAdminPermission($permission), 403, 'لا تملك صلاحية الإدارة المطلوبة: '.$permission);
    }

    public function dashboard(Request $request)
    {
        $this->guard($request, 'dashboard');
        $viewer = $request->user();
        $can = fn (string $permission): bool => $viewer->hasAdminPermission($permission);
        return response()->json([
            'ok' => true,
            'permissions' => $viewer->admin_permissions ?: [],
            'stats' => [
                'users' => User::count(),
                'online' => User::where('last_seen_at', '>=', now()->subMinutes(3))->count(),
                'games' => Game::where('active', true)->count(),
                'rooms' => Room::whereIn('status', ['waiting', 'bidding', 'playing'])->count(),
                'store_items' => StoreItem::where('active', true)->count(),
                'open_reports' => UserReport::whereIn('status', ['open','reviewing'])->count(),
            ],
            'users' => ($can('users') || $can('economy')) ? User::with('profile', 'wallet')->latest()->limit(100)->get()->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $can('users') ? $user->email : null,
                'is_admin' => (bool) $user->is_admin,
                'admin_permissions' => $can('users') ? ($user->admin_permissions ?: []) : [],
                'is_banned' => (bool) $user->is_banned,
                'level' => (int) ($user->profile?->level ?? 1),
                'tokens' => $can('economy') ? (string) ($user->wallet?->tokens ?? 0) : null,
            ]) : [],
            'games' => $can('games') ? Game::orderBy('id')->get() : [],
            'store' => $can('store') ? StoreItem::orderBy('category')->orderBy('price')->get() : [],
            'rooms' => $can('rooms') ? Room::with('game')->latest()->limit(100)->get() : [],
            'engine_registry' => $can('games') ? EngineRegistry::all() : [],
            'feature_flags' => $can('dashboard') ? FeatureFlag::orderBy('key')->get() : [],
            'app_releases' => $can('releases') ? AppRelease::latest('build_number')->limit(30)->get() : [],
            'moderation_reports' => $can('moderation') ? UserReport::with(['reporter.profile','reportedUser.profile'])->latest()->limit(30)->get() : [],
            'audit_logs' => $can('dashboard') ? AdminAuditLog::with('admin.profile')->latest()->limit(50)->get() : [],
            'designer_entities' => $can('designer') ? AdminDesignerEntity::orderBy('entity_type')->orderBy('sort_order')->orderBy('key')->get() : [],
        ]);
    }

    public function updateGame(Request $request, Game $game, AdminAuditService $audit)
    {
        $this->guard($request, 'games');
        $data = $request->validate([
            'active' => 'nullable|boolean',
            'min_players' => 'nullable|integer|min:1|max:8',
            'max_players' => 'nullable|integer|min:1|max:8',
            'partnership' => 'nullable|boolean',
            'name' => 'nullable|array',
            'rules' => 'nullable|array',
        ]);
        $before = $game->toArray();
        $game->update($data);
        $audit->record($request, 'admin.game.update', $game, $before, $game->fresh()->toArray());
        return response()->json(['ok' => true, 'message' => 'تم تحديث اللعبة', 'game' => $game->fresh()]);
    }

    public function updateStore(Request $request, StoreItem $item, AdminAuditService $audit)
    {
        $this->guard($request, 'store');
        $data = $request->validate([
            'price' => 'nullable|integer|min:0|max:9000000000000000000',
            'active' => 'nullable|boolean',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'name' => 'nullable|array',
            'payload' => 'nullable|array',
        ]);
        $before = $item->toArray();
        $item->update($data);
        $audit->record($request, 'admin.store.update', $item, $before, $item->fresh()->toArray());
        return response()->json(['ok' => true, 'message' => 'تم تحديث عنصر المتجر', 'item' => $item->fresh()]);
    }

    public function userAction(Request $request, User $user, AdminAuditService $audit)
    {
        $data = $request->validate([
            'action' => 'required|in:ban,unban,grant_tokens,set_level,set_admin_permissions,send_friend_request',
            'amount' => 'nullable|integer|min:0|max:1000000000000',
            'level' => 'nullable|integer|min:1|max:999',
            'permissions' => 'nullable|array',
        ]);
        $requiredPermission = in_array($data['action'], ['grant_tokens'], true) ? 'economy' : 'users';
        $this->guard($request, $requiredPermission);
        $before = $user->load('profile','wallet')->toArray();
        match ($data['action']) {
            'ban' => $user->update(['is_banned' => true]),
            'unban' => $user->update(['is_banned' => false]),
            'grant_tokens' => $user->wallet()->firstOrCreate(['user_id' => $user->id], ['tokens' => 50])->increment('tokens', (int) ($data['amount'] ?? 0)),
            'set_level' => $user->profile?->update(['level' => (int) ($data['level'] ?? 1)]),
            'set_admin_permissions' => $user->update(['admin_permissions' => $this->sanitizePermissions($data['permissions'] ?? [])]),
            'send_friend_request' => $this->sendAdminFriendRequest($request->user(), $user),
        };
        $audit->record($request, 'admin.user.' . $data['action'], $user, $before, $user->fresh()->load('profile','wallet')->toArray());
        return response()->json(['ok' => true, 'message' => 'تم تنفيذ الإجراء']);
    }

    public function updateFeatureFlag(Request $request, FeatureFlag $flag, AdminAuditService $audit, ProductionConfigService $config)
    {
        $this->guard($request, 'dashboard');
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'payload' => 'nullable|array',
            'environment' => 'nullable|in:all,local,testing,staging,production',
        ]);
        $before = $flag->toArray();
        $flag->update($data);
        $config->forget();
        $audit->record($request, 'admin.feature_flag.update', $flag, $before, $flag->fresh()->toArray());
        return response()->json(['ok'=>true,'message'=>'تم تحديث ميزة المنصة','flag'=>$flag->fresh()]);
    }


    public function designerIndex(Request $request)
    {
        $this->guard($request, 'designer');
        return response()->json([
            'ok' => true,
            'entities' => AdminDesignerEntity::orderBy('entity_type')->orderBy('sort_order')->orderBy('key')->get(),
        ]);
    }

    public function upsertDesigner(Request $request, string $entityType, string $key, AdminAuditService $audit)
    {
        $this->guard($request, 'designer');
        abort_unless((bool) preg_match('/^[a-z0-9_-]{2,80}$/i', $entityType), 422, 'نوع العنصر غير صحيح.');
        abort_unless((bool) preg_match('/^[a-z0-9_.:-]{2,150}$/i', $key), 422, 'مفتاح العنصر غير صحيح.');
        $data = $request->validate([
            'locale' => 'nullable|string|max:10',
            'payload' => 'required|array',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
            'active' => 'nullable|boolean',
        ]);
        $locale = (string) ($data['locale'] ?? 'all');
        $entity = AdminDesignerEntity::firstOrNew([
            'entity_type' => strtolower($entityType),
            'key' => $key,
            'locale' => $locale,
        ]);
        $before = $entity->exists ? $entity->toArray() : null;
        $entity->payload = $data['payload'];
        $entity->sort_order = (int) ($data['sort_order'] ?? $entity->sort_order ?? 0);
        $entity->active = array_key_exists('active', $data) ? (bool) $data['active'] : true;
        $entity->revision = max(1, (int) ($entity->revision ?? 0) + 1);
        $entity->updated_by = $request->user()->id;
        $entity->save();
        $audit->record($request, 'admin.designer.upsert', $entity, $before, $entity->fresh()->toArray());
        return response()->json(['ok'=>true,'message'=>'تم حفظ العنصر ونشره','entity'=>$entity->fresh()]);
    }

    public function deleteDesigner(Request $request, AdminDesignerEntity $entity, AdminAuditService $audit)
    {
        $this->guard($request, 'designer');
        $before = $entity->toArray();
        $audit->record($request, 'admin.designer.delete', $entity, $before, null);
        $entity->delete();
        return response()->json(['ok'=>true,'message'=>'تم حذف العنصر من المصمم الشامل']);
    }

    public function createRelease(Request $request, AdminAuditService $audit)
    {
        $this->guard($request, 'releases');
        $data = $request->validate([
            'platform'=>'required|in:web,android,ios',
            'version'=>'required|string|max:40',
            'build_number'=>'required|integer|min:1|max:999999',
            'required'=>'nullable|boolean',
            'active'=>'nullable|boolean',
            'notes'=>'nullable|string|max:5000',
            'download_url'=>'nullable|url|max:500',
        ]);
        $release = AppRelease::updateOrCreate(
            ['platform'=>$data['platform'],'version'=>$data['version'],'build_number'=>$data['build_number']],
            $data
        );
        $audit->record($request, 'admin.release.upsert', $release, null, $release->toArray());
        return response()->json(['ok'=>true,'message'=>'تم حفظ إصدار التطبيق','release'=>$release]);
    }

    /** @param array<string,mixed> $permissions */
    private function sanitizePermissions(array $permissions): array
    {
        $allowed = ['dashboard','users','economy','store','games','rooms','clubs','challenges','competitions','designer','translations','themes','ads','moderation','releases'];
        $safe = [];
        foreach ($allowed as $permission) $safe[$permission] = !empty($permissions[$permission]);
        if (in_array(true, $safe, true)) $safe['dashboard'] = true;
        return $safe;
    }

    private function sendAdminFriendRequest(User $admin, User $target): void
    {
        if ((int)$admin->id === (int)$target->id) return;
        \App\Models\Friendship::firstOrCreate(
            ['requester_id'=>$admin->id,'addressee_id'=>$target->id],
            ['status'=>'pending']
        );
    }


}
