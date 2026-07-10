<?php
namespace App\Services\GameEngine;

/** Double-six Dominoes for two or four players, played in rounds to 100 points. */
class DominoRules implements GameRuleContract
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values(array_slice($players,0,4));
        if(!in_array(count($players),[2,4],true))$players=array_slice(array_pad($players,2,'bot:domino'),0,2);
        $state=[
            'phase'=>'playing','game_type'=>'domino','players'=>$players,'round'=>1,'target'=>(int)($options['target']??100),
            'score'=>array_fill_keys($players,0),'round_starter'=>null,
            'messages'=>['بدأت الدومينو بدبل-ستة: طابق الطرف المفتوح، واسحب عند عدم وجود حجر صالح في لعب لاعبين.'],
            'engine_quality'=>'domino_complete_v142',
        ];
        return $this->startRound($state,null);
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['phase']??null)!=='playing'||($state['turn']??null)!==$playerId)return false;
        if($action==='draw')return count($state['players']??[])===2&&!$this->hasLegalTile($state,$playerId)&&!empty($state['boneyard']);
        if($action==='pass')return !$this->hasLegalTile($state,$playerId)&&empty($state['boneyard']);
        if($action!=='play_tile')return false;
        $tile=$this->canonicalTile((string)($payload['tile']??$payload['domino']??$payload['id']??''),$state['hands'][$playerId]??[]);if(!$tile)return false;
        if(empty($state['board']))return true;
        [$a,$b]=$this->parts($tile);$side=$payload['side']??'right';
        return $side==='left'?($a===$state['left']||$b===$state['left']):($a===$state['right']||$b===$state['right']);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='حركة دومينو غير قانونية: العب حجراً مطابقاً، أو اسحب/مرر فقط عند عدم وجود تطابق.';return $state;
        }
        if($action==='draw'){
            $tile=array_shift($state['boneyard']);$state['hands'][$playerId][]=$tile;$state['messages'][]=$this->label($playerId).' سحب حجراً.';
            if(!$this->hasLegalTile($state,$playerId)&&empty($state['boneyard'])){
                $state['passes_in_row']=(int)($state['passes_in_row']??0)+1;$state['turn']=$this->nextPlayer($state['players'],$playerId);
            }
            return $state;
        }
        if($action==='pass'){
            $state['passes_in_row']=(int)($state['passes_in_row']??0)+1;$state['messages'][]=$this->label($playerId).' مرر لعدم وجود حركة.';
            if($state['passes_in_row']>=count($state['players']))return $this->finishRound($state,$this->lowestPipsPlayer($state));
            $state['turn']=$this->nextPlayer($state['players'],$playerId);return $state;
        }
        $tile=$this->canonicalTile((string)($payload['tile']??$payload['domino']??$payload['id']??''),$state['hands'][$playerId]);$side=$payload['side']??'right';
        $i=array_search($tile,$state['hands'][$playerId],true);if($i!==false)array_splice($state['hands'][$playerId],$i,1);
        [$a,$b]=$this->parts($tile);
        if(empty($state['board'])){$state['board'][]=['tile'=>$tile,'left'=>$a,'right'=>$b];$state['left']=$a;$state['right']=$b;}
        elseif($side==='left'){
            $outer=$a===$state['left']?$b:$a;array_unshift($state['board'],['tile'=>$tile,'left'=>$outer,'right'=>$state['left']]);$state['left']=$outer;
        }else{
            $outer=$a===$state['right']?$b:$a;$state['board'][]=['tile'=>$tile,'left'=>$state['right'],'right'=>$outer];$state['right']=$outer;
        }
        $state['passes_in_row']=0;$state['messages'][]=$this->label($playerId).' لعب '.$tile.'.';
        if(empty($state['hands'][$playerId]))return $this->finishRound($state,$playerId);
        $state['turn']=$this->nextPlayer($state['players'],$playerId);unset($state['last_error_message']);return $state;
    }

    public function onTurnTimeout(array $state): array
    {
        $player=(string)($state['turn']??'');$actions=$this->availableActions($state,$player);
        if(!$actions)return $state;
        // Prefer the legal tile with the largest pip sum.
        usort($actions,function($a,$b){$pa=isset($a['tile'])?array_sum($this->parts((string)$a['tile'])):0;$pb=isset($b['tile'])?array_sum($this->parts((string)$b['tile'])):0;return $pb<=>$pa;});
        $chosen=$actions[0];$type=$chosen['type'];unset($chosen['type']);$state['messages'][]='⏱️ نفّذ الكمبيوتر حركة دومينو قانونية.';
        return $this->apply($state,$player,$type,$chosen);
    }

    public function availableActions(array $state,string $playerId): array
    {
        if(($state['turn']??null)!==$playerId)return [];$out=[];
        foreach($state['hands'][$playerId]??[] as $tile){
            if(empty($state['board'])){$out[]=['type'=>'play_tile','tile'=>$tile,'side'=>'right'];continue;}
            [$a,$b]=$this->parts((string)$tile);
            if($a===$state['left']||$b===$state['left'])$out[]=['type'=>'play_tile','tile'=>$tile,'side'=>'left'];
            if($a===$state['right']||$b===$state['right'])$out[]=['type'=>'play_tile','tile'=>$tile,'side'=>'right'];
        }
        if($out)return $out;
        if(count($state['players'])===2&&!empty($state['boneyard']))return [['type'=>'draw']];
        return [['type'=>'pass']];
    }

    private function startRound(array $state,?string $preferredStarter): array
    {
        $tiles=[];for($i=0;$i<=6;$i++)for($j=$i;$j<=6;$j++)$tiles[]=$i.'-'.$j;$tiles=DeckFactory::secureShuffle($tiles);
        $state['hands']=array_fill_keys($state['players'],[]);
        foreach($state['players'] as $player)$state['hands'][$player]=array_splice($tiles,0,7);
        $state['board']=[];$state['left']=null;$state['right']=null;$state['boneyard']=$tiles;$state['passes_in_row']=0;
        $starter=$preferredStarter&&in_array($preferredStarter,$state['players'],true)?$preferredStarter:$this->openingPlayer($state['hands'],$state['players']);
        $state['turn']=$starter;$state['round_starter']=$starter;
        $state['messages'][]='الجولة '.$state['round'].' — يبدأ '.$this->label($starter).'.';return $state;
    }

    private function finishRound(array $state,string $winner): array
    {
        $award=0;foreach($state['hands'] as $player=>$hand)if($player!==$winner)$award+=$this->pipSum($hand);
        $state['score'][$winner]=(int)($state['score'][$winner]??0)+$award;
        $state['messages'][]=$this->label($winner).' فاز بالجولة وحصل على '.$award.' نقطة.';
        if((int)$state['score'][$winner]>=(int)$state['target']){
            $state['phase']='finished';$state['winner']=$winner;$state['messages'][]='انتهت الدومينو عند '.$state['target'].' نقطة؛ الفائز '.$this->label($winner).'.';return $state;
        }
        $state['round']=(int)$state['round']+1;return $this->startRound($state,$winner);
    }

    private function openingPlayer(array $hands,array $players): string
    {
        for($double=6;$double>=0;$double--)foreach($players as $p)if(in_array($double.'-'.$double,$hands[$p]??[],true))return $p;
        $best=$players[0];$bestValue=-1;foreach($players as $p)foreach($hands[$p]??[] as $tile){$v=array_sum($this->parts((string)$tile));if($v>$bestValue){$bestValue=$v;$best=$p;}}return $best;
    }
    private function lowestPipsPlayer(array $state): string{$best=$state['players'][0];$min=PHP_INT_MAX;foreach($state['hands'] as $p=>$hand){$sum=$this->pipSum($hand);if($sum<$min){$min=$sum;$best=$p;}}return $best;}
    private function pipSum(array $hand): int{$sum=0;foreach($hand as $tile)$sum+=array_sum($this->parts((string)$tile));return $sum;}
    private function canonicalTile(string $tile,array $hand): ?string{$tile=str_replace(['|','_','/',' '],'-',trim($tile));if(in_array($tile,$hand,true))return $tile;if(str_contains($tile,'-')){[$a,$b]=$this->parts($tile);$rev=$b.'-'.$a;if(in_array($rev,$hand,true))return $rev;$norm=min($a,$b).'-'.max($a,$b);if(in_array($norm,$hand,true))return $norm;}return null;}
    private function parts(string $tile): array{$p=array_map('intval',explode('-',str_replace(['|','_','/',' '],'-',$tile)));return [$p[0]??0,$p[1]??0];}
    private function hasLegalTile(array $state,string $player): bool{foreach($this->availableActionsRaw($state,$player) as $action)if($action['type']==='play_tile')return true;return false;}
    private function availableActionsRaw(array $state,string $player): array{$out=[];foreach($state['hands'][$player]??[] as $tile){if(empty($state['board'])){$out[]=['type'=>'play_tile'];continue;}[$a,$b]=$this->parts((string)$tile);if($a===$state['left']||$b===$state['left']||$a===$state['right']||$b===$state['right'])$out[]=['type'=>'play_tile'];}return $out;}
    private function nextPlayer(array $players,string $current): string{$i=array_search($current,$players,true);return $players[(($i===false?0:$i)+1)%max(1,count($players))]??$current;}
    private function label(string $player): string{return str_replace(['user:','bot:'],['لاعب ','بوت '],$player);}
}
