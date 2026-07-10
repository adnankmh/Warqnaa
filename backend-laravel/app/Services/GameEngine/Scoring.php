<?php
namespace App\Services\GameEngine;

class Scoring
{
    public static function cardPoints(string $card, string $mode='standard', ?string $trump=null): int
    {
        [$r,$s] = array_pad(explode('_',$card),2,'');
        if ($mode === 'baloot_sun') return ['A'=>11,'10'=>10,'K'=>4,'Q'=>3,'J'=>2,'9'=>0,'8'=>0,'7'=>0][$r] ?? 0;
        if ($mode === 'baloot_hokm') {
            if ($s === $trump) return ['J'=>20,'9'=>14,'A'=>11,'10'=>10,'K'=>4,'Q'=>3,'8'=>0,'7'=>0][$r] ?? 0;
            return ['A'=>11,'10'=>10,'K'=>4,'Q'=>3,'J'=>2,'9'=>0,'8'=>0,'7'=>0][$r] ?? 0;
        }
        if ($mode === 'hand') return ['A'=>15,'K'=>10,'Q'=>10,'J'=>10,'10'=>10,'9'=>5,'8'=>5,'7'=>5,'6'=>5,'5'=>5,'4'=>5,'3'=>5,'2'=>20,'JOKER'=>25][$r] ?? 0;
        return ['A'=>14,'K'=>13,'Q'=>12,'J'=>11,'10'=>10,'9'=>9,'8'=>8,'7'=>7,'6'=>6,'5'=>5,'4'=>4,'3'=>3,'2'=>2][$r] ?? 0;
    }
}
