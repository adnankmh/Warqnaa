<?php
namespace App\Services\WarqnaPro;

use App\Models\SystemMetric;

class AntiCheatService
{
    public function validateMove(array $state, string $playerKey, string $action, array $payload): array
    {
        $flags=[];
        if (($state['turn'] ?? null) !== $playerKey) $flags[]='not_turn';
        if (($action === 'play_card') && !in_array((string)($payload['card'] ?? ''), $state['hands'][$playerKey] ?? [], true)) $flags[]='card_not_in_hand';
        if (count($flags)) {
            $this->log('invalid_move', ['player'=>$playerKey,'action'=>$action,'flags'=>$flags]);
            return ['ok'=>false,'flags'=>$flags,'message'=>'حركة مرفوضة من نظام الحماية: السيرفر هو الحكم ولا يقبل حركة غير قانونية.'];
        }
        return ['ok'=>true,'flags'=>[]];
    }

    public function collusionScore(array $recentMatches): int
    {
        $score=0;
        foreach($recentMatches as $m){
            if(($m['same_pair_losses'] ?? 0) >= 5) $score += 30;
            if(($m['coin_transfer_pattern'] ?? false)) $score += 40;
            if(($m['suspicious_passes'] ?? 0) >= 10) $score += 20;
        }
        return min(100,$score);
    }

    public function log(string $key, array $meta=[]): void
    {
        try { SystemMetric::create(['key'=>'anti_cheat_'.$key,'value'=>json_encode($meta,JSON_UNESCAPED_UNICODE),'meta'=>$meta]); } catch(\Throwable $e) {}
    }
}
