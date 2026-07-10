<?php
namespace App\Services\GameEngine;

class BoardDiceRules implements GameRuleContract
{
    public function __construct(private string $key='ludo') {}

    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values($players);
        $tokens=[]; foreach($players as $p){$tokens[$p]=[0=>-1,1=>-1,2=>-1,3=>-1];}
        return ['phase'=>'playing','game_type'=>$this->key,'players'=>$players,'turn'=>$players[0]??null,'tokens'=>$tokens,'dice'=>null,'score'=>array_fill_keys($players,0),'moves'=>[],'messages'=>['لعبة رقعة Warqna v129: النرد من السيرفر، ثم اختر الحركة المناسبة.'],'engine_quality'=>'board_dice_real_v129'];
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        $action=$this->normalizeAction($action);
        if($action==='roll_dice') return empty($state['dice']);
        if($action==='pass') return !empty($state['dice']);
        if($action==='move_token') return !empty($state['dice']);
        if($action==='move') return !empty($state['dice']) || isset($payload['steps']);
        return false;
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        $action=$this->normalizeAction($action);
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error_message']='حركة رقعة غير مقبولة الآن: ارمِ النرد ثم حرّك قطعة أو مرر.'; return $state; }
        if($action==='roll_dice'){
            $state['dice']=random_int(1,6);
            $state['messages'][]=$this->label($playerId).' رمى النرد: '.$state['dice'];
            return $state;
        }
        if($action==='pass'){
            $state['dice']=null; $state['turn']=$this->nextPlayer($state['players'],$playerId); $state['messages'][]=$this->label($playerId).' مرر الحركة.';
            return $state;
        }
        $token=(int)($payload['token']??$payload['piece']??0);
        $steps=(int)($payload['steps']??$state['dice']??0);
        if($steps<=0){$state['last_error_message']='لا توجد خطوات متاحة. ارمِ النرد أولًا.'; return $state;}
        $pos=$state['tokens'][$playerId][$token]??-1;
        if($pos<0 && $steps!==6){
            $state['last_error_message']='تحتاج 6 لإخراج القطعة من البيت.';
            return $state;
        }
        $state['tokens'][$playerId][$token]=($pos<0)?0:min(57,$pos+$steps);
        if($state['tokens'][$playerId][$token]>=57){
            $state['score'][$playerId]=($state['score'][$playerId]??0)+1;
            $state['messages'][]=$this->label($playerId).' أدخل قطعة للنهاية.';
        } else {
            $state['messages'][]=$this->label($playerId).' حرّك قطعة '.$token.' '.$steps.' خطوات.';
        }
        $state['moves'][]=['player'=>$playerId,'token'=>$token,'steps'=>$steps,'at'=>now()->toIso8601String()];
        $finished=count(array_filter($state['tokens'][$playerId]??[],fn($p)=>$p>=57));
        if($finished>=4){$state['phase']='finished';$state['winner']=$playerId;$state['messages'][]='انتهت اللعبة. الفائز: '.$this->label($playerId);return $state;}
        $state['dice']=null;
        if($steps!==6) $state['turn']=$this->nextPlayer($state['players'],$playerId);
        return $state;
    }

    private function normalizeAction(string $action): string
    {
        return match($action){'roll'=>'roll_dice','move_prompt'=>'move_token','move_piece'=>'move_token',default=>$action};
    }
    private function nextPlayer(array $players,string $current): string
    {
        if(!$players) return $current; $i=array_search($current,$players,true); return $players[(($i===false?0:$i)+1)%count($players)];
    }
    private function label(string $p): string { return str_replace(['user:','bot:'],['لاعب ','بوت '],$p); }
}
