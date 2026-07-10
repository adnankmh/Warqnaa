<?php
namespace App\Services\WarqnaPro;

class DailyRewardService
{
    public function rewardForStreak(int $streak): int
    {
        $rewards=config('warqna_economy_matrix.daily_rewards',[]);
        return (int)($rewards[min(max($streak,1),7)] ?? 500);
    }

    public function spinPrize(): array
    {
        $wheel=[
            ['type'=>'coins','amount'=>250,'weight'=>35],
            ['type'=>'coins','amount'=>500,'weight'=>30],
            ['type'=>'tokens','amount'=>50,'weight'=>20],
            ['type'=>'coins','amount'=>1500,'weight'=>10],
            ['type'=>'rare_ticket','amount'=>1,'weight'=>5],
        ];
        $total=array_sum(array_column($wheel,'weight'));
        $pick=random_int(1,$total);
        $run=0;
        foreach($wheel as $prize){$run+=$prize['weight']; if($pick <= $run) return $prize;}
        return $wheel[0];
    }
}
