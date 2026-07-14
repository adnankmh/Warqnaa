<?php
namespace App\Services\GameEngine;

class HandRules extends AbstractCardRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values($players); $cardsEach=14; $deck=DeckFactory::pinochle(true,count($players),$cardsEach); $cardsEach=min($cardsEach, intdiv(max(1,count($deck)-1), max(1,count($players)))); [$hands,$deck]=$this->deal($players,$deck,$cardsEach); $discard=[]; if($deck)$discard[]=array_shift($deck)->id();
        $prevScores = $options['previous_scores'] ?? array_fill_keys($players,0);
        $round = (int)($options['round'] ?? 1);
        return ['phase'=>'playing','game_type'=>'hand','players'=>$players,'turn'=>$players[0]??null,'hands'=>$hands,'deck'=>array_map(fn($c)=>$c->id(),$deck),'discard'=>$discard,'melds'=>[],'first_meld_done'=>[],'drew_this_turn'=>[],'scores'=>$prevScores,'round'=>$round,'rounds_total'=>5,'target'=>(int)($options['target'] ?? 5),'messages'=>['هاند: الجولة '.$round.' من 5. اسحب من الدك أو الرمي، رتّب/بدّل أوراقك بالسحب، انزل مجموعات أو سلاسل 3+، النزول الأول 51 نقطة، الفائز بالجولة -30 وإذا نزل هاند كامل -60.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if($action==='new_round') return ($state['phase']??null)==='finished' && !empty($state['next_round_available']);
        if(($state['turn']??null)!==$playerId) return false;
        if(in_array($action,['draw_deck','draw_discard'],true)) return empty($state['drew_this_turn'][$playerId]);
        if($action==='meld') return count($payload['cards']??[])>=3 && count($payload['cards']??[])<=13 && $this->hasCards($state['hands'][$playerId]??[],$payload['cards']) && $this->isValidMeld($payload['cards']);
        if($action==='arrange_melds') return $this->validateArrange($state,$playerId,$payload);
        if($action==='layoff') return !empty($state['first_meld_done'][$playerId]) && $this->validateLayoff($state,$playerId,$payload);
        if($action==='discard') return !empty($state['drew_this_turn'][$playerId]) && in_array((string)($payload['card']??''),$state['hands'][$playerId]??[],true);
        return false;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_action'; $state['last_error_message']='الحركة غير مقبولة الآن: اسحب أولًا، ثم نزّل مجموعة/سلسلة صحيحة 3 أوراق أو أكثر، ثم ارمِ ورقة.'; return $state; }
        if($action==='new_round') return $this->initialState($state['players'] ?? [], ['previous_scores'=>$state['scores'] ?? [], 'round'=>(int)($state['round'] ?? 1)+1, 'target'=>$state['target'] ?? 5]);
        if($action==='draw_deck'){ if(!empty($state['deck'])) $state['hands'][$playerId][]=array_shift($state['deck']); $state['hands'][$playerId]=$this->sortHand($state['hands'][$playerId]); $state['drew_this_turn'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' سحب من الدك.'; }
        if($action==='draw_discard'){ if(!empty($state['discard'])) $state['hands'][$playerId][]=array_pop($state['discard']); $state['hands'][$playerId]=$this->sortHand($state['hands'][$playerId]); $state['drew_this_turn'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' سحب من الرمي.'; }
        if($action==='meld'){
            $cards=$payload['cards']; if(!$this->isValidMeld($cards)){ $state['last_error']='invalid_meld'; return $state; }
            $points=$this->points($cards); if(empty($state['first_meld_done'][$playerId]) && $points<$this->firstMeldMinimum()){ $state['last_error']='first_meld_51_required'; return $state; }
            foreach($cards as $c)$this->removeCard($state['hands'][$playerId],$c); $state['melds'][$playerId][]=$cards; $state['first_meld_done'][$playerId]=true; $state['messages'][]=$this->labelPlayer($playerId).' نزل مجموعة بقيمة '.$points.'.';
        }
        if($action==='layoff'){
            $target=(string)($payload['target_player']??''); $index=(int)($payload['meld_index']??-1); $cards=array_values($payload['cards']??[]);
            $merged=array_merge($state['melds'][$target][$index]??[],$cards);
            foreach($cards as $c)$this->removeCard($state['hands'][$playerId],$c);
            $state['melds'][$target][$index]=$merged; $state['messages'][]=$this->labelPlayer($playerId).' ركّب على مجموعة موجودة.';
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
    private function hasCards($hand,$cards): bool { $counts=array_count_values(array_map('strval',$hand)); foreach($cards as $c){$key=(string)$c; if(($counts[$key]??0)<1)return false; $counts[$key]--; } return true; }
    protected function points($cards): int|float { $sum=0; foreach($cards as $c){$r=$this->rank($c); $sum += ['A'=>15,'K'=>10,'Q'=>10,'J'=>10,'10'=>10,'9'=>5,'8'=>5,'7'=>5,'6'=>5,'5'=>5,'4'=>5,'3'=>5,'2'=>20,'JOKER'=>25][$r]??0;} return $sum; }
    private function lowestScorePlayer(array $scores): ?string { if(empty($scores)) return null; asort($scores); return array_key_first($scores); }
    protected function validateLayoff(array $state,string $playerId,array $payload): bool {
        $target=(string)($payload['target_player']??''); $index=(int)($payload['meld_index']??-1); $cards=array_values($payload['cards']??[]);
        if(!$cards || count($cards)>13 || !$this->hasCards($state['hands'][$playerId]??[],$cards)) return false;
        $existing=$state['melds'][$target][$index]??null; if(!is_array($existing) || count($existing)<3) return false;
        return $this->isValidMeld(array_merge($existing,$cards));
    }
    protected function validateArrange(array $state,string $playerId,array $payload): bool {
        $groups=array_values($payload['groups'] ?? []); if(empty($groups)) return false; $all=[];
        foreach($groups as $g){ if(!is_array($g) || count($g)<3 || count($g)>13 || !$this->isValidMeld($g)) return false; foreach($g as $c)$all[]=$c; }
        return $this->hasCards($state['hands'][$playerId]??[],$all);
    }
    protected function firstMeldMinimum(): int|float { return 51; }
    protected function isWildCard(string $card): bool { return in_array($this->rank($card),['2','JOKER'],true); }
    protected function isValidMeld($cards): bool {
        $cards=array_values(array_map('strval',$cards)); if(count($cards)<3)return false;
        $natural=array_values(array_filter($cards,fn($c)=>!$this->isWildCard($c))); $wild=count($cards)-count($natural);
        if(count($natural)<2)return false;
        $ranks=array_map(fn($c)=>$this->rank($c),$natural); $suits=array_map(fn($c)=>$this->suit($c),$natural);
        if(count(array_unique($ranks))===1)return count($cards)<=8;
        if(count(array_unique($suits))!==1)return false;
        $vals=array_values(array_unique(array_map(fn($c)=>$this->cardValue($c),$natural))); sort($vals);
        $needed=0; for($i=1;$i<count($vals);$i++){$gap=$vals[$i]-$vals[$i-1]-1;if($gap<0)return false;$needed+=$gap;}
        return $needed<=$wild && count($cards)<=13;
    }
}
