<?php
namespace App\Services\WarqnaPro;

/**
 * Normalizes gameplay actions arriving from Web, Flutter, legacy rooms and
 * localized Arabic clients. Card normalization is deliberately token based;
 * it never performs recursive single-letter replacements inside words such as
 * `clubs`, which was the root cause of A_clubs becoming a corrupted value.
 */
class PlayActionNormalizer
{
    /** @var array<string,array<int,string>> */
    private const SUIT_ALIASES = [
        'clubs' => ['clubs','club','clover','♣','♧','سنك','سباتي','شجرة','تريفل','c'],
        'diamonds' => ['diamonds','diamond','♦','♢','ديناري','دينار','d'],
        'spades' => ['spades','spade','♠','♤','بستوني','باص','s'],
        'hearts' => ['hearts','heart','♥','♡','كبة','قلب','h'],
    ];

    /** @var array<string,string> */
    private const RANK_ALIASES = [
        'a'=>'A','ace'=>'A','آس'=>'A','اص'=>'A',
        'k'=>'K','king'=>'K','شيخ'=>'K','ملك'=>'K',
        'q'=>'Q','queen'=>'Q','بنت'=>'Q',
        'j'=>'J','jack'=>'J','ولد'=>'J','جك'=>'J','شاب'=>'J',
        '10'=>'10','9'=>'9','8'=>'8','7'=>'7','6'=>'6','5'=>'5','4'=>'4','3'=>'3','2'=>'2',
    ];

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
                $card=(string)($payload['rank'] ?? $payload['value'] ?? '').'_'.(string)($payload['suit'] ?? $payload['type'] ?? '');
            }
            $payload['card']=$this->canonicalCard($card,$hand) ?? $this->normalizeCardId($card);
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
        $needle=$this->cleanToken($s);
        foreach(self::SUIT_ALIASES as $canonical=>$aliases){
            foreach($aliases as $alias){
                if($needle===$this->cleanToken($alias)) return $canonical;
            }
        }
        return $needle;
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
        return null;
    }

    public function normalizeCardId(string $card): string
    {
        [$rank,$suit]=$this->parseCard($card);
        if($rank!=='' && $suit!=='') return $rank.'_'.$suit;

        $fallback=preg_replace('/[\s\-\/|:]+/u','_',trim($card)) ?? trim($card);
        return strtoupper(trim($fallback,'_'));
    }

    public function cardSuit(string $card): string
    {
        [, $suit]=$this->parseCard($card);
        return $suit;
    }

    /** @return array{0:string,1:string} */
    private function parseCard(string $card): array
    {
        $value=$this->cleanCardText($card);
        if($value==='') return ['',''];

        $aliases=[];
        foreach(self::SUIT_ALIASES as $suit=>$tokens){
            foreach($tokens as $token) $aliases[]=['suit'=>$suit,'token'=>$this->cleanToken($token)];
        }
        usort($aliases,fn($a,$b)=>$this->uLength($b['token'])<=>$this->uLength($a['token']));

        foreach($aliases as $entry){
            $token=$entry['token'];
            if($token==='') continue;

            if($value===$token) return ['', $entry['suit']];

            if(str_starts_with($value,$token)){
                $remainder=$this->trimSeparators($this->uSubstr($value,$this->uLength($token)));
                $rank=$this->normalizeRank($remainder);
                if($rank!=='') return [$rank,$entry['suit']];
            }

            if(str_ends_with($value,$token)){
                $length=$this->uLength($value)-$this->uLength($token);
                $remainder=$this->trimSeparators($this->uSubstr($value,0,$length));
                $rank=$this->normalizeRank($remainder);
                if($rank!=='') return [$rank,$entry['suit']];
            }
        }

        $parts=preg_split('/[_\s\-\/|:]+/u',$value,-1,PREG_SPLIT_NO_EMPTY) ?: [];
        if(count($parts)>=2){
            foreach($parts as $i=>$part){
                $suit=$this->normalizeSuit($part);
                if(!in_array($suit,array_keys(self::SUIT_ALIASES),true)) continue;
                foreach($parts as $j=>$candidate){
                    if($i===$j) continue;
                    $rank=$this->normalizeRank($candidate);
                    if($rank!=='') return [$rank,$suit];
                }
            }
        }

        return [$this->normalizeRank($value),''];
    }

    private function normalizeRank(string $rank): string
    {
        $rank=$this->trimSeparators($this->cleanToken($rank));
        return self::RANK_ALIASES[$rank] ?? '';
    }

    private function cleanCardText(string $value): string
    {
        $value=str_replace(["\u{FE0F}","\u{200E}","\u{200F}","\u{202A}","\u{202B}","\u{202C}"],'',$value);
        return function_exists('mb_strtolower') ? mb_strtolower(trim($value),'UTF-8') : strtolower(trim($value));
    }

    private function cleanToken(string $value): string
    {
        return $this->cleanCardText($value);
    }

    private function uLength(string $value): int
    {
        if(function_exists('mb_strlen')) return mb_strlen($value,'UTF-8');
        $chars=preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY);
        return is_array($chars) ? count($chars) : strlen($value);
    }

    private function uSubstr(string $value,int $start,?int $length=null): string
    {
        if(function_exists('mb_substr')) return mb_substr($value,$start,$length,'UTF-8');
        $chars=preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY);
        if(!is_array($chars)) return $length===null ? substr($value,$start) : substr($value,$start,$length);
        return implode('',array_slice($chars,$start,$length));
    }

    private function trimSeparators(string $value): string
    {
        return trim($value," _-\t\n\r\0\x0B/|:");
    }
}
