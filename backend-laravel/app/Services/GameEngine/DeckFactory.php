<?php
namespace App\Services\GameEngine;

/**
 * Cryptographically shuffled decks with optional low-variance fairness.
 * Fairness never targets a named player and never guarantees a winner: several
 * secure random candidates are generated and the candidate with the smallest
 * hand-strength spread is selected before seats are assigned.
 */
class DeckFactory
{
    public static function standard52(bool $balanced=false): array
    {
        $deck=[];
        foreach(['clubs','diamonds','spades','hearts'] as $s)
            foreach(['A','K','Q','J','10','9','8','7','6','5','4','3','2'] as $r)
                $deck[]=new Card($s,$r);
        return $balanced ? self::balancedDeck($deck,4,13) : self::secureShuffle($deck);
    }

    public static function pinochle(bool $balanced=false,int $players=4,int $cardsEach=14): array
    {
        $deck=[];
        for($i=0;$i<2;$i++)
            foreach(['clubs','diamonds','spades','hearts'] as $s)
                foreach(['A','K','Q','J','10','9','8','7','6','5','4','3','2'] as $r)
                    $deck[]=new Card($s,$r);
        $deck[]=new Card('joker','JOKER'); $deck[]=new Card('joker','JOKER');
        return $balanced ? self::balancedDeck($deck,max(2,$players),$cardsEach) : self::secureShuffle($deck);
    }

    /** @return array<string,array<int,Card>> */
    public static function balancedHands(array $players,int $cardsPerPlayer=13,?string $nonce=null): array
    {
        $players=array_values($players); $deck=[];
        foreach(['clubs','diamonds','spades','hearts'] as $s)
            foreach(['A','K','Q','J','10','9','8','7','6','5','4','3','2'] as $r)$deck[]=new Card($s,$r);
        $deck=self::balancedDeck($deck,count($players),$cardsPerPlayer);
        $hands=array_fill_keys($players,[]);
        for($round=0;$round<$cardsPerPlayer;$round++)foreach($players as $player)if($deck)$hands[$player][]=array_shift($deck);
        return $hands;
    }

    /** @param array<int,Card> $base @return array<int,Card> */
    public static function balancedDeck(array $base,int $players,int $cardsEach): array
    {
        $players=max(2,$players); $cardsEach=max(1,$cardsEach); $best=null; $bestSpread=PHP_INT_MAX;
        // Sixteen independent candidates keeps the distribution random while
        // avoiding extremely weak/strong outlier hands.
        for($candidate=0;$candidate<16;$candidate++){
            $deck=self::secureShuffle($base); $scores=array_fill(0,$players,0);
            $limit=min(count($deck),$players*$cardsEach);
            for($i=0;$i<$limit;$i++)$scores[$i%$players]+=self::cardStrength($deck[$i]);
            $spread=max($scores)-min($scores);
            if($spread<$bestSpread){$bestSpread=$spread;$best=$deck;}
        }
        return $best ?: self::secureShuffle($base);
    }

    private static function cardStrength(Card $card): int
    {
        return ['JOKER'=>18,'A'=>14,'K'=>13,'Q'=>12,'J'=>11,'10'=>10,'9'=>9,'8'=>8,'7'=>7,'6'=>6,'5'=>5,'4'=>4,'3'=>3,'2'=>8][$card->rank]??1;
    }

    /** @template T @param array<int,T> $items @return array<int,T> */
    public static function secureShuffle(array $items): array
    {
        for($i=count($items)-1;$i>0;$i--){$j=random_int(0,$i);[$items[$i],$items[$j]]=[$items[$j],$items[$i]];}
        return array_values($items);
    }
}
