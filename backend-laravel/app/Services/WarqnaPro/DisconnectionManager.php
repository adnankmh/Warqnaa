<?php
namespace App\Services\WarqnaPro;

class DisconnectionManager
{
    public function markAway(array $state, string $playerKey): array
    {
        $state['away_players'][$playerKey] = ['since'=>now()->toIso8601String(),'auto_play_count'=>(int)($state['away_players'][$playerKey]['auto_play_count'] ?? 0)];
        $state['messages'][]='انقطع اتصال '.str_replace(['user:','bot:'],['لاعب ','بوت '],$playerKey).'، النظام سيلعب مؤقتًا حتى 3 لفات.';
        return $state;
    }

    public function shouldAutoPlay(array $state, string $playerKey): bool
    {
        return isset($state['away_players'][$playerKey]) && (int)($state['away_players'][$playerKey]['auto_play_count'] ?? 0) < 3;
    }

    public function recordAutoPlay(array $state, string $playerKey): array
    {
        $state['away_players'][$playerKey]['auto_play_count']=(int)($state['away_players'][$playerKey]['auto_play_count'] ?? 0)+1;
        if($state['away_players'][$playerKey]['auto_play_count'] >= 3){
            $state['suspended_players'][$playerKey]=true;
            $state['messages'][]='تم إخراج اللاعب مؤقتًا بعد 3 لفات انقطاع. يمكنه العودة بالضغط على مقعده.';
        }
        return $state;
    }

    public function reclaimSeat(array $state, string $playerKey): array
    {
        unset($state['away_players'][$playerKey], $state['suspended_players'][$playerKey]);
        $state['messages'][]='عاد اللاعب إلى مقعده وتم إيقاف اللعب التلقائي.';
        return $state;
    }
}
