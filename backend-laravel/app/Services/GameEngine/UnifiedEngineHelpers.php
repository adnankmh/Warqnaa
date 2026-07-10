<?php
namespace App\Services\GameEngine;

trait UnifiedEngineHelpers
{
    protected array $rankPower = ['2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'J'=>11,'Q'=>12,'K'=>13,'A'=>14,'JOKER'=>20];
    protected array $suitOrder = ['clubs'=>1,'diamonds'=>2,'spades'=>3,'hearts'=>4,'joker'=>9];

    protected function suit(string $card): string { return explode('_',$card)[1] ?? ''; }
    protected function rank(string $card): string { return explode('_',$card)[0] ?? ''; }
    protected function sortCards(array $cards): array
    {
        usort($cards, fn($a,$b)=>[$this->suitOrder[$this->suit($a)] ?? 9,-($this->rankPower[$this->rank($a)] ?? 0)] <=> [$this->suitOrder[$this->suit($b)] ?? 9,-($this->rankPower[$this->rank($b)] ?? 0)]);
        return $cards;
    }
    protected function nextPlayer(array $players,string $current): string { $i=array_search($current,$players,true); return $players[(($i===false?0:$i)+1)%max(1,count($players))] ?? $current; }
    protected function removeCard(array &$hand,string $card): void { $i=array_search($card,$hand,true); if($i!==false) array_splice($hand,$i,1); }
    protected function cardLabel(string $c): string { $s=['clubs'=>'♣ سنك','diamonds'=>'♦ ديناري','spades'=>'♠ بستوني','hearts'=>'♥ كبة','joker'=>'🃏'][$this->suit($c)] ?? $this->suit($c); return $this->rank($c).' '.$s; }
}
