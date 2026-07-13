<?php
namespace App\Services\GameEngine;

/**
 * Two-player Basra implementation following the common 52-card rules:
 * four-card deals, same-rank/sum captures, Jack and 7♦ sweeps, Basra bonuses,
 * card bonuses, majority bonus and play to 121 points.
 */
class BasraRules extends AbstractCardRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values(array_slice($players,0,2));
        while(count($players)<2)$players[]='bot:basra_'.count($players);
        $state=[
            'phase'=>'playing','game_type'=>'basra','players'=>$players,'turn'=>$players[0],
            'dealer_index'=>1,'round'=>1,'target'=>(int)($options['target']??121),
            'hands'=>array_fill_keys($players,[]),'table'=>[],'deck'=>[],
            'captures'=>array_fill_keys($players,[]),'basra'=>array_fill_keys($players,0),
            'score'=>array_fill_keys($players,0),'round_score'=>array_fill_keys($players,0),
            'last_collector'=>null,'majority_carry'=>30,
            'messages'=>['بدأت الباصرة: أربع أوراق لكل لاعب وأربع على الأرض، ولا يوجد تمرير.'],
            'engine_quality'=>'basra_complete_v142',
        ];
        return $this->startRound($state);
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        return ($state['phase']??null)==='playing'&&($state['turn']??null)===$playerId&&$action==='play_card'&&in_array((string)($payload['card']??''),$state['hands'][$playerId]??[],true);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='اختر ورقة من يدك عندما يحين دورك؛ لا يوجد تمرير في الباصرة.';
            return $state;
        }
        $card=(string)$payload['card'];$tableBefore=$state['table'];
        $this->removeCard($state['hands'][$playerId],$card);
        $capture=$this->captureSet($card,$tableBefore);
        if($capture){
            $state['table']=$this->removeCaptured($state['table'],$capture);
            $state['captures'][$playerId]=array_merge($state['captures'][$playerId]??[],[$card],$capture);
            $state['last_collector']=$playerId;
            $isSweep=count($tableBefore)>0&&empty($state['table']);
            if($isSweep&&$this->qualifiesBasra($card,$tableBefore)){
                $state['basra'][$playerId]=(int)($state['basra'][$playerId]??0)+1;
                $state['messages'][]='✨ باصرة لـ '.$this->labelPlayer($playerId).' (+10).';
            }else{
                $state['messages'][]=$this->labelPlayer($playerId).' جمع '.count($capture).' ورقة من الأرض.';
            }
        }else{
            $state['table'][]=$card;
            $state['messages'][]=$this->labelPlayer($playerId).' وضع ورقة على الأرض.';
        }

        if($this->allHandsEmpty($state['hands'])){
            if(!empty($state['deck']))$this->dealFour($state);
            else return $this->finishRound($state);
        }
        $state['turn']=$this->playerKeyNext($state['players'],$playerId);
        unset($state['last_error_message']);
        return $state;
    }

    public function onTurnTimeout(array $state): array
    {
        $player=(string)($state['turn']??'');$hand=$state['hands'][$player]??[];
        if(!$hand)return $state;
        usort($hand,function($a,$b)use($state){
            $ca=count($this->captureSet((string)$a,$state['table']??[]));
            $cb=count($this->captureSet((string)$b,$state['table']??[]));
            return $cb<=>$ca;
        });
        $state['messages'][]='⏱️ انتهى الوقت؛ لعب الكمبيوتر أفضل ورقة قانونية.';
        return $this->apply($state,$player,'play_card',['card'=>$hand[0]]);
    }

    public function availableActions(array $state,string $playerId): array
    {
        if(($state['turn']??null)!==$playerId)return [];
        return array_map(fn($card)=>[
            'type'=>'play_card','card'=>$card,
            'captures'=>count($this->captureSet((string)$card,$state['table']??[])),
        ],$state['hands'][$playerId]??[]);
    }

    private function startRound(array $state): array
    {
        $deck=array_map(fn($card)=>$card->id(),DeckFactory::standard52(false));
        $state['hands']=array_fill_keys($state['players'],[]);$state['table']=[];
        $state['captures']=array_fill_keys($state['players'],[]);$state['basra']=array_fill_keys($state['players'],0);
        $state['round_score']=array_fill_keys($state['players'],0);$state['last_collector']=null;
        // Initial table may not contain J or 7♦; replace those cards and return them to the deck.
        $rejected=[];
        while(count($state['table'])<4&&$deck){
            $card=array_shift($deck);
            if($this->isSweepCard($card)){$rejected[]=$card;continue;}
            $state['table'][]=$card;
        }
        $deck=DeckFactory::secureShuffle(array_merge($deck,$rejected));
        $state['deck']=$deck;$this->dealFour($state);
        $dealer=$state['players'][$state['dealer_index']%2];
        $state['turn']=$this->playerKeyNext($state['players'],$dealer);
        $state['messages'][]='الجولة '.($state['round']??1).' — يبدأ اللاعب غير الموزع.';
        return $state;
    }

    private function dealFour(array &$state): void
    {
        for($i=0;$i<4;$i++)foreach($state['players'] as $player)if($state['deck'])$state['hands'][$player][]=array_shift($state['deck']);
        foreach($state['hands'] as $player=>$cards)$state['hands'][$player]=$this->sortHand($cards);
    }

    private function finishRound(array $state): array
    {
        if(!empty($state['table'])&&!empty($state['last_collector'])){
            $last=$state['last_collector'];$state['captures'][$last]=array_merge($state['captures'][$last]??[],$state['table']);$state['table']=[];
            $state['messages'][]=$this->labelPlayer($last).' أخذ الأوراق المتبقية لأنه آخر من جمع.';
        }
        $counts=[];foreach($state['players'] as $p)$counts[$p]=count($state['captures'][$p]??[]);
        $carry=(int)($state['majority_carry']??30);
        if(count(array_unique(array_values($counts)))===1){
            $state['majority_carry']=$carry+30;
            $state['messages'][]='تعادل في عدد الأوراق؛ ترحّل مكافأة الأكثر إلى الجولة التالية ('.$state['majority_carry'].').';
        }else{
            $majority=array_keys($counts,max($counts),true)[0];
            $state['round_score'][$majority]+=$carry;$state['majority_carry']=30;
        }
        foreach($state['players'] as $player){
            $points=10*(int)($state['basra'][$player]??0);
            foreach($state['captures'][$player]??[] as $card){
                $rank=$this->rank((string)$card);$suit=$this->suit((string)$card);
                if(in_array($rank,['A','J'],true))$points++;
                if($rank==='2'&&$suit==='clubs')$points+=2;
                if($rank==='10'&&$suit==='diamonds')$points+=3;
            }
            $state['round_score'][$player]+=$points;
            $state['score'][$player]=(int)($state['score'][$player]??0)+(int)$state['round_score'][$player];
            $state['messages'][]=$this->labelPlayer($player).' أحرز '.$state['round_score'][$player].' نقطة في الجولة.';
        }
        $leader=array_keys($state['score'],max($state['score']),true)[0]??null;
        if($leader!==null&&(int)$state['score'][$leader]>=(int)$state['target']){
            $state['phase']='finished';$state['winner']=$leader;
            $state['messages'][]='انتهت الباصرة عند هدف '.$state['target'].'؛ الفائز: '.$this->labelPlayer($leader).'.';
            return $state;
        }
        $state['round']=(int)($state['round']??1)+1;$state['dealer_index']=((int)$state['dealer_index']+1)%2;
        return $this->startRound($state);
    }

    /** @return array<int,string> */
    private function captureSet(string $card,array $table): array
    {
        if(!$table)return [];
        if($this->isSweepCard($card))return array_values($table);
        $rank=$this->rank($card);
        $captured=[];
        foreach($table as $tableCard)if($this->rank((string)$tableCard)===$rank)$captured[]=(string)$tableCard;
        if(in_array($rank,['Q','K'],true))return array_values(array_unique($captured));
        $target=$this->numericValue($card);
        $numeric=[];foreach($table as $index=>$tableCard)if(!in_array($this->rank((string)$tableCard),['Q','K','J'],true))$numeric[$index]=(string)$tableCard;
        $indexes=array_keys($numeric);$n=count($indexes);
        for($mask=1;$mask<(1<<min($n,16));$mask++){
            $sum=0;$subset=[];
            for($i=0;$i<$n;$i++)if($mask&(1<<$i)){$tc=$numeric[$indexes[$i]];$sum+=$this->numericValue($tc);$subset[]=$tc;}
            if($sum===$target)$captured=array_merge($captured,$subset);
        }
        return array_values(array_unique($captured));
    }

    private function qualifiesBasra(string $card,array $table): bool
    {
        if($this->rank($card)==='J')return false;
        if($this->isSevenDiamonds($card)){
            $sum=0;foreach($table as $t){if(in_array($this->rank((string)$t),['Q','K'],true))return false;$sum+=$this->numericValue((string)$t);}
            return $sum<=10;
        }
        return true;
    }
    private function removeCaptured(array $table,array $captured): array{foreach($captured as $card){$i=array_search($card,$table,true);if($i!==false)array_splice($table,$i,1);}return array_values($table);}
    private function isSweepCard(string $card): bool{return $this->rank($card)==='J'||$this->isSevenDiamonds($card);}
    private function isSevenDiamonds(string $card): bool{return $this->rank($card)==='7'&&$this->suit($card)==='diamonds';}
    private function numericValue(string $card): int{$r=$this->rank($card);return $r==='A'?1:(is_numeric($r)?(int)$r:0);}
}
