<?php

namespace App\Services\WarqnaPro;

use App\Models\{CompetitionTicket,DailyPackClaim,StoreItem,User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailyPackService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<string,mixed> */
    public function open(User $user): array
    {
        $today = now()->toDateString();
        if (DailyPackClaim::where('user_id', $user->id)->whereDate('claim_date', $today)->exists()) {
            throw new RuntimeException('تم فتح حزمة اليوم مسبقاً.');
        }

        $rewards = [
            ['weight'=>20,'type'=>'name_color','value'=>'#facc15','duration_hours'=>24,'label_ar'=>'لون اسم ذهبي ليوم واحد'],
            ['weight'=>18,'type'=>'chat_color','value'=>'#22d3ee','duration_hours'=>24,'label_ar'=>'لون كتابة سماوي ليوم واحد'],
            ['weight'=>18,'type'=>'xp_booster','value'=>'1.5','duration_hours'=>6,'label_ar'=>'مسرّع خبرة ×1.5 لمدة 6 ساعات'],
            ['weight'=>14,'type'=>'table','value'=>'table_v173_royal_01','duration_hours'=>48,'label_ar'=>'طاولة الزمرد الملكي ليومين'],
            ['weight'=>10,'type'=>'table','value'=>'table_v173_showcase_01','duration_hours'=>72,'label_ar'=>'طاولة الأسد الملكي لمدة 3 أيام'],
            ['weight'=>10,'type'=>'tokens','value'=>'250','duration_hours'=>0,'label_ar'=>'250 توكن مجاني'],
            ['weight'=>7,'type'=>'ticket','value'=>'500','duration_hours'=>0,'label_ar'=>'تذكرة منافسة بقيمة 500 توكن'],
            ['weight'=>3,'type'=>'ticket','value'=>'2000','duration_hours'=>0,'label_ar'=>'تذكرة منافسة بقيمة 2,000 توكن'],
        ];
        $reward = $this->weighted($rewards);

        DB::transaction(function () use ($user, $today, $reward) {
            $profile = $user->profile()->firstOrCreate(
                ['user_id'=>$user->id],
                ['display_name'=>$user->username,'country_code'=>'PS','country_name'=>'Palestine']
            );
            $expires = ($reward['duration_hours'] ?? 0) > 0 ? now()->addHours((int)$reward['duration_hours']) : null;
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
                    $item = StoreItem::where('key', $reward['value'])->first();
                    if ($item) {
                        $user->inventoryItems()->create([
                            'store_item_id'=>$item->id,
                            'active'=>true,
                            'activated_at'=>now(),
                            'expires_at'=>$expires,
                        ]);
                        $profile->active_table_skin = $reward['value'];
                        $profile->save();
                    }
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
                'duration_hours'=>(int)($reward['duration_hours'] ?? 0),
                'expires_at'=>$expires,
                'payload'=>$reward,
            ]);
        });

        return $reward;
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
