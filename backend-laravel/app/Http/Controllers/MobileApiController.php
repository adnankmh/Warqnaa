<?php

namespace App\Http\Controllers;

use App\Models\{AdminDesignerEntity,ChallengeDefinition,Club,CompetitionTicket,DailyPackClaim,DailyRewardClaim,Game,Profile,RewardedAdClaim,Room,StoreItem,Tournament,User,Wallet};
use App\Services\Wallet\WalletService;
use App\Services\Platform\ProductionConfigService;
use App\Services\Account\AccountCancellationService;
use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Hash};

class MobileApiController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users,username',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:8|max:120|confirmed',
            'country_code' => 'nullable|string|size:2|not_in:IL,il',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'last_seen_at' => now(),
        ]);
        Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->username,
            'country_code' => safe_country_code($data['country_code'] ?? 'PS'),
            'country_name' => country_name($data['country_code'] ?? 'PS'),
        ]);
        Wallet::create(['user_id' => $user->id, 'tokens' => 50, 'gems' => 0]);
        $token = $user->createToken('mobile', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'token' => $token,
            'user' => $user->fresh('profile')->publicProfile(),
            'wallet' => $this->walletPayload($user),
        ], 201);
    }

    public function login(Request $request, AccountCancellationService $cancellation)
    {
        $data = $request->validate(['login' => 'required|string|max:190', 'password' => 'required|string|max:120']);
        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!Auth::attempt([$field => $data['login'], 'password' => $data['password']])) {
            return response()->json(['ok' => false, 'message' => 'بيانات الدخول غير صحيحة'], 422);
        }
        $user = $request->user();
        if ($user->is_banned) return response()->json(['ok' => false, 'message' => 'الحساب موقوف'], 403);
        $reactivated = $cancellation->reactivate($user);
        $user->tokens()->where('name', 'mobile')->delete();
        $user->update([
            'last_seen_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);
        $streakReward = $this->applyLoginStreak($user);
        return response()->json([
            'ok' => true,
            'token' => $user->createToken('mobile', ['*'], now()->addDays(30))->plainTextToken,
            'user' => $user->load('profile')->publicProfile(),
            'wallet' => $this->walletPayload($user),
            'streak_reward' => $streakReward,
            'account_reactivated' => $reactivated,
            'reactivation_message' => $reactivated ? 'تمت استعادة الحساب لأنك سجلت الدخول خلال مهلة 30 يوماً.' : null,
        ]);
    }

    public function bootstrap(Request $request, ProductionConfigService $productionConfig, StoreCatalogService $catalog)
    {
        $catalog->sync();
        $request->user()->update(['last_seen_at'=>now()]);
        $user = $request->user()->load('profile', 'wallet');
        return response()->json([
            'ok' => true,
            'user' => $user->publicProfile(),
            'wallet' => $this->walletPayload($user),
            'games' => Game::where('active', true)->orderBy('id')->get(),
            'store' => StoreItem::where('active', true)->orderBy('category')->orderBy('price')->get(),
            'rooms' => Room::query()->with('game')->latest()->limit(30)->get(),
            'tournaments' => Tournament::query()->with('game')->latest()->limit(20)->get(),
            'clubs' => Club::query()->latest()->limit(20)->get(),
            'competition_tickets' => CompetitionTicket::where('user_id', $user->id)->pluck('quantity', 'denomination')->map(fn($value)=>(int)$value)->all(),
            'daily_pack' => $this->dailyPackPayload($user->id),
            'challenges' => ChallengeDefinition::where('active', true)->orderBy('sort_order')->get(),
            'designer_config' => AdminDesignerEntity::where('active', true)->orderBy('entity_type')->orderBy('sort_order')->get()->groupBy('entity_type'),
            'champion_rank_points' => (int)($user->profile?->champion_rank_points ?? 0),
            'online_only' => true,
            'features' => [
                'themes' => true,
                'languages' => ['ar', 'en', 'de', 'tr', 'fr', 'es'],
                'chat' => true,
                'quick_reactions' => true,
                'rewards' => true,
                'vip' => true,
                'friends' => true,
                'token_transfer_fee_percent' => 10,
                'gameplay_token_cost' => 0,
                'online_only' => true,
                'rewarded_ads' => true,
                'competition_tickets' => true,
                'daily_packs' => true,
                'universal_designer' => true,
            ],
            'production' => $productionConfig->publicConfig(strtolower((string) $request->header('X-Warqna-Platform', 'web'))),
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('profile', 'wallet');
        return response()->json(['ok' => true, 'user' => $user->publicProfile(), 'wallet' => $this->walletPayload($user)]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'nullable|string|min:2|max:80',
            'country_code' => 'nullable|string|size:2|not_in:IL,il',
            'locale' => 'nullable|in:ar,en,de,tr,fr,es',
            'theme' => 'nullable|string|max:40',
            'sound_enabled' => 'nullable|boolean',
            'avatar' => 'nullable|string|max:32',
            'avatar_data' => 'nullable|string|max:1500000',
            'active_cover' => 'nullable|string|max:120',
            'bot_difficulty' => 'nullable|in:easy,normal,pro,master',
            'ui_preferences' => 'nullable|array',
            'pasha_style' => 'nullable|in:yellow,red,blue,green,purple,bronze,gold,orange,pink,silver,platinum,navy,black,white',
        ]);
        $profile = $request->user()->profile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'display_name' => $request->user()->username,
            'country_code' => 'PS',
            'country_name' => 'Palestine',
        ]);
        if (isset($data['display_name'])) $profile->display_name = $data['display_name'];
        if (isset($data['country_code'])) {
            $profile->country_code = safe_country_code($data['country_code']);
            $profile->country_name = country_name($data['country_code']);
        }
        if (isset($data['theme'])) $profile->active_site_theme = $data['theme'];
        if (isset($data['sound_enabled'])) $profile->sound_enabled = $data['sound_enabled'];
        if (array_key_exists('avatar',$data)) $profile->avatar = $data['avatar'];
        if (array_key_exists('avatar_data',$data)) $profile->avatar_data = $data['avatar_data'];
        if (isset($data['active_cover'])) $profile->active_profile_cover = $data['active_cover'];
        if (isset($data['bot_difficulty'])) $profile->bot_difficulty = $data['bot_difficulty'];
        if (array_key_exists('ui_preferences',$data)) $profile->ui_preferences = $data['ui_preferences'];
        if (isset($data['pasha_style'])) $profile->pasha_style = $data['pasha_style'];
        $profile->save();

        return response()->json(['ok' => true, 'message' => 'تم تحديث الملف الشخصي', 'user' => $request->user()->fresh('profile')->publicProfile()]);
    }

    public function wallet(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'ok' => true,
            'wallet' => $this->walletPayload($user),
            'transactions' => $user->walletTransactions()->latest()->limit(100)->get()->map(fn ($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'amount' => (string) $tx->amount,
                'fee' => (string) $tx->fee,
                'meta' => $tx->meta,
                'created_at' => $tx->created_at?->toIso8601String(),
            ]),
            'inventory' => $user->inventoryItems()->with('storeItem')->latest()->get(),
        ]);
    }

    public function purchase(Request $request, WalletService $wallet)
    {
        $data = $request->validate(['key' => 'required|string|max:120', 'confirmed' => 'required|accepted']);
        $user = $request->user();
        $item = StoreItem::where('key', $data['key'])->where('active', true)->firstOrFail();
        if ($item->category === 'competition_ticket') {
            $denomination = (int) data_get($item->payload, 'denomination', 0);
            abort_if($denomination <= 0, 422, 'فئة التذكرة غير صحيحة.');
            try {
                DB::transaction(function () use ($user, $item, $wallet, $denomination) {
                    $wallet->debit($user, (int)$item->price, 'competition_ticket_purchase', [
                        'store_item_id'=>$item->id,'key'=>$item->key,'denomination'=>$denomination,
                    ]);
                    $ticket = CompetitionTicket::firstOrCreate(
                        ['user_id'=>$user->id,'denomination'=>$denomination],
                        ['quantity'=>0,'total_used'=>0]
                    );
                    $ticket->increment('quantity');
                    $admin = User::where('username', 'Adnan')->where('is_admin', true)->first() ?: User::where('is_admin', true)->first();
                    if ($admin && $admin->id !== $user->id && (int)$item->price > 0) {
                        $wallet->credit($admin, (int)$item->price, 'store_revenue', ['buyer_id'=>$user->id,'store_item_id'=>$item->id,'key'=>$item->key]);
                    }
                });
            } catch (\RuntimeException) {
                return response()->json(['ok'=>false,'message'=>'رصيد التوكنز غير كافٍ'], 422);
            }
            return response()->json([
                'ok'=>true,'message'=>'تم شراء تذكرة المنافسة','wallet'=>$this->walletPayload($user->fresh()),
                'tickets'=>CompetitionTicket::where('user_id',$user->id)->pluck('quantity','denomination')->map(fn($value)=>(int)$value)->all(),
            ]);
        }
        $alreadyOwned = $user->inventoryItems()->where('store_item_id', $item->id)->exists();
        if ($alreadyOwned) return response()->json(['ok' => false, 'message' => 'العنصر مملوك مسبقاً'], 409);

        try {
            $inventory = DB::transaction(function () use ($user, $item, $wallet) {
                $wallet->debit($user, (int) $item->price, 'store_purchase', [
                    'store_item_id' => $item->id,
                    'key' => $item->key,
                    'category' => $item->category,
                ]);

                $admin = User::where('username', 'Adnan')->where('is_admin', true)->first()
                    ?: User::where('is_admin', true)->first();
                if ($admin && $admin->id !== $user->id && (int) $item->price > 0) {
                    $wallet->credit($admin, (int) $item->price, 'store_revenue', [
                        'buyer_id' => $user->id,
                        'store_item_id' => $item->id,
                        'key' => $item->key,
                    ]);
                }

                // Only one cosmetic from the same category remains active.
                if (in_array($item->category, ['name_color','text_color','badge','table','pasha_style','xp_booster','card_back','name_frame','effect','emoji_pack','profile_cover'], true)) {
                    $user->inventoryItems()
                        ->whereHas('storeItem', fn ($query) => $query->where('category', $item->category))
                        ->update(['active' => false]);
                }

                $inventory = $user->inventoryItems()->create([
                    'store_item_id' => $item->id,
                    'active' => true,
                    'activated_at' => now(),
                    'expires_at' => $item->duration_days ? now()->addDays((int) $item->duration_days) : null,
                ]);

                $this->activateStoreItem($user, $item);
                return $inventory;
            });
        } catch (\RuntimeException) {
            return response()->json(['ok' => false, 'message' => 'رصيد التوكنز غير كافٍ'], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'تم الشراء والتفعيل بنجاح',
            'wallet' => $this->walletPayload($user->fresh()),
            'profile' => $user->profile?->fresh(),
            'inventory_item' => $inventory->load('storeItem'),
        ]);
    }

    public function notifications(Request $request)
    {
        return response()->json(['ok' => true, 'notifications' => $request->user()->notifications()->latest()->limit(100)->get()]);
    }

    public function markNotification(Request $request, int $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['read' => true]);
        return response()->json(['ok' => true, 'notification' => $notification]);
    }

    public function deleteNotification(Request $request, int $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }

    public function claimDaily(Request $request, WalletService $wallet)
    {
        $user = $request->user();
        $today = now()->toDateString();
        if (DailyRewardClaim::where('user_id', $user->id)->whereDate('claim_date', $today)->exists()) {
            return response()->json(['ok' => false, 'message' => 'تم استلام مكافأة اليوم مسبقاً'], 409);
        }
        $coins = 100;
        $xp = 20;
        $wallet->credit($user, $coins, 'daily_reward', ['claim_date' => $today]);
        DailyRewardClaim::create(['user_id' => $user->id, 'claim_date' => $today, 'streak' => 1, 'coins' => $coins, 'payload' => ['xp' => $xp]]);
        if ($user->profile) {
            $user->profile->increment('xp', $xp);
            $user->profile->update(['last_daily_reward_at' => now()]);
        }
        return response()->json([
            'ok' => true,
            'message' => 'تم استلام المكافأة اليومية',
            'coins' => $coins,
            'xp' => $xp,
            'wallet' => $this->walletPayload($user->fresh()),
            'profile' => $user->profile?->fresh(),
        ]);
    }

    public function claimRewardedAd(Request $request, WalletService $wallet, ProductionConfigService $productionConfig)
    {
        abort_unless($productionConfig->enabled('rewarded_ads', true), 503, 'الإعلانات المكافِئة متوقفة مؤقتًا.');
        $data = $request->validate([
            'verification_id'=>'required|string|min:8|max:190',
            'network'=>'nullable|string|max:40',
            'reward_type'=>'nullable|in:standard,double',
        ]);
        $user = $request->user();
        $today = now()->toDateString();
        $dailyCount = RewardedAdClaim::where('user_id',$user->id)->whereDate('claim_date',$today)->count();
        $dailyLimit = max(0, (int) data_get($productionConfig->flags(), 'rewarded_ads.payload.daily_limit', 5));
        if ($dailyLimit === 0 || $dailyCount >= $dailyLimit) return response()->json(['ok'=>false,'message'=>'وصلت إلى الحد اليومي للإعلانات المكافِئة.'],429);
        if (RewardedAdClaim::where('verification_id',$data['verification_id'])->exists()) {
            return response()->json(['ok'=>false,'message'=>'تم استخدام إثبات الإعلان مسبقاً.'],409);
        }
        $multiplier = ($data['reward_type'] ?? 'standard') === 'double' ? 2 : 1;
        $tokens = 50 * $multiplier;
        $xp = 15 * $multiplier;
        DB::transaction(function () use ($user,$wallet,$data,$today,$tokens,$xp) {
            $wallet->credit($user,$tokens,'rewarded_ad',['verification_id'=>$data['verification_id']]);
            RewardedAdClaim::create([
                'user_id'=>$user->id,'claim_date'=>$today,'reward_tokens'=>$tokens,'reward_xp'=>$xp,
                'network'=>$data['network'] ?? 'admob','verification_id'=>$data['verification_id'],
                'payload'=>['reward_type'=>$data['reward_type'] ?? 'standard'],
            ]);
            $user->profile?->increment('xp',$xp);
        });
        return response()->json([
            'ok'=>true,'message'=>'تمت إضافة مكافأة الإعلان','tokens'=>$tokens,'xp'=>$xp,
            'remaining'=>max(0,$dailyLimit-$dailyCount-1),'wallet'=>$this->walletPayload($user->fresh()),
            'profile'=>$user->profile?->fresh(),
        ]);
    }

    public function deleteAccount(Request $request, AccountCancellationService $cancellation)
    {
        $user = $request->user();
        abort_if($user->is_admin, 403, 'لا يمكن إلغاء حساب المدير الرئيسي.');

        $data = $request->validate([
            'password' => 'required|string|max:120',
            'confirmation' => 'sometimes|accepted',
            'reason' => 'nullable|string|max:500',
        ]);
        abort_unless(Hash::check($data['password'], $user->password), 422, 'كلمة المرور غير صحيحة.');
        $deletion = $cancellation->request($user, $data['reason'] ?? null);

        return response()->json([
            'ok' => true,
            'account_cancelled' => true,
            'grace_days' => $cancellation->graceDays(),
            'scheduled_for' => $deletion->scheduled_for?->toIso8601String(),
            'message' => 'تم إلغاء الحساب. سيُحذف نهائياً فقط إذا لم تسجل الدخول خلال 30 يوماً.',
        ]);
    }

    private function applyLoginStreak(User $user): array
    {
        $profile = $user->profile()->firstOrCreate(['user_id'=>$user->id],[
            'display_name'=>$user->username,'country_code'=>'PS','country_name'=>'Palestine'
        ]);
        $today = now()->startOfDay();
        $last = $profile->last_login_reward_at ? $profile->last_login_reward_at->copy()->startOfDay() : null;
        if ($last && $last->equalTo($today)) return ['streak'=>(int)$profile->login_streak,'pasha_awarded'=>0];
        $streak = $last && $last->equalTo($today->copy()->subDay()) ? ((int)$profile->login_streak + 1) : 1;
        $award = $streak % 3 === 0 ? 1 : 0;
        $profile->login_streak = $streak;
        $profile->last_login_reward_at = now();
        if ($award) $profile->pasha_days = (int)$profile->pasha_days + 1;
        $profile->save();
        return ['streak'=>$streak,'pasha_awarded'=>$award];
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    private function activateStoreItem(User $user, StoreItem $item): void
    {
        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->username, 'country_code' => 'PS', 'country_name' => 'Palestine']
        );
        $payload = $item->payload ?: [];

        switch ($item->category) {
            case 'pasha':
                $profile->increment('pasha_days', (int) ($item->duration_days ?: ($payload['days'] ?? 30)));
                return;
            case 'pasha_style':
                $style = (string)($payload['style'] ?? 'red');
                $profile->pasha_style = $style;
                if (isset($payload['color1'])) {
                    $profile->name_color = (string)$payload['color1'];
                    $profile->chat_color = (string)$payload['color1'];
                    $profile->text_color = (string)$payload['color1'];
                    $preferences = is_array($profile->ui_preferences) ? $profile->ui_preferences : [];
                    $preferences['accent_hex'] = (string)$payload['color1'];
                    $profile->ui_preferences = $preferences;
                }
                break;
            case 'name_color':
                if (isset($payload['color'])) $profile->name_color = (string) $payload['color'];
                $profile->name_color_expires_at = $item->duration_days ? now()->addDays((int)$item->duration_days) : null;
                $profile->active_name_frame = $payload['frame'] ?? $payload['glow'] ?? $profile->active_name_frame;
                break;
            case 'text_color':
                if (isset($payload['color'])) {
                    $profile->text_color = (string) $payload['color'];
                    $profile->chat_color = (string) $payload['color'];
                    $profile->chat_color_expires_at = $item->duration_days ? now()->addDays((int)$item->duration_days) : null;
                }
                break;
            case 'badge':
                $profile->badge = $payload['badge'] ?? $item->key;
                break;
            case 'table':
                $profile->active_table_skin = $payload['table'] ?? $item->key;
                break;
            case 'card_back':
                $profile->active_card_back = $payload['card_back'] ?? $item->key;
                break;
            case 'name_frame':
                $profile->active_name_frame = $payload['frame'] ?? $item->key;
                if (isset($payload['color'])) $profile->name_color = (string) $payload['color'];
                $profile->name_color_expires_at = $item->duration_days ? now()->addDays((int)$item->duration_days) : null;
                break;
            case 'xp_booster':
                $profile->xp_boost_multiplier = (float) ($payload['multiplier'] ?? 1.25);
                break;
            case 'effect':
                if (isset($payload['theme'])) $profile->active_site_theme = (string) $payload['theme'];
                else $profile->active_effect = $payload['effect'] ?? $item->key;
                break;
            case 'profile_cover':
                $profile->active_profile_cover = $payload['cover'] ?? $item->key;
                break;
        }
        $profile->save();
    }

    /** @return array<string,mixed> */
    private function dailyPackPayload(int $userId): array
    {
        $claim = DailyPackClaim::where('user_id', $userId)->latest('claim_date')->first();
        return [
            'available'=>!$claim || !$claim->claim_date?->isToday(),
            'last_opened'=>$claim?->claim_date?->toDateString(),
            'last_reward'=>data_get($claim?->payload, 'label_ar'),
        ];
    }

    /** @return array<string,mixed> */
    private function walletPayload(User $user): array
    {
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['tokens' => 50, 'gems' => 0]);
        return [
            'id' => $wallet->id,
            'tokens' => (string) $wallet->tokens,
            'tokens_formatted' => number_format((int) $wallet->tokens),
            'gems' => (string) $wallet->gems,
        ];
    }
}
