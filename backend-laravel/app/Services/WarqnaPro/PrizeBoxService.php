<?php

namespace App\Services\WarqnaPro;

use App\Models\{CompetitionTicket, InventoryItem, PrizeBox, StoreItem, User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PrizeBoxService
{
    public const DAILY_LIMIT = 4;

    /** @var array<int,string> */
    public const BOX_KEYS = [
        'crimson_lion',
        'emerald_eagle',
        'bronze_dragon',
        'obsidian',
        'royal_amethyst',
        'diamond_phoenix',
    ];

    public function __construct(private readonly WalletService $wallet) {}

    /**
     * Awards one outcome-aware box for every completed game, up to the daily
     * limit. A normal loss yields a simple box, a normal win a strong box,
     * a competition loss an epic box, and a competition win a legendary box.
     */
    public function awardForCompletedGame(
        User $user,
        string $sourceKey,
        ?string $gameKey = null,
        string $mode = 'normal',
        bool $won = false,
    ): ?PrizeBox {
        $normalizedSource = trim($sourceKey);
        if ($normalizedSource === '') throw new RuntimeException('Prize box source key is required.');

        return DB::transaction(function () use ($user,$normalizedSource,$gameKey,$mode,$won) {
            $existing = PrizeBox::where('user_id',$user->id)->where('source_key',$normalizedSource)->lockForUpdate()->first();
            if ($existing) return $existing;

            $today = now()->toDateString();
            $todayBoxes = PrizeBox::where('user_id',$user->id)->whereDate('awarded_date',$today)->lockForUpdate()->orderBy('id')->get();
            if ($todayBoxes->count() >= self::DAILY_LIMIT) return null;

            $competition = in_array($mode, ['tournament','sponsored','seasonal','competition'], true);
            if ($competition && $won) {
                $boxKey = 'diamond_phoenix';
                $tier = 'legendary';
            } elseif ($competition) {
                $boxKey = 'royal_amethyst';
                $tier = 'epic';
            } elseif ($won) {
                $strong = ['emerald_eagle','bronze_dragon'];
                $boxKey = $strong[abs((int)crc32($normalizedSource)) % count($strong)];
                $tier = 'strong';
            } else {
                $simple = ['crimson_lion','obsidian'];
                $boxKey = $simple[abs((int)crc32($normalizedSource)) % count($simple)];
                $tier = 'simple';
            }

            return PrizeBox::create([
                'user_id'=>$user->id,
                'box_key'=>$boxKey,
                'source_type'=>$competition ? 'competition_complete' : 'game_complete',
                'source_key'=>$normalizedSource,
                'awarded_date'=>$today,
                'payload'=>[
                    'game_key'=>$gameKey,'mode'=>$mode,'won'=>$won,'tier'=>$tier,
                    'daily_sequence'=>$todayBoxes->count()+1,'daily_limit'=>self::DAILY_LIMIT,'version'=>'V0.3.1',
                ],
            ]);
        });
    }

    /** Backward-compatible alias used by older callers. */
    public function awardForWin(User $user, string $sourceKey, ?string $gameKey = null): ?PrizeBox
    {
        return $this->awardForCompletedGame($user,$sourceKey,$gameKey,'normal',true);
    }

    /** @return array<string,mixed> */
    public function center(User $user): array
    {
        $this->purgeExpiredRewards($user);
        $today = now()->toDateString();
        $boxes = PrizeBox::where('user_id', $user->id)
            ->whereDate('awarded_date', $today)
            ->latest('id')
            ->get()
            ->map(fn (PrizeBox $box) => $this->boxPayload($box))
            ->values()
            ->all();

        return [
            'boxes' => $boxes,
            'earned_today' => count($boxes),
            'remaining_today' => max(0, self::DAILY_LIMIT - count($boxes)),
            'daily_limit' => self::DAILY_LIMIT,
            'box_keys' => self::BOX_KEYS,
            'possible_rewards' => self::catalog(),
        ];
    }

    /** @return array<string,mixed> */
    public function open(User $user, PrizeBox $box, ?array $forcedReward = null): array
    {
        $this->purgeExpiredRewards($user);
        if ((int) $box->user_id !== (int) $user->id) {
            throw new RuntimeException('لا يمكنك فتح هذا الصندوق.');
        }

        return DB::transaction(function () use ($user, $box, $forcedReward) {
            /** @var PrizeBox|null $locked */
            $locked = PrizeBox::whereKey($box->id)->lockForUpdate()->first();
            if (!$locked || (int) $locked->user_id !== (int) $user->id) {
                throw new RuntimeException('صندوق الجوائز غير موجود.');
            }
            if ($locked->opened_at) {
                throw new RuntimeException('تم فتح صندوق الجوائز مسبقاً.');
            }

            $reward = $forcedReward ?: $this->randomRewardForBox((string)$locked->box_key);
            $reward = $this->normalizeReward($reward);
            $expiresAt = $reward['duration_hours'] > 0 ? now()->addHours($reward['duration_hours']) : null;
            $reward['opened_at'] = now()->toIso8601String();
            $reward['expires_at'] = $expiresAt?->toIso8601String();

            $inventoryItem = $this->applyReward($user, $reward, $expiresAt, $locked);

            $locked->forceFill([
                'opened_at' => now(),
                'reward_type' => $reward['type'],
                'reward_key' => (string) $reward['value'],
                'duration_hours' => $reward['duration_hours'],
                'expires_at' => $expiresAt,
                'payload' => array_merge($locked->payload ?? [], [
                    'reward' => $reward,
                    'version' => 'V0.3.1',
                ]),
            ])->save();

            $reward['inventory_item'] = $inventoryItem;

            return [
                'box' => $this->boxPayload($locked->fresh()),
                'reward' => $reward,
                'inventory' => $user->inventoryItems()->with('storeItem')->latest()->limit(200)->get(),
                'tickets' => CompetitionTicket::where('user_id', $user->id)
                    ->pluck('quantity', 'denomination')
                    ->map(fn ($quantity) => (int) $quantity)
                    ->all(),
                'wallet' => $this->walletPayload($user->fresh()),
                'profile' => $user->fresh()->publicProfile(),
            ];
        });
    }

    public function purgeExpiredRewards(User $user): void
    {
        $expired = $user->inventoryItems()
            ->with('storeItem')
            ->where('active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($user, $expired) {
            $profile = $user->profile()->lockForUpdate()->first();
            foreach ($expired as $inventory) {
                $store = $inventory->storeItem;
                $payload = is_array($store?->payload) ? $store->payload : [];
                $source = (string) ($payload['source'] ?? '');
                if (!in_array($source, ['daily_prize_box_v02', 'daily_pack'], true)) {
                    continue;
                }

                $inventory->forceFill(['active' => false])->save();
                if (!$profile || !$store) {
                    continue;
                }

                $value = (string) ($payload['value'] ?? '');
                if ($store->category === 'text_color' && (string) $profile->chat_color === $value) {
                    $profile->chat_color = '#ffffff';
                    $profile->text_color = '#ffffff';
                    $profile->chat_color_expires_at = null;
                } elseif ($store->category === 'name_color' && (string) $profile->name_color === $value) {
                    $profile->name_color = '#facc15';
                    $profile->name_color_expires_at = null;
                } elseif ($store->category === 'cover' && (string) $profile->active_profile_cover === $value) {
                    $profile->active_profile_cover = 'cover_royal_gold';
                }
            }
            $profile?->save();
        });
    }

    /** @return array<int,array<string,mixed>> */
    public static function catalog(): array
    {
        return [
            ['weight' => 15, 'type' => 'pasha_day', 'value' => '1', 'duration_hours' => 24, 'rarity' => 'legendary', 'icon' => '👑', 'label_ar' => 'يوم باشا', 'store_item_key' => 'daily_prize_pasha_day_v02'],
            ['weight' => 18, 'type' => 'writing_color', 'value' => '#22d3ee', 'duration_hours' => 24, 'rarity' => 'rare', 'icon' => '✍️', 'label_ar' => 'لون كتابة لمدة يوم', 'store_item_key' => 'daily_pack_chat_cyan_24h_v176'],
            ['weight' => 18, 'type' => 'player_color', 'value' => '#facc15', 'duration_hours' => 24, 'rarity' => 'rare', 'icon' => '🎨', 'label_ar' => 'لون لاعب لمدة يوم', 'store_item_key' => 'daily_pack_name_gold_24h_v176'],
            ['weight' => 14, 'type' => 'profile_cover', 'value' => 'cover_v02_royal', 'duration_hours' => 72, 'rarity' => 'epic', 'icon' => '🖼️', 'label_ar' => 'غلاف شخصي لمدة 3 أيام', 'store_item_key' => 'daily_prize_cover_v02'],
            ['weight' => 25, 'type' => 'tokens', 'value' => 'random_50_1000', 'duration_hours' => 0, 'rarity' => 'common', 'icon' => '🪙', 'label_ar' => 'توكنز عشوائية'],
            ['weight' => 8, 'type' => 'ticket', 'value' => '200', 'duration_hours' => 0, 'rarity' => 'epic', 'icon' => '🎟️', 'label_ar' => 'تذكرة مسابقة 200'],
            ['weight' => 2, 'type' => 'ticket', 'value' => '500', 'duration_hours' => 0, 'rarity' => 'legendary', 'icon' => '🎟️', 'label_ar' => 'تذكرة مسابقة 500'],
        ];
    }

    /** @return array<string,mixed> */
    private function randomRewardForBox(string $boxKey): array
    {
        $items = self::catalog();
        $allowed = match ($boxKey) {
            'crimson_lion','obsidian' => ['tokens','writing_color','player_color'],
            'emerald_eagle','bronze_dragon' => ['tokens','writing_color','player_color','ticket','profile_cover'],
            'royal_amethyst' => ['ticket','profile_cover','pasha_day','tokens','writing_color','player_color'],
            'diamond_phoenix' => ['ticket','profile_cover','pasha_day','tokens'],
            default => array_values(array_unique(array_column($items,'type'))),
        };
        $items = array_values(array_filter($items, fn(array $item)=>in_array($item['type'],$allowed,true)));
        if ($boxKey === 'diamond_phoenix') {
            foreach ($items as &$item) {
                if ($item['type'] === 'ticket' && (int)$item['value'] === 500) $item['weight'] = 28;
                if ($item['type'] === 'pasha_day') $item['weight'] = 24;
                if ($item['type'] === 'tokens') $item['weight'] = 22;
            }
            unset($item);
        }
        $total = array_sum(array_column($items,'weight'));
        $pick = random_int(1,max(1,$total));
        foreach ($items as $item) {
            $pick -= (int)$item['weight'];
            if ($pick <= 0) return $item;
        }
        return $items[0];
    }

    /** @param array<string,mixed> $reward @return array<string,mixed> */
    private function normalizeReward(array $reward): array
    {
        $type = (string) ($reward['type'] ?? 'tokens');
        if ($type === 'tokens') {
            $requested = (string) ($reward['value'] ?? 'random_50_1000');
            $tokens = ctype_digit($requested) ? (int) $requested : random_int(1, 20) * 50;
            $tokens = max(50, min(1000, (int) (round($tokens / 50) * 50)));
            $reward['value'] = (string) $tokens;
            $reward['label_ar'] = $tokens.' توكن مجاني';
        }
        if ($type === 'ticket') {
            $denomination = (int)($reward['value'] ?? 200);
            $reward['value'] = (string)(in_array($denomination,[50,100,200,500,1000],true) ? $denomination : 200);
            $reward['label_ar'] = 'تذكرة مسابقة '.$reward['value'];
        }
        $reward['duration_hours'] = max(0, (int) ($reward['duration_hours'] ?? 0));
        $reward['rarity'] = (string) ($reward['rarity'] ?? 'common');
        $reward['icon'] = (string) ($reward['icon'] ?? '🎁');
        $reward['label_ar'] = (string) ($reward['label_ar'] ?? 'مكافأة صندوق الجوائز');
        return $reward;
    }

    /**
     * @param array<string,mixed> $reward
     * @return array<string,mixed>|null
     */
    private function applyReward(User $user, array &$reward, $expiresAt, PrizeBox $box): ?array
    {
        $profile = $user->profile()->lockForUpdate()->firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->username, 'country_code' => 'PS', 'country_name' => country_name('PS')]
        );

        $inventoryPayload = null;
        $storeItem = $this->ensureRewardStoreItem($reward);
        if ($storeItem) {
            $user->inventoryItems()
                ->where('store_item_id', $storeItem->id)
                ->where('active', true)
                ->update(['active' => false]);

            $inventory = $user->inventoryItems()->create([
                'store_item_id' => $storeItem->id,
                'active' => true,
                'activated_at' => now(),
                'expires_at' => $expiresAt,
            ]);
            $inventoryPayload = $this->inventoryPayload($inventory->load('storeItem'));
            $reward['store_item_key'] = $storeItem->key;
        }

        switch ($reward['type']) {
            case 'pasha_day':
                $profile->pasha_style = 'red';
                $profile->pasha_days = (int) $profile->pasha_days + 1;
                $profile->save();
                break;
            case 'writing_color':
                $profile->chat_color = (string) $reward['value'];
                $profile->text_color = (string) $reward['value'];
                $profile->chat_color_expires_at = $expiresAt;
                $profile->save();
                break;
            case 'player_color':
                $profile->name_color = (string) $reward['value'];
                $profile->name_color_expires_at = $expiresAt;
                $profile->save();
                break;
            case 'profile_cover':
                $profile->active_profile_cover = (string) $reward['value'];
                $profile->save();
                break;
            case 'tokens':
                $this->wallet->credit($user, (int) $reward['value'], 'prize_box_v02', ['prize_box_id' => $box->id]);
                break;
            case 'ticket':
                $ticket = CompetitionTicket::firstOrCreate(
                    ['user_id' => $user->id, 'denomination' => (int)$reward['value']],
                    ['quantity' => 0, 'total_used' => 0]
                );
                $ticket->increment('quantity');
                break;
        }

        return $inventoryPayload;
    }

    /** @param array<string,mixed> $reward */
    private function ensureRewardStoreItem(array $reward): ?StoreItem
    {
        if (!in_array($reward['type'], ['pasha_day', 'writing_color', 'player_color', 'profile_cover'], true)) {
            return null;
        }

        $key = (string) ($reward['store_item_key'] ?? 'daily_prize_'.$reward['type'].'_v02');
        $category = match ($reward['type']) {
            'pasha_day' => 'pasha',
            'writing_color' => 'text_color',
            'player_color' => 'name_color',
            'profile_cover' => 'cover',
            default => 'reward',
        };

        return StoreItem::updateOrCreate(
            ['key' => $key],
            [
                'name' => ['ar' => $reward['label_ar'], 'en' => $this->englishRewardName((string) $reward['type'])],
                'category' => $category,
                'price' => 0,
                'duration_days' => null,
                'payload' => [
                    'source' => 'daily_prize_box_v02',
                    'value' => $reward['value'],
                    'duration_hours' => $reward['duration_hours'],
                    'rarity' => $reward['rarity'],
                    'icon' => $reward['icon'],
                    'asset' => $this->rewardAsset((string) $reward['type']),
                ],
                'active' => true,
            ]
        );
    }

    private function englishRewardName(string $type): string
    {
        return match ($type) {
            'pasha_day' => 'One Pasha Day',
            'writing_color' => 'Writing Color for One Day',
            'player_color' => 'Player Color for One Day',
            'profile_cover' => 'Profile Cover for Three Days',
            default => 'Prize Box Reward',
        };
    }

    private function rewardAsset(string $type): string
    {
        return match ($type) {
            'pasha_day' => 'assets/images/v02/rewards/pasha_day.png',
            'writing_color' => 'assets/images/v02/rewards/writing_color.png',
            'player_color' => 'assets/images/v02/rewards/player_color.png',
            'profile_cover' => 'assets/images/v02/rewards/profile_cover.png',
            'tokens' => 'assets/images/v02/rewards/tokens.png',
            'ticket' => 'assets/images/v02/rewards/ticket_200.png',
            default => 'assets/images/v02/rewards/tokens.png',
        };
    }

    /** @return array<string,mixed> */
    public function boxPayload(PrizeBox $box): array
    {
        $reward = data_get($box->payload, 'reward');
        return [
            'id' => $box->id,
            'box_key' => $box->box_key,
            'source_type' => $box->source_type,
            'source_key' => $box->source_key,
            'awarded_date' => $box->awarded_date?->format('Y-m-d'),
            'opened_at' => $box->opened_at?->toIso8601String(),
            'reward_type' => $box->reward_type,
            'reward_key' => $box->reward_key,
            'duration_hours' => (int) $box->duration_hours,
            'expires_at' => $box->expires_at?->toIso8601String(),
            'reward' => is_array($reward) ? $reward : null,
            'created_at' => $box->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string,mixed> */
    private function inventoryPayload(InventoryItem $inventory): array
    {
        return [
            'id' => $inventory->id,
            'active' => (bool) $inventory->active,
            'activated_at' => $inventory->activated_at?->toIso8601String(),
            'expires_at' => $inventory->expires_at?->toIso8601String(),
            'store_item' => $inventory->storeItem ? [
                'id' => $inventory->storeItem->id,
                'key' => $inventory->storeItem->key,
                'name' => $inventory->storeItem->name,
                'category' => $inventory->storeItem->category,
                'payload' => $inventory->storeItem->payload,
            ] : null,
        ];
    }

    /** @return array<string,string> */
    private function walletPayload(User $user): array
    {
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['tokens' => 50, 'gems' => 0]);
        return ['tokens' => (string) $wallet->tokens, 'gems' => (string) $wallet->gems];
    }
}
