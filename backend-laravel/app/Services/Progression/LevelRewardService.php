<?php

namespace App\Services\Progression;

use App\Models\{CompetitionTicket,LevelRewardClaim,PrizeBox,User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class LevelRewardService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<int,array<string,mixed>> */
    public function grantRange(User $user, int $oldLevel, int $newLevel): array
    {
        $granted = [];
        for ($level = max(2, $oldLevel + 1); $level <= $newLevel; $level++) {
            $reward = $this->grant($user, $level);
            if ($reward !== null) $granted[] = $reward;
        }
        return $granted;
    }

    /** @return array<string,mixed>|null */
    public function grant(User $user, int $level): ?array
    {
        return DB::transaction(function () use ($user, $level) {
            $existing = LevelRewardClaim::where('user_id', $user->id)->where('level', $level)->lockForUpdate()->first();
            if ($existing) return null;

            $reward = $this->rewardFor($level);
            $tokens = min(1000, 75 + ($level * 15));
            $this->wallet->credit($user, $tokens, 'level_reward', ['level' => $level, 'version' => 'V0.2.5']);
            $profile = $user->profile()->lockForUpdate()->firstOrCreate([], ['display_name'=>$user->username,'country_code'=>'PS','country_name'=>country_name('PS')]);

            switch ($reward['type']) {
                case 'pasha_days':
                    $profile->pasha_days = (int)$profile->pasha_days + (int)$reward['amount'];
                    break;
                case 'ticket':
                    $ticket = CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>200], ['quantity'=>0,'total_used'=>0]);
                    $ticket->increment('quantity', (int)$reward['amount']);
                    break;
                case 'booster_hours':
                    $base = $profile->xp_boost_expires_at && now()->lt($profile->xp_boost_expires_at) ? $profile->xp_boost_expires_at : now();
                    $profile->xp_boost_multiplier = max(2, (float)($profile->xp_boost_multiplier ?? 1));
                    $profile->xp_boost_expires_at = $base->copy()->addHours((int)$reward['amount']);
                    break;
                case 'name_color_days':
                    $profile->name_color = (string)$reward['value'];
                    $profile->name_color_expires_at = now()->addDays((int)$reward['amount']);
                    break;
                case 'chat_color_days':
                    $profile->chat_color = (string)$reward['value'];
                    $profile->text_color = (string)$reward['value'];
                    $profile->chat_color_expires_at = now()->addDays((int)$reward['amount']);
                    break;
                case 'table_days':
                    $prefs = is_array($profile->ui_preferences) ? $profile->ui_preferences : [];
                    $prefs['temporary_table'] = ['key'=>(string)$reward['value'],'expires_at'=>now()->addDays((int)$reward['amount'])->toIso8601String()];
                    $profile->ui_preferences = $prefs;
                    break;
                case 'prize_box':
                    PrizeBox::firstOrCreate(
                        ['user_id'=>$user->id,'source_key'=>'level:'.$level],
                        ['box_key'=>(string)$reward['value'],'source_type'=>'level_reward','awarded_date'=>now()->toDateString(),'payload'=>['level'=>$level,'version'=>'V0.2.5']]
                    );
                    break;
            }
            $profile->save();

            $payload = $reward + ['tokens'=>$tokens,'level'=>$level];
            LevelRewardClaim::create([
                'user_id'=>$user->id,'level'=>$level,'reward_type'=>$reward['type'],
                'amount'=>(int)($reward['amount'] ?? 1),'reward_payload'=>$payload,
            ]);
            return $payload;
        });
    }

    /** @return array<string,mixed> */
    public function rewardFor(int $level): array
    {
        return match (true) {
            $level % 25 === 0 => ['type'=>'pasha_days','amount'=>3,'icon'=>'👑','label_ar'=>'3 أيام باشا'],
            $level % 20 === 0 => ['type'=>'prize_box','amount'=>1,'value'=>'diamond_phoenix','icon'=>'🎁','label_ar'=>'صندوق ألماسي'],
            $level % 15 === 0 => ['type'=>'table_days','amount'=>7,'value'=>'table_v025_level_royal','icon'=>'🎴','label_ar'=>'طاولة ملكية 7 أيام'],
            $level % 10 === 0 => ['type'=>'pasha_days','amount'=>1,'icon'=>'👑','label_ar'=>'يوم باشا'],
            $level % 7 === 0 => ['type'=>'ticket','amount'=>1,'icon'=>'🎟️','label_ar'=>'تذكرة مسابقة 200'],
            $level % 5 === 0 => ['type'=>'prize_box','amount'=>1,'value'=>'royal_amethyst','icon'=>'📦','label_ar'=>'صندوق إضافي'],
            $level % 4 === 0 => ['type'=>'booster_hours','amount'=>6,'icon'=>'⚡','label_ar'=>'مسرّع نقاط 6 ساعات'],
            $level % 3 === 0 => ['type'=>'chat_color_days','amount'=>3,'value'=>'#22d3ee','icon'=>'✍️','label_ar'=>'لون كتابة 3 أيام'],
            default => ['type'=>'name_color_days','amount'=>2,'value'=>'#facc15','icon'=>'🎨','label_ar'=>'لون لاعب يومان'],
        };
    }
}
