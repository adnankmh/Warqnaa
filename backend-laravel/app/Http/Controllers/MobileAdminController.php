<?php

namespace App\Http\Controllers;

use App\Models\{AdminAuditLog,AdminDelegation,AdminDesignerEntity,AppRelease,FeatureFlag,Friendship,Game,Notification,Room,StoreItem,User,UserReport};
use App\Services\GameEngine\EngineRegistry;
use App\Services\Platform\{AdminAuditService,ProductionConfigService};
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;

class MobileAdminController extends Controller
{
    private function guard(Request $request, string $permission = 'dashboard.view'): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        if ((bool)$user->is_admin) return;
        $delegation = $user->adminDelegation()->where('active', true)->first();
        abort_unless($delegation?->allows($permission) || $delegation?->allows('dashboard.view'), 403, 'ليست لديك الصلاحية الإدارية المطلوبة.');
    }

    private function ownerGuard(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && (bool)$user->is_admin && strtolower(trim((string)$user->username)) === 'adnan', 403, 'إدارة التفويضات للمدير الرئيسي Adnan فقط.');
    }

    private function primaryAdnanGuard(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && (bool)$user->is_admin && strtolower(trim((string)$user->username)) === 'adnan', 403, 'المصمم الشامل متاح للمدير الرئيسي Adnan فقط.');
    }

    public function dashboard(Request $request)
    {
        $this->guard($request);
        return response()->json([
            'ok' => true,
            'stats' => [
                'users' => User::count(),
                'online' => User::where('last_seen_at', '>=', now()->subMinutes(3))->count(),
                'games' => Game::where('active', true)->count(),
                'rooms' => Room::whereIn('status', ['waiting', 'bidding', 'playing'])->count(),
                'store_items' => StoreItem::where('active', true)->count(),
                'open_reports' => UserReport::whereIn('status', ['open','reviewing'])->count(),
            ],
            'users' => User::with('profile', 'wallet')->latest()->limit(100)->get()->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
                'is_banned' => (bool) $user->is_banned,
                'level' => (int) ($user->profile?->level ?? 1),
                'tokens' => (string) ($user->wallet?->tokens ?? 0),
            ]),
            'games' => Game::orderBy('id')->get(),
            'store' => StoreItem::orderBy('category')->orderBy('price')->get(),
            'rooms' => Room::with('game')->latest()->limit(100)->get(),
            'engine_registry' => EngineRegistry::all(),
            'feature_flags' => FeatureFlag::orderBy('key')->get(),
            'app_releases' => AppRelease::latest('build_number')->limit(30)->get(),
            'moderation_reports' => UserReport::with(['reporter.profile','reportedUser.profile'])->latest()->limit(30)->get(),
            'audit_logs' => AdminAuditLog::with('admin.profile')->latest()->limit(50)->get(),
            'designer_entities' => AdminDesignerEntity::orderBy('entity_type')->orderBy('sort_order')->orderBy('key')->get(),
            'delegations' => AdminDelegation::with('user.profile')->latest()->get(),
        ]);
    }

    public function updateGame(Request $request, Game $game, AdminAuditService $audit)
    {
        $this->guard($request, 'games.manage');
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
        $this->guard($request, 'store.manage');
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

    public function userAction(Request $request, User $user, AdminAuditService $audit, WalletService $wallet)
    {
        $this->guard($request, 'users.moderate');
        $data = $request->validate([
            'action' => 'required|in:ban,unban,grant_tokens,set_level,friend_request',
            'amount' => 'nullable|integer|min:0|max:1000000000000',
            'level' => 'nullable|integer|min:1|max:999',
        ]);
        if (in_array($data['action'], ['grant_tokens','friend_request'], true)) {
            $this->primaryAdnanGuard($request);
        }
        if ($data['action'] === 'grant_tokens') {
            abort_unless((int)($data['amount'] ?? 0) > 0, 422, 'يجب أن تكون كمية التوكنز أكبر من صفر.');
        }
        $before = $user->load('profile','wallet')->toArray();
        match ($data['action']) {
            'ban' => $user->update(['is_banned' => true]),
            'unban' => $user->update(['is_banned' => false]),
            'grant_tokens' => $wallet->credit($user, (int)$data['amount'], 'admin_grant', [
                'admin_id'=>(int)$request->user()->id,
                'reason'=>'mobile_admin_action',
            ]),
            'set_level' => $user->profile()->firstOrCreate([], ['display_name'=>$user->username])->update(['level' => (int) ($data['level'] ?? 1)]),
            'friend_request' => $this->sendAdminFriendRequest($request->user(), $user),
        };
        $audit->record($request, 'admin.user.' . $data['action'], $user, $before, $user->fresh()->load('profile','wallet')->toArray());
        return response()->json(['ok' => true, 'message' => 'تم تنفيذ الإجراء']);
    }

    public function updateFeatureFlag(Request $request, FeatureFlag $flag, AdminAuditService $audit, ProductionConfigService $config)
    {
        $this->guard($request, 'features.manage');
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
        $this->primaryAdnanGuard($request);
        return response()->json([
            'ok' => true,
            'entities' => AdminDesignerEntity::orderBy('entity_type')->orderBy('sort_order')->orderBy('key')->get(),
        ]);
    }

    public function upsertDesigner(Request $request, string $entityType, string $key, AdminAuditService $audit)
    {
        $this->primaryAdnanGuard($request);
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
        $this->primaryAdnanGuard($request);
        $before = $entity->toArray();
        $audit->record($request, 'admin.designer.delete', $entity, $before, null);
        $entity->delete();
        return response()->json(['ok'=>true,'message'=>'تم حذف العنصر من المصمم الشامل']);
    }

    public function delegations(Request $request)
    {
        $this->ownerGuard($request);
        return response()->json(['ok'=>true,'delegations'=>AdminDelegation::with('user.profile')->latest()->get()]);
    }

    public function updateDelegation(Request $request, User $user, AdminAuditService $audit)
    {
        $this->ownerGuard($request);
        abort_if(strtolower((string)$user->username) === 'adnan', 422, 'المدير الرئيسي يملك كل الصلاحيات تلقائياً.');
        $allowed = ['store.manage','games.manage','clubs.manage','competitions.manage','designer.manage','users.moderate','reports.manage','features.manage','dashboard.view'];
        $data = $request->validate(['permissions'=>'required|array|max:20','permissions.*'=>'string|in:'.implode(',', $allowed)]);
        $delegation = AdminDelegation::updateOrCreate(
            ['user_id'=>$user->id],
            ['granted_by'=>$request->user()->id,'permissions'=>array_values(array_unique($data['permissions'])),'active'=>true]
        );
        $audit->record($request, 'admin.delegation.update', $delegation, null, $delegation->toArray());
        return response()->json(['ok'=>true,'message'=>'تم حفظ الصلاحيات','delegation'=>$delegation->load('user.profile')]);
    }

    public function deleteDelegation(Request $request, User $user, AdminAuditService $audit)
    {
        $this->ownerGuard($request);
        $delegation = AdminDelegation::where('user_id',$user->id)->first();
        if ($delegation) {
            $before=$delegation->toArray();
            $audit->record($request, 'admin.delegation.delete', $delegation, $before, null);
            $delegation->delete();
        }
        return response()->json(['ok'=>true,'message'=>'تم إلغاء التفويض']);
    }

    private function sendAdminFriendRequest(User $admin, User $target): void
    {
        abort_if((int)$admin->id === (int)$target->id, 422, 'لا يمكن إرسال طلب صداقة إلى الحساب نفسه.');
        $existing = Friendship::where(function ($query) use ($admin, $target) {
            $query->where('requester_id',$admin->id)->where('addressee_id',$target->id);
        })->orWhere(function ($query) use ($admin, $target) {
            $query->where('requester_id',$target->id)->where('addressee_id',$admin->id);
        })->first();
        if ($existing) {
            abort_if($existing->status === 'blocked', 422, 'توجد حالة حظر بين الحسابين.');
            return;
        }
        $friendship = Friendship::create(['requester_id'=>$admin->id,'addressee_id'=>$target->id,'status'=>'pending']);
        Notification::create([
            'user_id'=>$target->id,'type'=>'friend_request',
            'title'=>['ar'=>'طلب صداقة من الإدارة','en'=>'Admin friend request'],
            'body'=>['ar'=>$admin->username.' أرسل لك طلب صداقة','en'=>$admin->username.' sent you a friend request'],
            'meta'=>['friendship_id'=>$friendship->id,'from'=>$admin->id,'admin'=>true],
        ]);
    }

    public function createRelease(Request $request, AdminAuditService $audit)
    {
        $this->guard($request, 'features.manage');
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

}
