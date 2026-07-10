<?php
namespace App\Services\GameEngine;

class EstimationRules extends AbstractCardRules
{
    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players); $deck=DeckFactory::standard52(true); [$hands,$deck]=$this->deal($players,$deck,13);
        return ['phase'=>'bidding','game_type'=>'estimation','players'=>$players,'turn'=>$players[0]??null,'hands'=>$hands,'bids'=>[],'trick'=>[],'last_trick'=>[],'round_tricks'=>array_fill_keys($players,0),'score'=>array_fill_keys($players,0),'target'=>(int)($options['target']??100),'lead_player'=>null,'round'=>1,'messages'=>['استيميشن: كل لاعب يطلب عدد اللمات المتوقع من 0 إلى 13، ثم يحاول تحقيقه بدقة.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        if(($state['phase']??'')==='bidding') return $action==='bid' && is_numeric($payload['value']??null) && (int)$payload['value']>=0 && (int)$payload['value']<=13;
        if(($state['phase']??'')==='playing'){
            $card=(string)($payload['card']??''); if($action!=='play_card'||!in_array($card,$state['hands'][$playerId]??[],true)) return false;
            if(!empty($state['trick'])){ $lead=$this->suit((string)reset($state['trick'])); if($this->hasSuit($state['hands'][$playerId],$lead)&&$this->suit($card)!==$lead) return false; }
            return true;
        }
        return false;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_estimation_action'; return $state; }
        $players=$state['players'];
        if(($state['phase']??'')==='bidding'){
            $v=(int)$payload['value']; $state['bids'][$playerId]=$v; $state['messages'][]=$this->labelPlayer($playerId).' طلب '.$v.' لمّة.';
            if(count($state['bids'])>=count($players)){ $state['phase']='playing'; $state['turn']=$players[0]; $state['lead_player']=$players[0]; $state['messages'][]='انتهت مرحلة الطلب. اللعب يبدأ الآن مع إلزام اتباع النوع.'; }
            else $state['turn']=$this->playerKeyNext($players,$playerId);
            return $state;
        }
        $card=(string)$payload['card']; $this->removeCard($state['hands'][$playerId],$card); $state['trick'][$playerId]=$card; $state['messages'][]=$this->labelPlayer($playerId).' لعب '.$this->rank($card).' '.$this->suitName($this->suit($card));
        if(count($state['trick'])>=count($players)){ $winner=$this->trickWinner($state['trick']); $state['round_tricks'][$winner]++; $state['last_trick']=$state['trick']; $state['trick']=[]; $state['turn']=$winner; $state['lead_player']=$winner; $state['messages'][]='صاحب اللمة: '.$this->labelPlayer($winner); }
        else $state['turn']=$this->playerKeyNext($players,$playerId);
        if($this->allHandsEmpty($state['hands'])) return $this->scoreRound($state);
        return $state;
    }
    private function scoreRound(array $state): array
    {
        foreach($state['players'] as $p){ $bid=(int)($state['bids'][$p]??0); $got=(int)($state['round_tricks'][$p]??0); $delta=($bid===$got) ? (10+$bid*2) : (-abs($bid-$got)*2); $state['score'][$p]=($state['score'][$p]??0)+$delta; $state['messages'][]=$this->labelPlayer($p).': طلب '.$bid.' وأخذ '.$got.' → '.($delta>=0?'+':'').$delta; }
        $state['phase']='finished'; $state['winner']=array_keys($state['score'],max($state['score']))[0]??null; $state['messages'][]='انتهت جولة الاستيميشن. المتصدر: '.$this->labelPlayer($state['winner']??''); return $state;
    }
}
