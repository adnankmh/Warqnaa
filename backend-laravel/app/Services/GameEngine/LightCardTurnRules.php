<?php
namespace App\Services\GameEngine;

class LightCardTurnRules implements GameRuleContract
{
    use UnifiedEngineHelpers;
    public function __construct(private string $key='oono') {}

    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players);
        $deck=DeckFactory::standard52(true);
        $hands=[]; foreach($players as $p)$hands[$p]=[];
        $cardsEach=(int)($options['cards_each'] ?? 7);
        for($i=0;$i<$cardsEach;$i++){
            foreach($players as $p){ if($deck) $hands[$p][]=array_shift($deck)->id(); }
        }
        foreach($hands as $p=>$h)$hands[$p]=$this->sortCards($h);
        $discard=[]; if($deck) $discard[]=array_shift($deck)->id();
        $top=end($discard) ?: null;
        return [
            'phase'=>'playing','game_type'=>$this->key,'players'=>$players,'turn'=>$players[0]??null,
            'hands'=>$hands,'deck'=>array_map(fn($c)=>$c->id(),$deck),'discard'=>$discard,'top_card'=>$top,
            'current_suit'=>$top?$this->suit($top):null,'current_rank'=>$top?$this->rank($top):null,
            'direction'=>1,'score'=>array_fill_keys($players,0),'messages'=>['بدأت '.$this->key.': العب ورقة تطابق النوع أو الرقم/القيمة، أو اسحب من الدك.'],
            'engine_quality'=>'light_card_real_v129'
        ];
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        if(in_array($action,['draw','draw_deck'],true)) return !empty($state['deck']);
        if($action==='pass') return !empty($state['drew_this_turn'][$playerId]) || empty($this->legalCards($state,$playerId));
        if(!in_array($action,['play_card','play'],true)) return false;
        $hand=$state['hands'][$playerId]??[];
        $card=$this->canonicalCard((string)($payload['card']??$payload['card_id']??$payload['id']??''),$hand);
        if(!$card) return false;
        if(empty($state['discard'])) return true;
        return $this->isLegalCard($state,$card);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='الحركة غير مقبولة: العب ورقة تطابق النوع أو الرقم/القيمة، أو اسحب من الدك.';
            return $state;
        }
        if(in_array($action,['draw','draw_deck'],true)){
            if(!empty($state['deck'])) $state['hands'][$playerId][]=array_shift($state['deck']);
            $state['hands'][$playerId]=$this->sortCards($state['hands'][$playerId]);
            $state['drew_this_turn'][$playerId]=true;
            $state['messages'][]=$this->label($playerId).' سحب ورقة.';
            return $state;
        }
        if($action==='pass'){
            unset($state['drew_this_turn'][$playerId]);
            $state['turn']=$this->next($state,$playerId);
            $state['messages'][]=$this->label($playerId).' مرر الدور.';
            return $state;
        }
        $hand=$state['hands'][$playerId]??[];
        $card=$this->canonicalCard((string)($payload['card']??$payload['card_id']??$payload['id']??''),$hand);
        $this->removeCard($state['hands'][$playerId],$card);
        $state['discard'][]=$card; $state['top_card']=$card; $state['current_suit']=$this->suit($card); $state['current_rank']=$this->rank($card);
        $state['messages'][]=$this->label($playerId).' لعب '.$card.'.';
        unset($state['drew_this_turn'][$playerId]);
        if(empty($state['hands'][$playerId])){
            $state['phase']='finished'; $state['winner']=$playerId; $state['messages'][]='انتهت اللعبة. الفائز: '.$this->label($playerId);
            return $state;
        }
        $state['turn']=$this->next($state,$playerId);
        return $state;
    }

    private function isLegalCard(array $state,string $card): bool
    {
        return $this->suit($card)===($state['current_suit']??null) || $this->rank($card)===($state['current_rank']??null);
    }

    private function legalCards(array $state,string $playerId): array
    {
        return array_values(array_filter($state['hands'][$playerId]??[], fn($c)=>$this->isLegalCard($state,(string)$c)));
    }

    private function canonicalCard(string $card,array $hand): ?string
    {
        if($card==='' || !$hand) return null;
        if(in_array($card,$hand,true)) return $card;
        $norm=$this->norm($card);
        foreach($hand as $h) if($this->norm((string)$h)===$norm) return (string)$h;
        return null;
    }

    private function norm(string $card): string
    {
        $c=strtolower(trim(str_replace([' ','-','/'],['_','_','_'],$card)));
        $map=['♣'=>'clubs','♧'=>'clubs','سنك'=>'clubs','شجرة'=>'clubs','♦'=>'diamonds','ديناري'=>'diamonds','♠'=>'spades','بستوني'=>'spades','♥'=>'hearts','كبة'=>'hearts'];
        foreach($map as $a=>$b)$c=str_replace($a,$b,$c);
        $c=trim(preg_replace('/_+/','_',$c),'_'); $p=explode('_',$c);
        return count($p)>=2?strtoupper($p[0]).'_'.end($p):strtoupper($c);
    }

    private function next(array $state,string $player): string
    {
        $players=array_values($state['players']??[]);
        if(!$players) return $player;
        $i=array_search($player,$players,true); if($i===false)$i=0;
        $dir=(int)($state['direction']??1);
        return $players[($i+$dir+count($players))%count($players)];
    }
    private function label(string $p): string { return str_replace(['user:','bot:'],['لاعب ','بوت '],$p); }
}
