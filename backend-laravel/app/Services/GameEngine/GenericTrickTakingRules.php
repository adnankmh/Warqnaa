<?php
namespace App\Services\GameEngine;

class GenericTrickTakingRules implements GameRuleContract
{
    use UnifiedEngineHelpers;
    public function __construct(private string $gameKey='trick_taking') {}

    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players);
        $deal=(int)($options['deal'] ?? (count($players)===4?13:intdiv(52,max(1,count($players)))));
        $balanced=DeckFactory::balancedHands($players,$deal);
        $hands=[]; foreach($balanced as $p=>$cards)$hands[$p]=$this->sortCards(array_map(fn($c)=>$c->id(),$cards));
        return ['phase'=>'playing','game_type'=>$this->gameKey,'players'=>array_values($players),'turn'=>$players[0]??null,'hands'=>$hands,'trick'=>[],'last_trick'=>[],'score'=>array_fill_keys($players,0),'round'=>1,'trump'=>$options['trump']??null,'messages'=>['بدأت لعبة '.$this->gameKey.' بنظام اللمّات.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId || $action!=='play_card') return false;
        $hand=$state['hands'][$playerId]??[];
        $card=$this->canonicalCard((string)($payload['card']??$payload['card_id']??$payload['id']??''),$hand);
        if(!$card) return false;
        if(!empty($state['trick'])){
            $lead=$this->suit((string)reset($state['trick'])); $has=false; foreach($hand as $c){if($this->suit($c)===$lead){$has=true;break;}}
            if($has && $this->suit($card)!==$lead) return false;
        }
        return true;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error_message']='الحركة غير مقبولة: اتبع نوع أول ورقة إذا كان لديك منها.'; return $state; }
        $card=$this->canonicalCard((string)($payload['card']??$payload['card_id']??$payload['id']??''),$state['hands'][$playerId]??[]);
        $this->removeCard($state['hands'][$playerId],$card); $state['trick'][$playerId]=$card;
        if(count($state['trick'])>=count($state['players'])){
            $winner=$this->winner($state['trick'],$state['trump']??null); $state['score'][$winner]=($state['score'][$winner]??0)+1; $state['last_trick']=$state['trick']; $state['trick']=[]; $state['turn']=$winner; $state['messages'][]='الفائز باللمة: '.$winner;
            $remaining=0; foreach($state['hands']??[] as $h)$remaining+=count($h);
            if($remaining===0){$state['phase']='finished'; arsort($state['score']); $state['winner']=array_key_first($state['score']); $state['messages'][]='انتهت الجولة. الفائز: '.$state['winner'];}
        } else $state['turn']=$this->nextPlayer($state['players'],$playerId);
        return $state;
    }
    private function canonicalCard(string $card,array $hand): ?string
    {
        if($card===''||!$hand)return null;
        if(in_array($card,$hand,true))return $card;
        $norm=$this->normCard($card);
        foreach($hand as $h) if($this->normCard((string)$h)===$norm) return (string)$h;
        return null;
    }
    private function normCard(string $card): string
    {
        $c=strtolower(trim(str_replace([' ','-','/'],['_','_','_'],$card)));
        $map=['♣'=>'clubs','♧'=>'clubs','سنك'=>'clubs','شجرة'=>'clubs','♦'=>'diamonds','ديناري'=>'diamonds','♠'=>'spades','بستوني'=>'spades','♥'=>'hearts','كبة'=>'hearts'];
        foreach($map as $a=>$b)$c=str_replace($a,$b,$c);
        $c=trim(preg_replace('/_+/','_',$c),'_'); $p=explode('_',$c);
        return count($p)>=2?strtoupper($p[0]).'_'.end($p):strtoupper($c);
    }

    private function winner(array $trick,?string $trump): string
    {
        $lead=$this->suit((string)reset($trick)); $best=array_key_first($trick); $score=-1;
        foreach($trick as $p=>$c){$s=($this->suit($c)===$trump?100:($this->suit($c)===$lead?50:0))+($this->rankPower[$this->rank($c)]??0); if($s>$score){$score=$s;$best=$p;}}
        return $best;
    }
}
