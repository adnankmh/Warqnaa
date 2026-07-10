<?php
namespace App\Services\GameEngine;

/**
 * Server-authoritative four-player/two-partnership Jackaroo core.
 * Implements four-card rounds, A/K start, A 1-or-11 movement, J swaps,
 * split-seven movement, backward four, captures, protected bases, safety lanes,
 * partner assistance after finishing and automatic legal bot turns.
 */
class JackarooRules extends AbstractCardRules
{
    private array $forwardValues=['2'=>2,'3'=>3,'5'=>5,'6'=>6,'8'=>8,'9'=>9,'10'=>10,'Q'=>12];

    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values(array_slice($players,0,4));
        while(count($players)<4)$players[]='bot:jackaroo_'.count($players);
        $deck=array_map(fn($card)=>$card->id(),DeckFactory::standard52(false));
        $hands=array_fill_keys($players,[]);$this->dealRound($hands,$deck,$players);
        return [
            'phase'=>'playing','game_type'=>'jackaroo','players'=>$players,'turn'=>$players[0],
            'dealer_index'=>3,'teams'=>$this->teams($players),'hands'=>$hands,'deck'=>$deck,'discard'=>[],
            'pieces'=>array_fill_keys($players,[-1,-1,-1,-1]),
            'round'=>1,'score'=>['teamA'=>0,'teamB'=>0],
            'messages'=>['بدأت جاكارو: A وK للإنزال، A خطوة أو 11، J للتبديل، 7 قابلة للتقسيم، و4 للخلف.'],
            'engine_quality'=>'jackaroo_partnership_complete_v142',
        ];
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['phase']??null)!=='playing'||($state['turn']??null)!==$playerId)return false;
        if($action==='pass')return empty($this->availableActions($state,$playerId))&&!empty($state['hands'][$playerId]);
        if($action!=='play_card')return false;
        $card=$this->canonical((string)($payload['card']??''),$state['hands'][$playerId]??[]);if(!$card)return false;
        foreach($this->availableActions($state,$playerId) as $candidate){
            if(($candidate['type']??null)!=='play_card'||($candidate['card']??null)!==$card)continue;
            if($this->payloadMatches($candidate,$payload))return true;
        }
        return false;
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if($action==='pass'&&empty($this->availableActions($state,$playerId))&&!empty($state['hands'][$playerId])){
            $discarded=array_shift($state['hands'][$playerId]);if($discarded)$state['discard'][]=$discarded;
            $state['messages'][]=$this->labelPlayer($playerId).' لا يملك حركة قانونية؛ أسقط ورقة.';
            return $this->completeTurn($state,$playerId);
        }
        if(!$this->validate($state,$playerId,$action,$payload)){
            $state['last_error_message']='اختر حركة جاكارو قانونية مطابقة لوظيفة الورقة.';return $state;
        }
        $card=$this->canonical((string)$payload['card'],$state['hands'][$playerId]);$rank=$this->rank($card);
        $owner=(string)($payload['owner']??$this->movableOwner($state,$playerId));
        if($rank==='J'){
            $piece=(int)$payload['piece'];$targetOwner=(string)$payload['target_owner'];$targetPiece=(int)$payload['target_piece'];
            [$state['pieces'][$owner][$piece],$state['pieces'][$targetOwner][$targetPiece]]=[$state['pieces'][$targetOwner][$targetPiece],$state['pieces'][$owner][$piece]];
            $state['messages'][]=$this->labelPlayer($playerId).' بدّل حجرين باستخدام J.';
        }elseif($rank==='7'&&isset($payload['piece2'])){
            $this->executeSteps($state,$owner,(int)$payload['piece'],(int)$payload['steps']);
            $this->executeSteps($state,$owner,(int)$payload['piece2'],(int)$payload['steps2']);
            $state['messages'][]=$this->labelPlayer($playerId).' قسّم السبعة بين حجرين.';
        }else{
            $piece=(int)$payload['piece'];$steps=(int)($payload['steps']??$this->defaultSteps($rank));
            if(($state['pieces'][$owner][$piece]??-1)<0){$state['pieces'][$owner][$piece]=0;$this->captureAt($state,$owner,$piece,0);$label='أنزل حجراً من البيت';}
            else{$this->executeSteps($state,$owner,$piece,$steps);$label='حرّك الحجر '.($piece+1).' بمقدار '.$steps;}
            $state['messages'][]=$this->labelPlayer($playerId).' '.$label.'.';
        }
        $this->removeCard($state['hands'][$playerId],$card);$state['discard'][]=$card;
        $team=$this->teamOf($state,$playerId);
        if($this->teamFinished($state,$team)){
            $state['phase']='finished';$state['winner_team']=$team;$state['winner']=$playerId;$state['score'][$team]=1;
            $state['messages'][]='فاز فريق '.$team.' بعد إدخال الأحجار الثمانية إلى الأمان.';return $state;
        }
        return $this->completeTurn($state,$playerId);
    }

    public function onTurnTimeout(array $state): array
    {
        $player=(string)($state['turn']??'');$actions=$this->availableActions($state,$player);
        if(!$actions){$state['messages'][]='⏱️ لا توجد حركة؛ مرّر الكمبيوتر الدور.';return $this->apply($state,$player,'pass',[]);}
        usort($actions,fn($a,$b)=>($this->actionPriority($b)<=>$this->actionPriority($a)));
        $state['messages'][]='⏱️ انتهى الوقت؛ نفّذ الكمبيوتر أفضل حركة قانونية.';
        $chosen=$actions[0];$payload=$chosen;unset($payload['type']);
        return $this->apply($state,$player,'play_card',$payload);
    }

    /** @return array<int,array<string,mixed>> */
    public function availableActions(array $state,string $playerId): array
    {
        if(($state['phase']??null)!=='playing'||($state['turn']??null)!==$playerId)return [];
        $out=[];$owner=$this->movableOwner($state,$playerId);
        foreach($state['hands'][$playerId]??[] as $card){
            $rank=$this->rank((string)$card);
            if(in_array($rank,['A','K'],true)){
                foreach($state['pieces'][$owner]??[] as $piece=>$position){
                    if((int)$position<0&&$this->canStart($state,$owner))$out[]=['type'=>'play_card','card'=>$card,'owner'=>$owner,'piece'=>(int)$piece,'steps'=>0,'label'=>'إنزال الحجر '.($piece+1)];
                }
                if($rank==='K')continue;
            }
            if($rank==='J'){
                foreach($this->swapActions($state,$playerId,$owner,(string)$card) as $action)$out[]=$action;
                continue;
            }
            if($rank==='7'){
                foreach($this->sevenActions($state,$owner,(string)$card) as $action)$out[]=$action;
                continue;
            }
            $steps=$rank==='A'?11:($rank==='4'?-4:($this->forwardValues[$rank]??0));
            if($rank==='A'){
                foreach($this->normalActions($state,$owner,(string)$card,1) as $action)$out[]=$action;
                foreach($this->normalActions($state,$owner,(string)$card,11) as $action)$out[]=$action;
            }elseif($steps!==0){foreach($this->normalActions($state,$owner,(string)$card,$steps) as $action)$out[]=$action;}
        }
        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function normalActions(array $state,string $owner,string $card,int $steps): array
    {
        $out=[];foreach($state['pieces'][$owner]??[] as $piece=>$position){
            if((int)$position<0)continue;
            if($this->canMove($state,$owner,(int)$piece,$steps))$out[]=['type'=>'play_card','card'=>$card,'owner'=>$owner,'piece'=>(int)$piece,'steps'=>$steps,'label'=>'الحجر '.($piece+1).' '.($steps<0?'للخلف ':'للأمام ').abs($steps)];
        }return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function sevenActions(array $state,string $owner,string $card): array
    {
        $out=$this->normalActions($state,$owner,$card,7);
        $pieces=array_keys($state['pieces'][$owner]??[]);
        foreach($pieces as $p1)foreach($pieces as $p2){
            if($p1===$p2)continue;
            for($s1=1;$s1<=6;$s1++){$s2=7-$s1;
                if(!$this->canMove($state,$owner,(int)$p1,$s1))continue;
                $copy=$state;$this->executeSteps($copy,$owner,(int)$p1,$s1);
                if(!$this->canMove($copy,$owner,(int)$p2,$s2))continue;
                $out[]=['type'=>'play_card','card'=>$card,'owner'=>$owner,'piece'=>(int)$p1,'steps'=>$s1,'piece2'=>(int)$p2,'steps2'=>$s2,'label'=>'تقسيم '.$s1.' + '.$s2.' على الحجرين '.($p1+1).' و'.($p2+1)];
            }
        }
        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function swapActions(array $state,string $player,string $owner,string $card): array
    {
        $out=[];foreach($state['pieces'][$owner]??[] as $piece=>$position){
            if((int)$position<=0||(int)$position>=56)continue;
            foreach($state['pieces']??[] as $targetOwner=>$targetPieces){
                if($targetOwner===$owner||$this->teamOf($state,(string)$targetOwner)===$this->teamOf($state,$player))continue;
                foreach($targetPieces as $targetPiece=>$targetPosition){
                    if((int)$targetPosition<=0||(int)$targetPosition>=56)continue;
                    $out[]=['type'=>'play_card','card'=>$card,'owner'=>$owner,'piece'=>(int)$piece,'target_owner'=>(string)$targetOwner,'target_piece'=>(int)$targetPiece,'label'=>'تبديل الحجر '.($piece+1).' مع '.$this->labelPlayer((string)$targetOwner)];
                }
            }
        }return $out;
    }

    private function canStart(array $state,string $owner): bool
    {
        foreach($state['pieces'][$owner]??[] as $position)if((int)$position===0)return false;
        return true;
    }

    private function canMove(array $state,string $owner,int $piece,int $steps): bool
    {
        $position=(int)($state['pieces'][$owner][$piece]??-1);if($position<0)return false;
        $next=$position+$steps;if($next<0||$next>59)return false;
        $increment=$steps<0?-1:1;
        for($step=$position+$increment;$increment>0?$step<=$next:$step>=$next;$step+=$increment){
            if($this->ownPieceAt($state,$owner,$step,$piece))return false;
            if($step<56&&$this->protectedOpponentBaseAt($state,$owner,$step))return false;
        }
        return true;
    }

    private function executeSteps(array &$state,string $owner,int $piece,int $steps): void
    {
        $current=(int)$state['pieces'][$owner][$piece];$next=$current+$steps;
        $state['pieces'][$owner][$piece]=$next;$this->captureAt($state,$owner,$piece,$next);
    }

    private function captureAt(array &$state,string $owner,int $piece,int $position): void
    {
        if($position<0||$position>=56)return;$global=$this->globalPosition($state,$owner,$position);
        foreach($state['pieces'] as $opponent=>$pieces){
            if($opponent===$owner)continue;
            foreach($pieces as $i=>$p){
                if((int)$p<=0||(int)$p>=56)continue; // bases and safety are protected
                if($this->globalPosition($state,(string)$opponent,(int)$p)===$global){$state['pieces'][$opponent][$i]=-1;$state['messages'][]=$this->labelPlayer($owner).' أعاد حجر '.$this->labelPlayer((string)$opponent).' إلى البيت.';}
            }
        }
    }

    private function completeTurn(array $state,string $player): array
    {
        if($this->allHandsEmpty($state['hands'])){
            if(count($state['deck'])<16){$state['deck']=DeckFactory::secureShuffle(array_merge($state['deck'],$state['discard']));$state['discard']=[];}
            $this->dealRound($state['hands'],$state['deck'],$state['players']);$state['round']=(int)$state['round']+1;
            $state['dealer_index']=((int)($state['dealer_index']??0)+1)%4;
        }
        $state['turn']=$this->playerKeyNext($state['players'],$player);unset($state['last_error_message']);return $state;
    }

    private function movableOwner(array $state,string $player): string
    {
        if($this->playerFinished($state,$player)){
            $team=$this->teamOf($state,$player);
            foreach($state['teams'][$team]??[] as $member)if($member!==$player&&!$this->playerFinished($state,(string)$member))return (string)$member;
        }
        return $player;
    }
    private function playerFinished(array $state,string $player): bool{foreach($state['pieces'][$player]??[] as $position)if((int)$position<56)return false;return true;}
    private function teamFinished(array $state,string $team): bool{foreach($state['teams'][$team]??[] as $p)if(!$this->playerFinished($state,(string)$p))return false;return true;}
    private function protectedOpponentBaseAt(array $state,string $owner,int $progress): bool
    {
        $global=$this->globalPosition($state,$owner,$progress);
        foreach($state['pieces'] as $other=>$pieces){if($other===$owner)continue;foreach($pieces as $p)if((int)$p===0&&$this->globalPosition($state,(string)$other,0)===$global)return true;}return false;
    }
    private function ownPieceAt(array $state,string $owner,int $position,int $except): bool{foreach($state['pieces'][$owner]??[] as $i=>$p)if($i!==$except&&(int)$p===$position)return true;return false;}
    private function globalPosition(array $state,string $player,int $progress): int{$seat=array_search($player,$state['players'],true);return ((($seat===false?0:$seat)*14)+$progress)%56;}
    private function dealRound(array &$hands,array &$deck,array $players): void{foreach($players as $p)$hands[$p]=[];for($i=0;$i<4;$i++)foreach($players as $p)if($deck)$hands[$p][]=array_shift($deck);}
    private function defaultSteps(string $rank): int{return $rank==='4'?-4:($rank==='A'?1:($this->forwardValues[$rank]??0));}
    private function canonical(string $card,array $hand): ?string{if(in_array($card,$hand,true))return $card;$n=strtolower(str_replace(['-',' '],'_',$card));foreach($hand as $h)if(strtolower((string)$h)===$n)return (string)$h;return null;}
    private function payloadMatches(array $candidate,array $payload): bool{foreach(['owner','piece','steps','piece2','steps2','target_owner','target_piece'] as $key)if(array_key_exists($key,$candidate)&&(string)$candidate[$key]!==((string)($payload[$key]??'')))return false;return true;}
    private function actionPriority(array $action): int{$score=0;if(isset($action['target_owner']))$score+=100;if(isset($action['piece2']))$score+=40;$score+=abs((int)($action['steps']??0));return $score;}
}
