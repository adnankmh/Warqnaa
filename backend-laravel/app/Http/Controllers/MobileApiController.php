<?php

namespace App\Http\Controllers;

use App\Models\{Club,DailyRewardClaim,Game,Profile,Room,StoreItem,Tournament,User,Wallet};
use App\Services\Wallet\WalletService;
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
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'token' => $token,
            'user' => $user->fresh('profile')->publicProfile(),
            'wallet' => $this->walletPayload($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['login' => 'required|string|max:190', 'password' => 'required|string|max:120']);
        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!Auth::attempt([$field => $data['login'], 'password' => $data['password']])) {
            return response()->json(['ok' => false, 'message' => 'بيانات الدخول غير صحيحة'], 422);
        }
        $user = $request->user();
        if ($user->is_banned) return response()->json(['ok' => false, 'message' => 'الحساب موقوف'], 403);
        $user->tokens()->where('name', 'mobile')->delete();
        $user->update(['last_seen_at' => now()]);
        return response()->json([
            'ok' => true,
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user->load('profile')->publicProfile(),
            'wallet' => $this->walletPayload($user),
        ]);
    }

    public function bootstrap(Request $request)
    {
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
            ],
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
        $alreadyOwned = $user->inventoryItems()->where('store_item_id', $item->id)->exists();
        if ($alreadyOwned) return response()->json(['ok' => false, 'message' => 'العنصر مملوك مسبقاً'], 409);

        try {
            $inventory = DB::transaction(function () use ($user, $item, $wallet) {
                $wallet->debit($user, (int) $item->price, 'store_purchase', [
                    'store_item_id' => $item->id,
                    'key' => $item->key,
                    'category' => $item->category,
                ]);

                // Only one cosmetic from the same category remains active.
                if (in_array($item->category, ['name_color','text_color','badge','table','xp_booster','card_back','name_frame','effect','emoji_pack'], true)) {
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
        $coins = 2500;
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
            case 'name_color':
                if (isset($payload['color'])) $profile->name_color = (string) $payload['color'];
                $profile->active_name_frame = $payload['frame'] ?? $payload['glow'] ?? $profile->active_name_frame;
                break;
            case 'text_color':
                if (isset($payload['color'])) {
                    $profile->text_color = (string) $payload['color'];
                    $profile->chat_color = (string) $payload['color'];
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
                break;
            case 'xp_booster':
                $profile->xp_boost_multiplier = (float) ($payload['multiplier'] ?? 1.25);
                break;
            case 'effect':
                if (isset($payload['theme'])) $profile->active_site_theme = (string) $payload['theme'];
                else $profile->active_effect = $payload['effect'] ?? $item->key;
                break;
        }
        $profile->save();
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
