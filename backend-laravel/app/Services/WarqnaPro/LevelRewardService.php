<?php

namespace App\Services\WarqnaPro;

use App\Models\{CompetitionTicket, LevelRewardClaim, User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class LevelRewardService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<int,array<string,mixed>> */
    public function grantRange(User $user, int $oldLevel, int $newLevel): array
    {
        $granted = [];
        for ($level=max(2,$oldLevel+1); $level<=$newLevel; $level++) {
            $reward = DB::transaction(function () use ($user,$level) {
                if (LevelRewardClaim::where('user_id',$user->id)->where('level',$level)->lockForUpdate()->exists()) return null;
                $reward=$this->rewardFor($level); $profile=$user->profile()->lockForUpdate()->first();
                if ($reward['type']==='tokens') $this->wallet->credit($user,(int)$reward['value'],'level_reward',['level'=>$level]);
                elseif ($reward['type']==='pasha_days' && $profile) { $profile->pasha_days=(int)$profile->pasha_days+(int)$reward['value']; $profile->save(); }
                elseif ($reward['type']==='ticket') { $ticket=CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>(int)$reward['value']],['quantity'=>0,'total_used'=>0]); $ticket->increment('quantity'); }
                elseif ($reward['type']==='xp_booster' && $profile) { $profile->xp_boost_multiplier=2.0; $profile->xp_boost_expires_at=now()->addHours((int)$reward['value']); $profile->save(); }
                elseif ($reward['type']==='prize_box') app(PrizeBoxService::class)->awardForWin($user,'level:'.$level,null);
                LevelRewardClaim::create(['user_id'=>$user->id,'level'=>$level,'reward_type'=>$reward['type'],'reward_value'=>(string)$reward['value'],'payload'=>$reward]);
                return $reward+['level'=>$level];
            });
            if ($reward) $granted[]=$reward;
        }
        return $granted;
    }

    /** @return array<string,mixed> */
    public function rewardFor(int $level): array
    {
        return match (true) {
            $level === 100 => ['type'=>'pasha_days','value'=>30,'label'=>'30 يوم باشا'],
            $level % 25 === 0 => ['type'=>'pasha_days','value'=>7,'label'=>'7 أيام باشا'],
            $level % 10 === 0 => ['type'=>'ticket','value'=>200,'label'=>'تذكرة منافسة 200'],
            $level % 5 === 0 => ['type'=>'prize_box','value'=>'level_box','label'=>'صندوق جوائز إضافي'],
            $level % 3 === 0 => ['type'=>'xp_booster','value'=>3,'label'=>'مسرّع خبرة 3 ساعات'],
            default => ['type'=>'tokens','value'=>min(1000,max(50,$level*20)),'label'=>'توكنز'],
        };
    }
}
