<?php
namespace App\Services\WarqnaPro;

class PlayActionNormalizer
{
    public function normalize(string $action, array $payload, array $state, string $playerKey): array
    {
        $action=trim($action);

        if(str_starts_with($action,'bid:')){
            $payload['value']=(int)str_replace('bid:','',$action);
            $action='bid';
        }

        if(in_array($action,['choose_trump','choose_hokm','select_trump','trump'],true)){
            $payload['suit']=$this->normalizeSuit((string)($payload['suit'] ?? $payload['trump'] ?? $payload['type'] ?? $payload['value'] ?? ''));
            $action='choose_trump';
        }

        if($action==='play_card' || $action==='card'){
            $action='play_card';
            $hand=$state['hands'][$playerKey] ?? [];
            $card=$this->extractCardValue($payload);
            if($card==='' && (isset($payload['rank']) || isset($payload['suit']) || isset($payload['type']))){
                $rank=strtoupper((string)($payload['rank'] ?? $payload['value'] ?? ''));
                $suit=$this->normalizeSuit((string)($payload['suit'] ?? $payload['type'] ?? ''));
                $card=$rank.'_'.$suit;
            }
            $payload['card']=$this->canonicalCard($card,$hand) ?? $card;
            $payload['_normalized_card']=$payload['card'];
        }

        if($action==='play_tile' || $action==='domino'){
            $action='play_tile';
            $payload['tile']=(string)($payload['tile'] ?? $payload['domino'] ?? $payload['id'] ?? $payload['value'] ?? '');
            $payload['side']=$payload['side'] ?? $payload['edge'] ?? 'right';
        }

        return [$action,$payload];
    }

    public function diagnostic(array $state,string $playerKey,string $action,array $payload): array
    {
        $hand=$state['hands'][$playerKey] ?? [];
        if($action!=='play_card') return ['type'=>'other'];
        $card=(string)($payload['card'] ?? '');
        $canon=$this->canonicalCard($card,$hand);
        if(!$canon) return ['type'=>'not_in_hand','card'=>$card,'hand_count'=>count($hand),'hand'=>$hand];
        if(!empty($state['trick'])){
            $leadCard=(string)reset($state['trick']);
            $leadSuit=$this->cardSuit($leadCard);
            $cardSuit=$this->cardSuit($canon);
            $hasLead=false;
            foreach($hand as $h){ if($this->cardSuit((string)$h)===$leadSuit){$hasLead=true;break;} }
            if($hasLead && $cardSuit!==$leadSuit){
                return ['type'=>'must_follow_suit','card'=>$canon,'lead_suit'=>$leadSuit,'card_suit'=>$cardSuit,'legal'=>array_values(array_filter($hand,fn($h)=>$this->cardSuit((string)$h)===$leadSuit))];
            }
        }
        return ['type'=>'looks_legal','card'=>$canon];
    }

    private function extractCardValue(array $payload): string
    {
        $raw=$payload['card'] ?? $payload['card_id'] ?? $payload['id'] ?? $payload['code'] ?? $payload['value'] ?? '';
        if(is_array($raw)){
            return (string)($raw['id'] ?? $raw['card'] ?? $raw['card_id'] ?? $raw['code'] ?? (($raw['rank'] ?? '').'_'.($raw['suit'] ?? $raw['type'] ?? '')));
        }
        if(is_object($raw)){
            $arr=(array)$raw;
            return (string)($arr['id'] ?? $arr['card'] ?? $arr['card_id'] ?? $arr['code'] ?? (($arr['rank'] ?? '').'_'.($arr['suit'] ?? $arr['type'] ?? '')));
        }
        return trim((string)$raw);
    }

    public function normalizeSuit(string $s): string
    {
        $s=strtolower(trim(str_replace(['️',' ','_','-'], '', $s)));
        return match($s){
            'c','club','clubs','♣','♧','سنك','سباتي','شجرة','تريفل' => 'clubs',
            'd','diamond','diamonds','♦','♢','ديناري','دينار' => 'diamonds',
            's','spade','spades','♠','♤','بستوني','باص' => 'spades',
            'h','heart','hearts','♥','♡','كبة','قلب' => 'hearts',
            default => $s,
        };
    }

    public function canonicalCard(string $card, array $hand): ?string
    {
        $card=trim($card);
        if($card==='' || empty($hand)) return null;
        if(in_array($card,$hand,true)) return $card;

        $norm=$this->normalizeCardId($card);
        foreach($hand as $h){
            if($this->normalizeCardId((string)$h)===$norm) return (string)$h;
        }

        [$wantRank,$wantSuit]=$this->splitNormalized($norm);
        foreach($hand as $h){
            [$rank,$suit]=$this->splitNormalized($this->normalizeCardId((string)$h));
            if($wantRank!=='' && $wantSuit!=='' && $rank===$wantRank && $suit===$wantSuit) return (string)$h;
        }

        // Some frontends send suit first: clubs_A, heart-10, ♣A.
        if(str_contains($card,'_')){
            $parts=explode('_',$card);
            if(count($parts)>=2){
                $rev=strtoupper(end($parts)).'_'.$this->normalizeSuit($parts[0]);
                foreach($hand as $h){ if($this->normalizeCardId((string)$h)===$this->normalizeCardId($rev)) return (string)$h; }
            }
        }

        return null;
    }

    public function normalizeCardId(string $card): string
    {
        $c=strtolower(trim((string)$card));
        $c=str_replace(['️',' ','-','/','|',':'],['_','','_','_','_','_'],$c);
        $rankMap=['ace'=>'a','king'=>'k','queen'=>'q','jack'=>'j','آس'=>'a','اص'=>'a','شيخ'=>'k','ملك'=>'k','بنت'=>'q','ولد'=>'j','جك'=>'j','شاب'=>'j'];
        foreach($rankMap as $from=>$to) $c=str_replace($from,$to,$c);
        $suitMap=[
            'clubs'=>['♣','♧','club','clubs','clover','c','سنك','سباتي','شجرة','تريفل'],
            'diamonds'=>['♦','♢','diamond','diamonds','d','ديناري','دينار'],
            'spades'=>['♠','♤','spade','spades','s','بستوني','باص'],
            'hearts'=>['♥','♡','heart','hearts','h','كبة','قلب'],
        ];
        foreach($suitMap as $suit=>$tokens){
            foreach($tokens as $t){
                if($c===$t) return '_'.$suit;
                if(str_ends_with($c,$t)){
                    $rank=trim(substr($c,0,strlen($c)-strlen($t)),'_');
                    return strtoupper($rank).'_'.$suit;
                }
                if(str_starts_with($c,$t)){
                    $rank=trim(substr($c,strlen($t)),'_');
                    return strtoupper($rank).'_'.$suit;
                }
                $c=str_replace($t,'_'.$suit,$c);
            }
        }
        $c=trim(preg_replace('/_+/','_',$c),'_');
        [$rank,$suit]=$this->splitNormalized($c);
        return $rank && $suit ? $rank.'_'.$suit : strtoupper($c);
    }

    public function cardSuit(string $card): string
    {
        return $this->splitNormalized($this->normalizeCardId($card))[1] ?? '';
    }

    private function splitNormalized(string $norm): array
    {
        $parts=array_values(array_filter(explode('_',trim($norm,'_')),fn($x)=>$x!==''));
        if(count($parts)>=2){
            $a=strtoupper($parts[0]); $b=$this->normalizeSuit(end($parts));
            if(in_array(strtolower($parts[0]),['clubs','diamonds','spades','hearts'],true)){
                return [strtoupper(end($parts)),$this->normalizeSuit($parts[0])];
            }
            return [$a,$b];
        }
        return [strtoupper($norm),''];
    }
}
