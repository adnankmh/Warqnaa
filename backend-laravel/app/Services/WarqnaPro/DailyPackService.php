<?php

namespace App\Services\WarqnaPro;

use App\Models\{CompetitionTicket,DailyPackClaim,InventoryItem,StoreItem,User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailyPackService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<string,mixed> */
    public function open(User $user, ?array $forcedReward = null): array
    {
        $today = now()->toDateString();
        if (DailyPackClaim::where('user_id', $user->id)->whereDate('claim_date', $today)->exists()) {
            throw new RuntimeException('تم فتح حزمة اليوم مسبقاً.');
        }

        $reward = $forcedReward ?: $this->weighted(self::catalog());
        $reward['opened_at'] = now()->toIso8601String();
        $durationHours = max(0, (int)($reward['duration_hours'] ?? 0));
        $expires = $durationHours > 0 ? now()->addHours($durationHours) : null;
        $reward['expires_at'] = $expires?->toIso8601String();
        $inventoryPayload = null;

        DB::transaction(function () use ($user, $today, &$reward, $expires, $durationHours, &$inventoryPayload) {
            $profile = $user->profile()->firstOrCreate(
                ['user_id'=>$user->id],
                ['display_name'=>$user->username,'country_code'=>'PS','country_name'=>'Palestine']
            );

            $storeItem = $this->ensureRewardStoreItem($reward);
            if ($storeItem) {
                $user->inventoryItems()
                    ->whereHas('storeItem', fn ($query) => $query->where('category', $storeItem->category))
                    ->update(['active'=>false]);
                $inventory = $user->inventoryItems()->create([
                    'store_item_id'=>$storeItem->id,
                    'active'=>true,
                    'activated_at'=>now(),
                    'expires_at'=>$expires,
                ]);
                $inventoryPayload = $this->inventoryPayload($inventory->load('storeItem'));
                $reward['store_item_key'] = $storeItem->key;
            }

            switch ($reward['type']) {
                case 'name_color':
                    $profile->name_color = (string)$reward['value'];
                    $profile->name_color_expires_at = $expires;
                    $profile->save();
                    break;
                case 'chat_color':
                    $profile->chat_color = (string)$reward['value'];
                    $profile->text_color = (string)$reward['value'];
                    $profile->chat_color_expires_at = $expires;
                    $profile->save();
                    break;
                case 'xp_booster':
                    $profile->xp_boost_multiplier = (float)$reward['value'];
                    $profile->xp_boost_expires_at = $expires;
                    $profile->save();
                    break;
                case 'table':
                    $profile->active_table_skin = (string)$reward['value'];
                    $profile->save();
                    break;
                case 'tokens':
                    $this->wallet->credit($user, (int)$reward['value'], 'daily_pack', ['claim_date'=>$today]);
                    break;
                case 'ticket':
                    $ticket = CompetitionTicket::firstOrCreate(
                        ['user_id'=>$user->id,'denomination'=>(int)$reward['value']],
                        ['quantity'=>0,'total_used'=>0]
                    );
                    $ticket->increment('quantity');
                    break;
            }

            DailyPackClaim::create([
                'user_id'=>$user->id,
                'claim_date'=>$today,
                'reward_type'=>$reward['type'],
                'reward_key'=>(string)$reward['value'],
                'duration_hours'=>$durationHours,
                'expires_at'=>$expires,
                'payload'=>$reward,
            ]);
        });

        $reward['inventory_item'] = $inventoryPayload;
        return $reward;
    }

    /** @return array<int,array<string,mixed>> */
    public static function catalog(): array
    {
        return [
            ['weight'=>18,'type'=>'name_color','value'=>'#facc15','store_item_key'=>'daily_pack_name_gold_24h_v176','duration_hours'=>24,'rarity'=>'rare','icon'=>'🎨','label_ar'=>'لون اسم ذهبي ليوم واحد'],
            ['weight'=>17,'type'=>'chat_color','value'=>'#22d3ee','store_item_key'=>'daily_pack_chat_cyan_24h_v176','duration_hours'=>24,'rarity'=>'rare','icon'=>'💬','label_ar'=>'لون كتابة سماوي ليوم واحد'],
            ['weight'=>17,'type'=>'xp_booster','value'=>'1.5','store_item_key'=>'daily_pack_xp_15x_6h_v176','duration_hours'=>6,'rarity'=>'epic','icon'=>'⚡','label_ar'=>'مسرّع خبرة ×1.5 لمدة 6 ساعات'],
            ['weight'=>13,'type'=>'table','value'=>'table_v173_royal_01','store_item_key'=>'table_v173_royal_01','duration_hours'=>48,'rarity'=>'epic','icon'=>'🎴','label_ar'=>'طاولة الزمرد الملكي ليومين'],
            ['weight'=>9,'type'=>'table','value'=>'table_v173_showcase_01','store_item_key'=>'table_v173_showcase_01','duration_hours'=>72,'rarity'=>'legendary','icon'=>'🦁','label_ar'=>'طاولة الأسد الملكي لمدة 3 أيام'],
            ['weight'=>13,'type'=>'tokens','value'=>'250','duration_hours'=>0,'rarity'=>'common','icon'=>'🪙','label_ar'=>'250 توكن مجاني'],
            ['weight'=>5,'type'=>'tokens','value'=>'2500','duration_hours'=>0,'rarity'=>'legendary','icon'=>'💰','label_ar'=>'2,500 توكن مجاني'],
            ['weight'=>6,'type'=>'ticket','value'=>'500','duration_hours'=>0,'rarity'=>'rare','icon'=>'🎟️','label_ar'=>'تذكرة منافسة بقيمة 500 توكن'],
            ['weight'=>2,'type'=>'ticket','value'=>'5000','duration_hours'=>0,'rarity'=>'legendary','icon'=>'🏆','label_ar'=>'تذكرة منافسة بقيمة 5,000 توكن'],
        ];
    }

    /** @param array<string,mixed> $reward */
    private function ensureRewardStoreItem(array $reward): ?StoreItem
    {
        $type = (string)($reward['type'] ?? '');
        if (!in_array($type, ['name_color','chat_color','xp_booster','table'], true)) return null;

        $key = (string)($reward['store_item_key'] ?? $reward['value'] ?? '');
        if ($key === '') return null;
        $existing = StoreItem::where('key', $key)->first();
        if ($existing) return $existing;

        $category = match ($type) {
            'name_color' => 'name_color',
            'chat_color' => 'text_color',
            'xp_booster' => 'xp_booster',
            default => 'table',
        };
        $payload = [
            'source'=>'daily_pack',
            'value'=>$reward['value'] ?? null,
            'duration_hours'=>(int)($reward['duration_hours'] ?? 0),
            'rarity'=>$reward['rarity'] ?? 'common',
            'icon'=>$reward['icon'] ?? '🎁',
        ];
        if ($type === 'xp_booster') $payload['multiplier'] = (float)($reward['value'] ?? 1.5);
        if ($type === 'table') $payload['asset_key'] = $reward['value'] ?? $key;

        return StoreItem::create([
            'key'=>$key,
            'name'=>['ar'=>$reward['label_ar'] ?? 'هدية الحزمة اليومية','en'=>'Daily Pack Reward'],
            'category'=>$category,
            'price'=>0,
            'duration_days'=>null,
            'payload'=>$payload,
            'active'=>true,
        ]);
    }

    /** @return array<string,mixed> */
    private function inventoryPayload(InventoryItem $inventory): array
    {
        return [
            'id'=>$inventory->id,
            'active'=>(bool)$inventory->active,
            'activated_at'=>$inventory->activated_at?->toIso8601String(),
            'expires_at'=>$inventory->expires_at?->toIso8601String(),
            'expired'=>$inventory->expires_at ? $inventory->expires_at->isPast() : false,
            'store_item'=>$inventory->storeItem ? [
                'id'=>$inventory->storeItem->id,
                'key'=>$inventory->storeItem->key,
                'name'=>$inventory->storeItem->name,
                'category'=>$inventory->storeItem->category,
                'payload'=>$inventory->storeItem->payload,
            ] : null,
        ];
    }

    /** @param array<int,array<string,mixed>> $items */
    private function weighted(array $items): array
    {
        $total = array_sum(array_column($items, 'weight'));
        $pick = random_int(1, max(1, $total));
        foreach ($items as $item) {
            $pick -= (int)$item['weight'];
            if ($pick <= 0) return $item;
        }
        return $items[0];
    }
}
