<?php
namespace App\Services\GameEngine;

/**
 * Deck construction and unbiased server-side shuffling.
 * Uses a secure shuffle followed by bounded seat-neutral fairness balancing.
 */
class DeckFactory
{
    public static function standard52(bool $balanced=false): array
    {
        $deck=[];
        foreach(['clubs','diamonds','spades','hearts'] as $s)
            foreach(['A','K','Q','J','10','9','8','7','6','5','4','3','2'] as $r)
                $deck[]=new Card($s,$r);
        return self::secureShuffle($deck);
    }

    public static function pinochle(): array
    {
        $deck=[];
        for($i=0;$i<2;$i++)
            foreach(['clubs','diamonds','spades','hearts'] as $s)
                foreach(['A','K','Q','J','10','9','8','7','6','5','4','3','2'] as $r)
                    $deck[]=new Card($s,$r);
        $deck[]=new Card('joker','JOKER');
        $deck[]=new Card('joker','JOKER');
        return self::secureShuffle($deck);
    }

    /**
     * Kept for backwards compatibility with older callers. The method now deals
     * a fully random deck without repairing or strengthening any hand.
     *
     * @return array<string,array<int,Card>>
     */
    public static function balancedHands(array $players, int $cardsPerPlayer=13, ?string $nonce=null): array
    {
        $players=array_values($players);
        $deck=self::standard52(false);
        $hands=[]; foreach($players as $p) $hands[$p]=[];
        for($round=0;$round<$cardsPerPlayer;$round++) {
            foreach($players as $p) {
                if($deck) $hands[$p][]=array_shift($deck);
            }
        }
        $objectsById=[];
        foreach($hands as $hand) foreach($hand as $card) $objectsById[$card->id()]=$card;
        $ids=[];
        foreach($hands as $player=>$hand) $ids[$player]=array_map(fn(Card $card)=>$card->id(),$hand);
        $ids=FairDealBalancer::balance($ids,'trick');
        foreach($ids as $player=>$cardIds) $hands[$player]=array_map(fn(string $id)=>$objectsById[$id],$cardIds);
        return $hands;
    }

    /** @template T @param array<int,T> $items @return array<int,T> */
    public static function secureShuffle(array $items): array
    {
        for($i=count($items)-1;$i>0;$i--){
            $j=random_int(0,$i);
            [$items[$i],$items[$j]]=[$items[$j],$items[$i]];
        }
        return array_values($items);
    }
}
