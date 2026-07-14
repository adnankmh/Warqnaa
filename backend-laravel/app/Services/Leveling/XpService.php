<?php
namespace App\Services\Leveling;

use App\Models\{CompetitionTicket, User};

class XpService
{
    public function levelForXp(int $xp): int
    {
        $level = 1;
        $remaining = max(0, $xp);
        while ($level < 200 && $remaining >= $this->requiredXp($level)) {
            $remaining -= $this->requiredXp($level);
            $level++;
        }
        return $level;
    }

    public function requiredXp(int $level): int
    {
        $safe = max(1, min(200, $level));
        $exact = config('warqna_xp_levels.'.$safe);
        if ($exact !== null) return (int) $exact;
        $level100 = (int) config('warqna_xp_levels.100', 8000000);
        return (int) round($level100 * (1.12 ** ($safe - 100)));
    }

    public function rewardForLevel(int $level): int
    {
        return match (true) {
            $level <= 10 => 50 + ($level * 90),
            $level <= 30 => 1000 + ($level * 75),
            $level <= 60 => 3000,
            default => 10000,
        };
    }

    public function award(User $user, int $xp, int $tokens = 0, bool $win = false, bool $countGame = true, bool $applyMultipliers = true): array
    {
        $profile = $user->profile;
        $wallet = $user->wallet;
        $oldLevel = (int) ($profile->level ?? 1);
        $booster = max(1, (float)($profile->xp_boost_multiplier ?? 1));
        $pashaBoost = ((int)($profile->pasha_days ?? 0) > 0) ? 2.0 : 1.0;
        $earnedXp = $applyMultipliers ? (int) round(max(0, $xp) * $booster * $pashaBoost) : max(0, $xp);
        $profile->xp = (int) $profile->xp + $earnedXp;
        $newLevel = $this->levelForXp((int) $profile->xp);
        $bonus = 0;
        $levelRewards = [];
        if ($newLevel > $oldLevel) {
            for ($i = $oldLevel + 1; $i <= $newLevel; $i++) {
                $tokensForLevel = $this->rewardForLevel($i);
                $bonus += $tokensForLevel;
                $pashaDays = $i % 5 === 0 ? ($i % 25 === 0 ? 3 : 1) : 0;
                $ticket200 = $i % 10 === 0 ? 1 : 0;
                if ($pashaDays > 0) $profile->pasha_days = (int)($profile->pasha_days ?? 0) + $pashaDays;
                if ($ticket200 > 0) {
                    $ticket = CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>200],['quantity'=>0,'total_used'=>0]);
                    $ticket->increment('quantity',$ticket200);
                }
                $levelRewards[] = ['level'=>$i,'tokens'=>$tokensForLevel,'pasha_days'=>$pashaDays,'ticket_200'=>$ticket200,'prize_box'=>$i % 3 === 0];
            }
        }
        $profile->level = $newLevel;
        $profile->games_played = (int) ($profile->games_played ?? 0) + ($countGame ? 1 : 0);
        $profile->wins = (int) ($profile->wins ?? 0) + (($countGame && $win) ? 1 : 0);
        $profile->save();
        if ($wallet) {
            $wallet->tokens += max(0, $tokens) + $bonus;
            $wallet->save();
        }
        return ['old_level'=>$oldLevel,'new_level'=>$newLevel,'level_bonus'=>$bonus,'level_rewards'=>$levelRewards,'earned_xp'=>$earnedXp ?? $xp];
    }
}
