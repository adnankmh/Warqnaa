<?php
namespace App\Services\GameEngine;

class UniversalSocialGameRules implements GameRuleContract
{
    public function __construct(private string $key='universal') {}

    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players);
        $score=[]; foreach($players as $p) $score[$p]=0;
        return [
            'phase'=>'playing','game_type'=>$this->key,'players'=>$players,'turn'=>$players[0] ?? null,'round'=>1,
            'score'=>$score,'moves'=>[],'board'=>[],'dice'=>null,'messages'=>['بدأت لعبة '.$this->key.' بمحرك Warqna v129 الآمن: حركة/تمرير/رمي نرد حسب نوع اللعبة.'],
            'engine_quality'=>'universal_safe_v129','move_limit'=>(int)($options['move_limit'] ?? 80)
        ];
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn'] ?? null) !== $playerId) return false;
        return in_array($this->normalizeAction($action),['move','roll_dice','play','pass','ready','claim','play_card','play_tile','select_card','draw','move_token'],true);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        $action=$this->normalizeAction($action);
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='الحركة غير متاحة الآن أو الدور ليس دورك.';
            return $state;
        }
        if($action==='roll_dice'){
            $state['dice']=[random_int(1,6), random_int(1,6)];
            $state['moves'][]=['player'=>$playerId,'action'=>$action,'dice'=>$state['dice'],'at'=>now()->toIso8601String()];
            $state['messages'][]=$this->label($playerId).' رمى النرد.';
        }else{
            $state['moves'][]=['player'=>$playerId,'action'=>$action,'payload'=>$payload,'at'=>now()->toIso8601String()];
            $state['score'][$playerId]=($state['score'][$playerId] ?? 0)+($action==='claim'?3:1);
            $state['board'][]=['player'=>$playerId,'action'=>$action,'payload'=>$payload];
            $state['messages'][]=$this->label($playerId).' نفّذ '.$action.'.';
        }
        $state['turn']=$this->nextPlayer($state['players'] ?? [],$playerId);
        $limit=(int)($state['move_limit'] ?? 80);
        if(count($state['moves'])>=$limit){arsort($state['score']);$state['phase']='finished'; $state['winner']=array_key_first($state['score']);$state['messages'][]='انتهت اللعبة بالحد الأعلى للحركات. الفائز: '.$this->label((string)$state['winner']);}
        return $state;
    }

    private function normalizeAction(string $action): string
    {
        return match($action){'roll'=>'roll_dice','move_prompt'=>'move_token','card'=>'play_card','domino'=>'play_tile',default=>$action};
    }
    private function nextPlayer(array $players,string $current): string
    {
        if(!$players) return $current; $i=array_search($current,$players,true); return $players[(($i===false?0:$i)+1)%count($players)];
    }
    private function label(string $p): string { return str_replace(['user:','bot:'],['لاعب ','بوت '],$p); }
}
