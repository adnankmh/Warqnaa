<?php

/**
 * V0.3 balanced-premium dealing.
 * It never duplicates or invents cards. Every player receives a comparable
 * strength budget, while rummy games receive legal meld potential.
 */
final class BalancedDealV03
{
    /** @return array{hands:array<string,array<int,string>>,deck:array<int,string>} */
    public static function trick(array $deck, array $playerIds, int $cardsEach): array
    {
        $hands=array_fill_keys($playerIds,[]);
        $scores=array_fill_keys($playerIds,0);
        usort($deck,fn($a,$b)=>self::strength($b)<=>self::strength($a));
        $direction=1;$cursor=0;$count=count($playerIds);
        foreach($deck as $card){
            $eligible=array_values(array_filter($playerIds,fn($id)=>count($hands[$id])<$cardsEach));
            if(!$eligible) break;
            usort($eligible,function($a,$b) use($scores,$hands){
                $cmp=$scores[$a]<=>$scores[$b];
                return $cmp!==0?$cmp:count($hands[$a])<=>count($hands[$b]);
            });
            // Among equally weak hands rotate the recipient to avoid seat bias.
            $min=$scores[$eligible[0]];
            $tied=array_values(array_filter($eligible,fn($id)=>$scores[$id]===$min));
            $recipient=$tied[$cursor%count($tied)];$cursor++;
            $hands[$recipient][]=$card;$scores[$recipient]+=self::strength($card);
        }
        $used=[];foreach($hands as $cards)foreach($cards as $card)$used[]=self::token($card,$used);
        $remaining=self::subtractMultiset($deck,$hands);
        foreach($hands as &$cards) usort($cards,fn($a,$b)=>self::sortKey($a)<=>self::sortKey($b));
        return ['hands'=>$hands,'deck'=>$remaining];
    }

    /** @return array{hands:array<string,array<int,string>>,deck:array<int,string>} */
    public static function rummy(array $deck, array $playerIds, int $cardsEach, int $firstExtra=0): array
    {
        $hands=array_fill_keys($playerIds,[]);
        $pool=array_values($deck);
        foreach($playerIds as $index=>$pid){
            $target=$cardsEach+($index===0?$firstExtra:0);
            foreach(['run','set'] as $kind){
                $meld=$kind==='run'?self::extractRun($pool):self::extractSet($pool);
                if($meld && count($hands[$pid])+count($meld)<=$target){
                    foreach($meld as $card){$hands[$pid][]=$card;self::removeOne($pool,$card);}
                }
            }
        }
        // Fill by the lowest current hand strength so no seat is starved.
        while(true){
            $eligible=[];
            foreach($playerIds as $index=>$pid){$target=$cardsEach+($index===0?$firstExtra:0);if(count($hands[$pid])<$target)$eligible[]=$pid;}
            if(!$eligible||!$pool)break;
            usort($eligible,fn($a,$b)=>self::handStrength($hands[$a])<=>self::handStrength($hands[$b]));
            usort($pool,fn($a,$b)=>self::strength($b)<=>self::strength($a));
            $pid=$eligible[0];$card=array_shift($pool);$hands[$pid][]=$card;
        }
        foreach($hands as &$cards) usort($cards,fn($a,$b)=>self::sortKey($a)<=>self::sortKey($b));
        return ['hands'=>$hands,'deck'=>array_values($pool)];
    }

    private static function extractRun(array $pool): array
    {
        $bySuit=[];foreach($pool as $card){if(str_starts_with($card,'JOKER'))continue;[$rank,$suit]=self::parts($card);$bySuit[$suit][self::rank($rank)][]=$card;}
        foreach($bySuit as $ranks){ksort($ranks);$keys=array_keys($ranks);for($i=0;$i<count($keys)-2;$i++){if($keys[$i+1]===$keys[$i]+1&&$keys[$i+2]===$keys[$i]+2)return [$ranks[$keys[$i]][0],$ranks[$keys[$i+1]][0],$ranks[$keys[$i+2]][0]];}}
        return [];
    }

    private static function extractSet(array $pool): array
    {
        $byRank=[];foreach($pool as $card){if(str_starts_with($card,'JOKER'))continue;[$rank,$suit]=self::parts($card);$byRank[$rank][$suit][]=$card;}
        foreach($byRank as $suits){if(count($suits)>=3){$out=[];foreach($suits as $cards){$out[]=$cards[0];if(count($out)===3)return $out;}}}
        return [];
    }

    private static function subtractMultiset(array $deck,array $hands): array
    {
        $remaining=array_values($deck);foreach($hands as $cards)foreach($cards as $card)self::removeOne($remaining,$card);return $remaining;
    }
    private static function removeOne(array &$pool,string $card): void {$i=array_search($card,$pool,true);if($i!==false)array_splice($pool,$i,1);}
    private static function handStrength(array $hand): int {return array_sum(array_map([self::class,'strength'],$hand));}
    private static function parts(string $card): array {$p=explode('_',$card,2);return [$p[0]??$card,$p[1]??''];}
    private static function rank(string $rank): int {return match($rank){'A'=>14,'K'=>13,'Q'=>12,'J'=>11,'10'=>10,'9'=>9,'8'=>8,'7'=>7,'6'=>6,'5'=>5,'4'=>4,'3'=>3,'2'=>2,default=>1};}
    private static function strength(string $card): int {if(str_starts_with($card,'JOKER'))return 15;[$r]=$p=self::parts($card);return self::rank($r);}
    private static function sortKey(string $card): int {[$r,$s]=self::parts($card);$si=array_search($s,['C','D','S','H'],true);return (($si===false?9:$si)*100)+(15-self::rank($r));}
    private static function token(string $card,array $seen): string {return $card.'#'.count(array_filter($seen,fn($x)=>str_starts_with($x,$card.'#')));}
}
