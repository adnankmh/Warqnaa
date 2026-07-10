<?php
namespace App\Services\GameEngine;

abstract class AbstractCardRules implements GameRuleContract
{
    protected array $rankPower = ['2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'J'=>11,'Q'=>12,'K'=>13,'A'=>14];
    protected array $suits = ['clubs','diamonds','spades','hearts'];

    protected function playerKeyNext(array $players, string $current): string
    {
        $i = array_search($current, $players, true);
        return $players[(($i === false ? 0 : $i) + 1) % max(1, count($players))] ?? $current;
    }
    protected function suit(string $card): string { return explode('_', $card)[1] ?? ''; }
    protected function rank(string $card): string { return explode('_', $card)[0] ?? ''; }
    protected function cardValue(string $card): int { return $this->rankPower[$this->rank($card)] ?? 0; }
    protected function removeCard(array &$hand, string $card): bool
    {
        $idx = array_search($card, $hand, true);
        if ($idx === false) return false;
        array_splice($hand, $idx, 1);
        return true;
    }
    protected function sortHand(array $cards): array
    {
        $suitOrder = ['clubs'=>1,'diamonds'=>2,'spades'=>3,'hearts'=>4,'joker'=>9];
        usort($cards, fn($a,$b)=>[$suitOrder[$this->suit($a)] ?? 9, -($this->rankPower[$this->rank($a)] ?? 0)] <=> [$suitOrder[$this->suit($b)] ?? 9, -($this->rankPower[$this->rank($b)] ?? 0)]);
        return $cards;
    }
    protected function deal(array $players, array $deck, int $cardsEach): array
    {
        $hands = [];
        foreach ($players as $p) $hands[$p] = [];
        for ($r=0; $r<$cardsEach; $r++) foreach ($players as $p) if ($deck) $hands[$p][] = array_shift($deck)->id();
        foreach ($hands as $k=>$h) $hands[$k] = $this->sortHand($h);
        return [$hands, $deck];
    }
    protected function teams(array $players): array
    {
        return ['teamA'=>array_values(array_filter([$players[0] ?? null, $players[2] ?? null, $players[4] ?? null])), 'teamB'=>array_values(array_filter([$players[1] ?? null, $players[3] ?? null, $players[5] ?? null]))];
    }
    protected function teamOf(array $state, string $player): string
    {
        foreach (($state['teams'] ?? []) as $team=>$members) if (in_array($player, $members, true)) return $team;
        return $player;
    }
    protected function allHandsEmpty(array $hands): bool { foreach ($hands as $h) if (count($h)>0) return false; return true; }
    protected function hasSuit(array $hand, string $suit): bool { foreach ($hand as $c) if ($this->suit($c)===$suit) return true; return false; }
    protected function trickWinner(array $trick, ?string $trump=null, array $rankPower=null): string
    {
        $rankPower = $rankPower ?: $this->rankPower;
        $leadSuit = $this->suit((string)reset($trick));
        $bestPlayer = array_key_first($trick); $best = -1;
        foreach ($trick as $p=>$card) {
            $score = (($this->suit($card)===$trump && $trump) ? 1000 : (($this->suit($card)===$leadSuit) ? 500 : 0)) + ($rankPower[$this->rank($card)] ?? 0);
            if ($score > $best) { $best = $score; $bestPlayer = $p; }
        }
        return $bestPlayer;
    }
    protected function labelPlayer(string $p): string { return str_replace(['user:','bot:'], ['لاعب ','بوت '], $p); }
    protected function suitName(string $s): string { return ['clubs'=>'♣ سباتي','diamonds'=>'♦ ديناري','spades'=>'♠ بستوني','hearts'=>'♥ كبة'][$s] ?? $s; }
}
