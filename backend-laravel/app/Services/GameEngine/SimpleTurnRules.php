<?php
namespace App\Services\GameEngine;
class SimpleTurnRules implements GameRuleContract
{
    public function __construct(private string $type='training'){}
    public function initialState(array $players,array $options=[]): array { $players=array_values($players); return ['phase'=>'playing','game_type'=>$this->type,'players'=>$players,'turn'=>$players[0]??null,'score'=>array_fill_keys($players,0),'moves'=>[],'messages'=>[$this->type.' جاهزة: محرك دور بسيط للتدريب إلى حين إضافة لوحة رسومية كاملة.']]; }
    public function validate(array $state,string $playerId,string $action,array $payload): bool { return ($state['turn']??null)===$playerId && in_array($action,['move','pass'],true); }
    public function apply(array $state,string $playerId,string $action,array $payload): array { if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_action'; return $state;} $state['moves'][]=['player'=>$playerId,'action'=>$action,'payload'=>$payload]; $state['score'][$playerId]=($state['score'][$playerId]??0)+1; $players=$state['players']; $i=array_search($playerId,$players,true); $state['turn']=$players[(($i===false?0:$i)+1)%max(1,count($players))]; if(count($state['moves'])>=40){$state['phase']='finished'; $state['winner']=array_keys($state['score'],max($state['score']))[0]??null;} return $state; }
}
