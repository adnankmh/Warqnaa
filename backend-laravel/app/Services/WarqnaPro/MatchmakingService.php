<?php
namespace App\Services\WarqnaPro;

class MatchmakingService
{
    public function buildMatch(array $queue, int $playersNeeded = 4): ?array
    {
        usort($queue, fn($a,$b)=>($a['elo'] ?? 1000) <=> ($b['elo'] ?? 1000));
        for($i=0; $i <= count($queue)-$playersNeeded; $i++){
            $group=array_slice($queue,$i,$playersNeeded);
            $min=min(array_column($group,'elo'));
            $max=max(array_column($group,'elo'));
            $wait=max(array_column($group,'wait_seconds') ?: [0]);
            $allowed=100 + ($wait * 10);
            if(($max-$min) <= $allowed) return $group;
        }
        return null;
    }
}
