<?php

namespace App\Services\WarqnaPro;

use App\Models\{ChallengeRun, CompetitionTicket, User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Original Warqna challenge-road implementation. It does not copy another
 * product's visual assets or proprietary progression data.
 */
class ChallengeRoadService
{
    public const MAX_LIVES = 5;
    public const ALLOWED_STAGES = [10, 12, 15];

    public function __construct(private readonly WalletService $wallet) {}

    public function active(User $user): ?ChallengeRun
    {
        return ChallengeRun::where('user_id', $user->id)->where('status', 'active')->latest()->first();
    }

    public function start(User $user, string $gameKey, int $stages = 15): ChallengeRun
    {
        if (!in_array($stages, self::ALLOWED_STAGES, true)) $stages = 15;
        return DB::transaction(function () use ($user, $gameKey, $stages) {
            ChallengeRun::where('user_id', $user->id)->where('status', 'active')->update(['status'=>'failed']);
            return ChallengeRun::create([
                'user_id'=>$user->id,'game_key'=>$gameKey,'stage'=>0,'lives'=>self::MAX_LIVES,
                'stages_total'=>$stages,'status'=>'active','claimed_stages'=>[],
            ]);
        });
    }

    /** @return array<string,mixed> */
    public function record(User $user, string $gameKey, bool $won, string $matchKey): array
    {
        return DB::transaction(function () use ($user, $gameKey, $won, $matchKey) {
            $run = ChallengeRun::where('user_id',$user->id)->where('status','active')->lockForUpdate()->latest()->first();
            if (!$run || $run->game_key !== $gameKey) return ['active'=>false];
            $claimed = is_array($run->claimed_stages) ? $run->claimed_stages : [];
            if (in_array($matchKey, $claimed, true)) return ['active'=>true,'duplicate'=>true,'run'=>$this->payload($run)];

            $claimed[] = $matchKey;
            $reward = null;
            if ($won) {
                $run->stage = min((int)$run->stages_total, (int)$run->stage + 1);
                $reward = $this->rewardForStage($run->stage);
                $this->applyReward($user, $run, $reward);
                if ($run->stage >= $run->stages_total) {
                    $run->status = 'completed';
                    $run->completed_at = now();
                }
            } else {
                $run->lives = max(0, (int)$run->lives - 1);
                if ($run->lives <= 0) $run->status = 'failed';
            }
            $run->claimed_stages = array_slice($claimed, -50);
            $run->save();
            return ['active'=>true,'won'=>$won,'reward'=>$reward,'run'=>$this->payload($run->fresh())];
        });
    }

    /** @return array<string,mixed> */
    public function payload(ChallengeRun $run): array
    {
        return [
            'id'=>$run->id,'game_key'=>$run->game_key,'game'=>$run->game_key,'stage'=>(int)$run->stage,'current_stage'=>(int)$run->stage,
            'lives'=>(int)$run->lives,'lives_remaining'=>(int)$run->lives,'stages_total'=>(int)$run->stages_total,
            'status'=>$run->status,'completed_at'=>$run->completed_at?->toIso8601String(),
            'next_reward'=>$run->status === 'active' ? $this->rewardForStage(min((int)$run->stages_total, (int)$run->stage + 1)) : null,
        ];
    }

    /** @return array<string,mixed> */
    public function rewardForStage(int $stage): array
    {
        return match (true) {
            $stage % 15 === 0 => ['type'=>'pasha_days','value'=>3,'label'=>'3 أيام باشا'],
            $stage % 10 === 0 => ['type'=>'ticket','value'=>200,'label'=>'تذكرة منافسة 200'],
            $stage % 7 === 0 => ['type'=>'xp_booster','value'=>6,'label'=>'مسرّع خبرة 6 ساعات'],
            $stage % 5 === 0 => ['type'=>'prize_box','value'=>'bonus','label'=>'صندوق جوائز إضافي'],
            $stage % 3 === 0 => ['type'=>'tokens','value'=>min(1000, $stage * 50),'label'=>'توكنز'],
            default => ['type'=>'tokens','value'=>min(1000, max(50, $stage * 25)),'label'=>'توكنز'],
        };
    }

    /** @param array<string,mixed> $reward */
    private function applyReward(User $user, ChallengeRun $run, array $reward): void
    {
        $profile = $user->profile()->lockForUpdate()->first();
        $type = (string)$reward['type']; $value = $reward['value'];
        if ($type === 'tokens') $this->wallet->credit($user, (int)$value, 'challenge_stage_reward', ['run_id'=>$run->id,'stage'=>$run->stage]);
        if ($type === 'pasha_days' && $profile) { $profile->pasha_days = (int)$profile->pasha_days + (int)$value; $profile->save(); }
        if ($type === 'xp_booster' && $profile) { $profile->xp_boost_multiplier = max(2.0,(float)$profile->xp_boost_multiplier); $profile->xp_boost_expires_at = now()->addHours((int)$value); $profile->save(); }
        if ($type === 'ticket') {
            $ticket = CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>(int)$value],['quantity'=>0,'total_used'=>0]);
            $ticket->increment('quantity');
        }
        if ($type === 'prize_box') {
            app(PrizeBoxService::class)->awardForWin($user, 'challenge:'.$run->id.':'.$run->stage, $run->game_key);
        }
    }
}
