<?php
namespace App\Services\GameEngine;

class TrixRules extends AbstractCardRules
{
    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players); $deck=DeckFactory::standard52(true); [$hands,$deck]=$this->deal($players,$deck,13);
        $partner=!empty($options['partners']);
        return ['phase'=>'choose_contract','game_type'=>$partner?'trix_partner':'trix','players'=>$players,'teams'=>$this->teams($players),'turn'=>$players[0]??null,'hands'=>$hands,'contract'=>null,'available_contracts'=>['king_hearts','queens','diamonds','hearts','tricks','trix'],'trick'=>[],'last_trick'=>[],'round_tricks'=>array_fill_keys($players,0),'score'=>$partner?['teamA'=>0,'teamB'=>0]:array_fill_keys($players,0),'messages'=>['تركس: اختر عقد المملكة ثم العب مع إلزام اتباع النوع. العقود السلبية تحسب عقوبات، وعقد تركس يحسب حسب ترتيب التخلص من الورق.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        if(($state['phase']??'')==='choose_contract') return $action==='choose_contract' && in_array($payload['contract']??'', $state['available_contracts']??[], true);
        if(($state['phase']??'')==='playing'){
            $card=(string)($payload['card']??''); if($action!=='play_card'||!in_array($card,$state['hands'][$playerId]??[],true)) return false;
            if(!empty($state['trick'])){ $lead=$this->suit((string)reset($state['trick'])); if($this->hasSuit($state['hands'][$playerId],$lead)&&$this->suit($card)!==$lead) return false; }
            return true;
        }
        return false;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_trix_action'; return $state; }
        if(($state['phase']??'')==='choose_contract'){ $state['contract']=$payload['contract']; $state['phase']='playing'; $state['messages'][]='تم اختيار عقد '.$this->contractName($payload['contract']).'.'; return $state; }
        $players=$state['players']; $card=(string)$payload['card']; $this->removeCard($state['hands'][$playerId],$card); $state['trick'][$playerId]=$card;
        if(($state['contract']??'')==='trix' && empty($state['hands'][$playerId])){ $key=$this->scoreKey($state,$playerId); $left=count(array_filter($state['players'],fn($p)=>!empty($state['hands'][$p]))); $bonus=[3=>200,2=>150,1=>100,0=>50][$left]??50; $state['score'][$key]=($state['score'][$key]??0)+$bonus; $state['messages'][]=$this->labelPlayer($playerId).' أنهى أوراقه في تركس وربح '.$bonus.' نقطة.'; }
        if(count($state['trick'])>=count($players)){ $winner=$this->trickWinner($state['trick']); $pen=$this->penalty($state['contract'],$state['trick']); $key=$this->scoreKey($state,$winner); $state['score'][$key]=($state['score'][$key]??0)+$pen; $state['round_tricks'][$winner]++; $state['last_trick']=$state['trick']; $state['trick']=[]; $state['turn']=$winner; $state['messages'][]='الفائز باللفة '.$this->labelPlayer($winner).'، نتيجة اللفة: '.$pen; }
        else $state['turn']=$this->playerKeyNext($players,$playerId);
        if($this->allHandsEmpty($state['hands'])){ $state['phase']='finished'; $state['messages'][]='انتهى عقد تركس وتم احتساب النقاط.'; }
        return $state;
    }
    private function scoreKey(array $state,string $player): string { return ($state['game_type']??'')==='trix_partner' ? $this->teamOf($state,$player) : $player; }
    private function penalty(?string $contract,array $trick): int
    {
        $p=0; foreach($trick as $c){ if($contract==='king_hearts'&&$c==='K_hearts')$p-=75; if($contract==='queens'&&$this->rank($c)==='Q')$p-=25; if($contract==='diamonds'&&$this->suit($c)==='diamonds')$p-=10; if($contract==='hearts'&&$this->suit($c)==='hearts')$p-=10; if($contract==='tricks')$p-=15; }
        return $p;
    }
    private function contractName($c): string { return ['king_hearts'=>'شيخ الكبة','queens'=>'البنات','diamonds'=>'الدنانير','hearts'=>'الكبة','tricks'=>'اللطوش','trix'=>'تركس'][$c]??$c; }
}
