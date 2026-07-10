<?php
namespace App\Services\GameEngine;

class HandRules extends AbstractCardRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values($players); $deck=DeckFactory::pinochle(); $cardsEach=min(14, intdiv(max(1,count($deck)-1), max(1,count($players)))); [$hands,$deck]=$this->deal($players,$deck,$cardsEach); $discard=[]; if($deck)$discard[]=array_shift($deck)->id();
        $prevScores = $options['previous_scores'] ?? array_fill_keys($players,0);
        $round = (int)($options['round'] ?? 1);
        return ['phase'=>'playing','game_type'=>'hand','players'=>$players,'turn'=>$players[0]??null,'hands'=>$hands,'deck'=>array_map(fn($c)=>$c->id(),$deck),'discard'=>$discard,'melds'=>[],'first_meld_done'=>[],'drew_this_turn'=>[],'scores'=>$prevScores,'round'=>$round,'rounds_total'=>5,'target'=>(int)($options['target']?:5),'messages'=>['هاند: الجولة '.$round.' من 5. اسحب من الدك أو الرمي، رتّب/بدّل أوراقك بالسحب، انزل مجموعات أو سلاسل 3+، النزول الأول 51 نقطة، الفائز بالجولة -30 وإذا نزل هاند كامل -60.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        if(in_array($action,['draw_deck','draw_discard'],true)) return empty($state['drew_this_turn'][$playerId]);
        if($action==='meld') return count($payload['cards']??[])>=3 && count($payload['cards']??[])<=13 && $this->hasCards($state['hands'][$playerId]??[],$payload['cards']) && $this->isValidMeld($payload['cards']);
        if($action==='arrange_melds') return $this->validateArrange($state,$playerId,$payload);
        if($action==='discard') return !empty($state['drew_this_turn'][$playerId]) && in_array((string)($payload['card']??''),$state['hands'][$playerId]??[],true);
        return false;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_action'; $state['last_error_message']='الحركة غير مقبولة الآن: اسحب أولًا، ثم نزّل مجموعة/سلسلة صحيحة 3 أوراق أو أكثر، ثم ارمِ ورقة.'; return $state; }
        if($action==='draw_deck'){ if(!empty($state['deck'])) $state['hands'][$playerId][]=array_shift($state['deck']); $state['hands'][$playerId]=$this->sortHand($state['hands'][$playerId]); $state['drew_this_turn'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' سحب من الدك.'; }
        if($action==='draw_discard'){ if(!empty($state['discard'])) $state['hands'][$playerId][]=array_pop($state['discard']); $state['hands'][$playerId]=$this->sortHand($state['hands'][$playerId]); $state['drew_this_turn'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' سحب من الرمي.'; }
        if($action==='meld'){
            $cards=$payload['cards']; if(!$this->isValidMeld($cards)){ $state['last_error']='invalid_meld'; return $state; }
            $points=$this->points($cards); if(empty($state['first_meld_done'][$playerId]) && $points<51){ $state['last_error']='first_meld_51_required'; return $state; }
            foreach($cards as $c)$this->removeCard($state['hands'][$playerId],$c); $state['melds'][$playerId][]=$cards; $state['first_meld_done'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' نزل مجموعة بقيمة '.$points.'.';
        }
        if($action==='arrange_melds'){
            $groups=array_values($payload['groups'] ?? []);
            $flat=[]; foreach($groups as $g){ foreach($g as $c) $flat[]=$c; }
            if(!$this->validateArrange($state,$playerId,$payload)){ $state['last_error']='invalid_arrange'; return $state; }
            $state['table_groups'][$playerId]=$groups;
            $state['messages'][]=$this->labelPlayer($playerId).' رتّب مجموعاته الجاهزة على الأرض حسب القانون.';
        }
        if($action==='discard'){
            $card=(string)$payload['card']; $this->removeCard($state['hands'][$playerId],$card); $state['discard'][]=$card; $state['turn']=$this->playerKeyNext($state['players'],$playerId); unset($state['drew_this_turn'][$playerId]); $state['messages'][]=$this->labelPlayer($playerId).' رمى ورقة.';
            if(empty($state['hands'][$playerId])){ $fullHand=empty($state['first_meld_done'][$playerId]) && empty($state['melds'][$playerId]); $bonus=$fullHand ? -60 : -30; $state['scores'][$playerId]=(float)($state['scores'][$playerId]??0)+$bonus; foreach($state['players'] as $p){ if($p!==$playerId)$state['scores'][$p]=(float)($state['scores'][$p]??0)+$this->points($state['hands'][$p]??[]); } $state['winner']=$playerId; $state['round_result']=['winner'=>$playerId,'bonus'=>$bonus,'full_hand'=>$fullHand]; $state['messages'][]='انتهت الجولة '.($state['round']??1).'. الفائز: '.$this->labelPlayer($playerId).' وحصل على '.$bonus.' نقطة.'.($fullHand?' نزول هاند كامل.':''); if((int)($state['round']??1) >= (int)($state['rounds_total']??5)){ $state['phase']='finished'; $state['game_over']=true; $state['overall_winner']=$this->lowestScorePlayer($state['scores'] ?? []); $state['messages'][]='انتهت لعبة الهاند بعد 5 جولات. الفائز النهائي هو أقل لاعب بالنقاط: '.$this->labelPlayer($state['overall_winner']); } else { $state['phase']='finished'; $state['next_round_available']=true; $state['messages'][]='انتهت الجولة. اضغط بدء الجولة التالية لاستكمال 5 جولات.'; } }
        }
        $state['hands'][$playerId]=$this->sortHand($state['hands'][$playerId]??[]); return $state;
    }
    private function hasCards($hand,$cards): bool { foreach($cards as $c) if(!in_array($c,$hand,true)) return false; return true; }
    protected function points($cards): int|float { $sum=0; foreach($cards as $c){$r=$this->rank($c); $sum += ['A'=>15,'K'=>10,'Q'=>10,'J'=>10,'10'=>10,'9'=>5,'8'=>5,'7'=>5,'6'=>5,'5'=>5,'4'=>5,'3'=>5,'2'=>20,'JOKER'=>25][$r]??0;} return $sum; }
    private function lowestScorePlayer(array $scores): ?string { if(empty($scores)) return null; asort($scores); return array_key_first($scores); }
    protected function validateArrange(array $state,string $playerId,array $payload): bool {
        $groups=array_values($payload['groups'] ?? []); if(empty($groups)) return false; $all=[];
        foreach($groups as $g){ if(!is_array($g) || count($g)<3 || count($g)>13 || !$this->isValidMeld($g)) return false; foreach($g as $c)$all[]=$c; }
        return $this->hasCards($state['hands'][$playerId]??[],$all);
    }
    protected function isValidMeld($cards): bool { $ranks=array_map(fn($c)=>$this->rank($c),$cards); $suits=array_map(fn($c)=>$this->suit($c),$cards); if(count(array_unique($ranks))===1) return true; if(count(array_unique($suits))===1){ $vals=array_map(fn($c)=>$this->cardValue($c),$cards); sort($vals); for($i=1;$i<count($vals);$i++) if($vals[$i]!==$vals[$i-1]+1)return false; return true; } return false; }
}
