<?php
namespace App\Services\Leveling;

use App\Models\User;

class XpService
{
    public function levelForXp(int $xp): int
    {
        $level = 1;
        while ($level < 100 && $xp >= $this->requiredXp($level)) {
            $level++;
        }
        return $level;
    }

    public function requiredXp(int $level): int
    {
        if ($level <= 10) return [1=>58,2=>87,3=>127,4=>173,5=>230,6=>299,7=>374,8=>460,9=>564,10=>690][$level] ?? 690;
        if ($level <= 50) return (int) round(690 * pow(1.16, $level - 10) + ($level * 120));
        return (int) round(90275 * pow(1.085, $level - 50) + ($level * 500));
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

    public function award(User $user, int $xp, int $tokens = 0, bool $win = false): array
    {
        $profile = $user->profile;
        $wallet = $user->wallet;
        $oldLevel = (int) ($profile->level ?? 1);
        $booster = max(1, (float)($profile->xp_boost_multiplier ?? 1));
        $pashaBoost = ((int)($profile->pasha_days ?? 0) > 0) ? 1.5 : 1.0;
        $earnedXp = (int) round(max(0, $xp) * $booster * $pashaBoost);
        $profile->xp = (int) $profile->xp + $earnedXp;
        $newLevel = $this->levelForXp((int) $profile->xp);
        $bonus = 0;
        if ($newLevel > $oldLevel) {
            for ($i = $oldLevel + 1; $i <= $newLevel; $i++) $bonus += $this->rewardForLevel($i);
        }
        $profile->level = $newLevel;
        $profile->games_played = (int) ($profile->games_played ?? 0) + 1;
        $profile->wins = (int) ($profile->wins ?? 0) + ($win ? 1 : 0);
        $profile->save();
        if ($wallet) {
            $wallet->tokens += max(0, $tokens) + $bonus;
            $wallet->save();
        }
        return ['old_level'=>$oldLevel,'new_level'=>$newLevel,'level_bonus'=>$bonus,'earned_xp'=>$earnedXp ?? $xp];
    }
}
