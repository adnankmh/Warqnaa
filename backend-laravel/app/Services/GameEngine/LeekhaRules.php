<?php
namespace App\Services\GameEngine;

class LeekhaRules extends AbstractCardRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values($players); $deck=DeckFactory::standard52(true); [$hands,$deck]=$this->deal($players,$deck,13);
        return ['phase'=>'playing','game_type'=>'leekha','players'=>$players,'turn'=>$players[0]??null,'hands'=>$hands,'trick'=>[],'last_trick'=>[],'score'=>array_fill_keys($players,0),'round_penalties'=>array_fill_keys($players,0),'messages'=>['ليخة: اتبع النوع وتجنب الليخات وأوراق العقوبة. آخذ اللفة يتحمل عقوبتها، والفائز الأقل عقوبة.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false; $card=(string)($payload['card']??''); if($action!=='play_card'||!in_array($card,$state['hands'][$playerId]??[],true)) return false;
        if(!empty($state['trick'])){ $lead=$this->suit((string)reset($state['trick'])); if($this->hasSuit($state['hands'][$playerId],$lead)&&$this->suit($card)!==$lead) return false; }
        return true;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_leekha_action'; return $state; }
        $players=$state['players']; $card=(string)$payload['card']; $this->removeCard($state['hands'][$playerId],$card); $state['trick'][$playerId]=$card;
        if(count($state['trick'])>=count($players)){ $winner=$this->trickWinner($state['trick']); $pen=$this->penalty($state['trick']); $state['score'][$winner]=($state['score'][$winner]??0)+$pen; $state['round_penalties'][$winner]=($state['round_penalties'][$winner]??0)+$pen; $state['last_trick']=$state['trick']; $state['trick']=[]; $state['turn']=$winner; $state['messages'][]=$this->labelPlayer($winner).' أخذ اللفة وعليه '.$pen.' نقطة.'; }
        else $state['turn']=$this->playerKeyNext($players,$playerId);
        if($this->allHandsEmpty($state['hands'])){ $state['phase']='finished'; $max=max($state['score']); $state['winner']=array_keys($state['score'],$max)[0]??null; $state['messages'][]='انتهت الليخة. الفائز الأقل عقوبة: '.$this->labelPlayer($state['winner']??''); }
        return $state;
    }
    private function penalty(array $trick): int { $p=0; foreach($trick as $c){ if($this->suit($c)==='hearts')$p-=1; if(in_array($c,['Q_spades','Q_diamonds'],true))$p-=13; } return $p; }
}
