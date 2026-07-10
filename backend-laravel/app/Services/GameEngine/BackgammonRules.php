<?php
namespace App\Services\GameEngine;

/**
 * Server-authoritative two-player Backgammon core.
 * Enforces movement direction, blocked points, hits, bar priority, legal entry,
 * home-board bearing off, doubles and automatic turn completion.
 */
class BackgammonRules implements GameRuleContract
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values(array_slice($players,0,2));
        while(count($players)<2)$players[]='bot:backgammon_'.count($players);
        return [
            'phase'=>'playing','game_type'=>'backgammon','players'=>$players,'turn'=>$players[0],
            'directions'=>[$players[0]=>1,$players[1]=>-1],
            'dice'=>[],'moves_left'=>[],
            'points'=>$this->initialBoard($players),
            'bar'=>array_fill_keys($players,0),'borne_off'=>array_fill_keys($players,0),
            'score'=>array_fill_keys($players,0),
            'messages'=>['بدأت طاولة الزهر: ارمِ النرد، أدخل الحجر المضروب أولاً، ثم حرّك باتجاه بيتك واخرج الأحجار قانونياً.'],
            'engine_quality'=>'backgammon_legal_core_v142',
        ];
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['phase']??null)!=='playing'||($state['turn']??null)!==$playerId)return false;
        if($action==='roll')return empty($state['moves_left'])&&empty($state['dice']);
        if($action==='pass')return !empty($state['moves_left'])&&empty($this->legalMoves($state,$playerId));
        if($action!=='move'||empty($state['moves_left']))return false;
        $from=$this->normalizePoint($payload['from']??null,$state,$playerId,true);
        $to=$this->normalizePoint($payload['to']??null,$state,$playerId,false);
        if($from===null||$to===null)return false;
        foreach($this->legalMoves($state,$playerId) as $move){
            if($move['from']===$from&&$move['to']===$to)return true;
        }
        return false;
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='حركة غير قانونية: التزم باتجاهك، أدخل أحجار البار أولاً، ولا تهبط على نقطة مغلقة.';
            return $state;
        }
        if($action==='roll'){
            $d1=random_int(1,6);$d2=random_int(1,6);
            $state['dice']=[$d1,$d2];
            $state['moves_left']=$d1===$d2?[$d1,$d1,$d1,$d1]:[$d1,$d2];
            $state['messages'][]=$this->name($playerId).' رمى النرد: '.$d1.'-'.$d2.'.';
            if(empty($this->legalMoves($state,$playerId))){
                $state['messages'][]='لا توجد حركة قانونية؛ انتهى الدور تلقائياً.';
                return $this->finishTurn($state,$playerId);
            }
            return $state;
        }
        if($action==='pass'){
            $state['messages'][]=$this->name($playerId).' لا يملك حركة قانونية.';
            return $this->finishTurn($state,$playerId);
        }

        $from=$this->normalizePoint($payload['from'],$state,$playerId,true);
        $to=$this->normalizePoint($payload['to'],$state,$playerId,false);
        $chosen=null;
        foreach($this->legalMoves($state,$playerId) as $move){
            if($move['from']===$from&&$move['to']===$to){$chosen=$move;break;}
        }
        if(!$chosen){$state['last_error_message']='لم يعد هذا المسار متاحاً.';return $state;}
        $this->executeMove($state,$playerId,$chosen);
        $dieIndex=array_search($chosen['die'],$state['moves_left'],true);
        if($dieIndex!==false)array_splice($state['moves_left'],$dieIndex,1);
        $state['messages'][]=$this->name($playerId).' حرّك من '.$this->pointLabel($from,$state,$playerId).' إلى '.$this->pointLabel($to,$state,$playerId).'.';

        if(($state['borne_off'][$playerId]??0)>=15){
            $opponent=$this->next($state['players'],$playerId);
            $multiplier=1;
            if((int)($state['borne_off'][$opponent]??0)===0){
                $multiplier=$this->isBackgammonLoss($state,$playerId,$opponent)?3:2;
            }
            $state['phase']='finished';$state['winner']=$playerId;$state['win_multiplier']=$multiplier;
            $state['score'][$playerId]=($state['score'][$playerId]??0)+$multiplier;
            $state['messages'][]='انتهت المباراة. الفائز: '.$this->name($playerId).' ('.($multiplier===3?'باكغمون':($multiplier===2?'غامون':'فوز عادي')).').';
            return $state;
        }

        if(empty($state['moves_left'])||empty($this->legalMoves($state,$playerId)))return $this->finishTurn($state,$playerId);
        unset($state['last_error_message']);
        return $state;
    }

    public function onTurnTimeout(array $state): array
    {
        $player=(string)($state['turn']??'');
        if($player==='')return $state;
        if(empty($state['moves_left'])){
            $state['messages'][]='⏱️ انتهى الوقت؛ رمى الكمبيوتر النرد.';
            return $this->apply($state,$player,'roll',[]);
        }
        $moves=$this->legalMoves($state,$player);
        if($moves){
            // Prefer hits, then bearing-off, then the move that advances farthest.
            usort($moves,fn($a,$b)=>[$b['hit']?1:0,$b['bear_off']?1:0,$b['die']]<=>[$a['hit']?1:0,$a['bear_off']?1:0,$a['die']]);
            $state['messages'][]='⏱️ انتهى الوقت؛ نفّذ الكمبيوتر حركة قانونية.';
            return $this->apply($state,$player,'move',['from'=>$moves[0]['from'],'to'=>$moves[0]['to']]);
        }
        return $this->finishTurn($state,$player);
    }

    /** @return array<int,array<string,mixed>> */
    public function availableActions(array $state,string $playerId): array
    {
        if(($state['phase']??null)!=='playing'||($state['turn']??null)!==$playerId)return [];
        if(empty($state['moves_left']))return [['type'=>'roll']];
        $moves=$this->legalMoves($state,$playerId);
        if(!$moves)return [['type'=>'pass']];
        return array_map(fn($m)=>['type'=>'move']+$m,$moves);
    }

    /** @return array<int,array{from:int,to:int,die:int,hit:bool,bear_off:bool}> */
    private function legalMoves(array $state,string $player): array
    {
        $moves=[];$dice=array_values(array_unique(array_map('intval',$state['moves_left']??[])));
        if(!$dice)return [];
        $direction=$this->direction($state,$player);
        $bar=(int)($state['bar'][$player]??0);
        foreach($dice as $die){
            if($bar>0){
                $from=$direction===1?0:25;$to=$direction===1?$die:25-$die;
                if($this->destinationOpen($state,$player,$to))$moves[]=$this->moveInfo($state,$player,$from,$to,$die);
                continue;
            }
            foreach($state['points']??[] as $from=>$point){
                $from=(int)$from;
                if(($point['owner']??null)!==$player||(int)($point['count']??0)<1)continue;
                $to=$from+$direction*$die;
                if($to>=1&&$to<=24){
                    if($this->destinationOpen($state,$player,$to))$moves[]=$this->moveInfo($state,$player,$from,$to,$die);
                }elseif($this->canBearOff($state,$player,$from,$die)){
                    $to=$direction===1?25:0;
                    $moves[]=['from'=>$from,'to'=>$to,'die'=>$die,'hit'=>false,'bear_off'=>true];
                }
            }
        }
        return $moves;
    }

    private function executeMove(array &$state,string $player,array $move): void
    {
        $from=(int)$move['from'];$to=(int)$move['to'];$direction=$this->direction($state,$player);
        if(($direction===1&&$from===0)||($direction===-1&&$from===25)){
            $state['bar'][$player]=max(0,(int)$state['bar'][$player]-1);
        }else{
            $state['points'][$from]['count']--;
            if((int)$state['points'][$from]['count']<=0)$state['points'][$from]=['owner'=>null,'count'=>0];
        }
        if($move['bear_off']){
            $state['borne_off'][$player]=(int)($state['borne_off'][$player]??0)+1;
            return;
        }
        $destination=$state['points'][$to]??['owner'=>null,'count'=>0];
        if(($destination['owner']??null)!==null&&$destination['owner']!==$player&&(int)$destination['count']===1){
            $opponent=(string)$destination['owner'];
            $state['bar'][$opponent]=(int)($state['bar'][$opponent]??0)+1;
            $state['points'][$to]=['owner'=>$player,'count'=>1];
            $state['messages'][]=$this->name($player).' ضرب حجراً لـ '.$this->name($opponent).'.';
        }else{
            $count=(($destination['owner']??null)===$player)?(int)$destination['count']:0;
            $state['points'][$to]=['owner'=>$player,'count'=>$count+1];
        }
    }

    private function canBearOff(array $state,string $player,int $from,int $die): bool
    {
        if(!$this->allInHome($state,$player))return false;
        $direction=$this->direction($state,$player);
        $distance=$direction===1?25-$from:$from;
        if($die===$distance)return true;
        if($die<$distance)return false;
        // A larger die may bear off only when there is no checker farther from the exit.
        if($direction===1){
            for($p=19;$p<$from;$p++)if(($state['points'][$p]['owner']??null)===$player&&(int)$state['points'][$p]['count']>0)return false;
        }else{
            for($p=6;$p>$from;$p--)if(($state['points'][$p]['owner']??null)===$player&&(int)$state['points'][$p]['count']>0)return false;
        }
        return true;
    }

    private function allInHome(array $state,string $player): bool
    {
        if((int)($state['bar'][$player]??0)>0)return false;
        $direction=$this->direction($state,$player);
        foreach($state['points']??[] as $point=>$data){
            if(($data['owner']??null)!==$player||(int)($data['count']??0)<1)continue;
            if($direction===1&&((int)$point<19||(int)$point>24))return false;
            if($direction===-1&&((int)$point<1||(int)$point>6))return false;
        }
        return true;
    }

    private function destinationOpen(array $state,string $player,int $to): bool
    {
        $point=$state['points'][$to]??['owner'=>null,'count'=>0];
        return ($point['owner']??null)===null||$point['owner']===$player||(int)($point['count']??0)<=1;
    }

    private function moveInfo(array $state,string $player,int $from,int $to,int $die): array
    {
        $point=$state['points'][$to]??['owner'=>null,'count'=>0];
        return ['from'=>$from,'to'=>$to,'die'=>$die,'hit'=>(($point['owner']??null)!==null&&$point['owner']!==$player&&(int)$point['count']===1),'bear_off'=>false];
    }

    private function finishTurn(array $state,string $player): array
    {
        $state['turn']=$this->next($state['players'],$player);$state['dice']=[];$state['moves_left']=[];
        unset($state['last_error_message']);return $state;
    }

    private function isBackgammonLoss(array $state,string $winner,string $loser): bool
    {
        if((int)($state['bar'][$loser]??0)>0)return true;
        $winnerDirection=$this->direction($state,$winner);
        $range=$winnerDirection===1?range(19,24):range(1,6);
        foreach($range as $point)if(($state['points'][$point]['owner']??null)===$loser&&(int)$state['points'][$point]['count']>0)return true;
        return false;
    }

    private function initialBoard(array $players): array
    {
        $p1=$players[0];$p2=$players[1];$board=[];
        foreach(range(1,24) as $i)$board[$i]=['owner'=>null,'count'=>0];
        $board[1]=['owner'=>$p1,'count'=>2];$board[12]=['owner'=>$p1,'count'=>5];$board[17]=['owner'=>$p1,'count'=>3];$board[19]=['owner'=>$p1,'count'=>5];
        $board[24]=['owner'=>$p2,'count'=>2];$board[13]=['owner'=>$p2,'count'=>5];$board[8]=['owner'=>$p2,'count'=>3];$board[6]=['owner'=>$p2,'count'=>5];
        return $board;
    }

    private function normalizePoint(mixed $value,array $state,string $player,bool $from): ?int
    {
        if(is_string($value)&&strtolower($value)==='bar')return $this->direction($state,$player)===1?0:25;
        if(is_string($value)&&in_array(strtolower($value),['off','home'],true))return $this->direction($state,$player)===1?25:0;
        if(!is_numeric($value))return null;$point=(int)$value;
        return $point>=0&&$point<=25?$point:null;
    }
    private function direction(array $state,string $player): int{return (int)($state['directions'][$player]??(($state['players'][0]??null)===$player?1:-1));}
    private function next(array $players,string $player): string{$i=array_search($player,$players,true);return $players[(($i===false?0:$i)+1)%max(1,count($players))]??$player;}
    private function name(string $player): string{return str_replace(['user:','bot:'],['لاعب ','بوت '],$player);}
    private function pointLabel(int $point,array $state,string $player): string{if($point===0||$point===25)return (($state['bar'][$player]??0)>0?'البار':'الخارج');return (string)$point;}
}
