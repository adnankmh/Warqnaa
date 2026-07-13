<?php
namespace App\Services\GameEngine;

class BalootRules extends AbstractCardRules
{
    private array $sunRank=['A'=>8,'10'=>7,'K'=>6,'Q'=>5,'J'=>4,'9'=>3,'8'=>2,'7'=>1];
    private array $hokmRankTrump=['J'=>9,'9'=>8,'A'=>7,'10'=>6,'K'=>5,'Q'=>4,'8'=>2,'7'=>1];
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values($players); $deck=[]; foreach(['clubs','diamonds','spades','hearts'] as $s) foreach(['A','10','K','Q','J','9','8','7'] as $r) $deck[]=new Card($s,$r); shuffle($deck); [$hands,$deck]=$this->deal($players,$deck,8);
        return ['phase'=>'baloot_bid','game_type'=>'baloot','players'=>$players,'teams'=>$this->teams($players),'turn'=>$players[0]??null,'hands'=>$hands,'bid'=>null,'passes'=>0,'trump'=>null,'trick'=>[],'last_trick'=>[],'round_tricks'=>['teamA'=>0,'teamB'=>0],'round_points'=>['teamA'=>0,'teamB'=>0],'score'=>['teamA'=>0,'teamB'=>0],'messages'=>['بلوت: اختر صن أو حكم. عند الحكم اختر النوع. يجب اتباع النوع، ويتم احتساب نقاط الأوراق للفريق.']];
    }
    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['turn']??null)!==$playerId) return false;
        if(($state['phase']??'')==='baloot_bid') return in_array($action,['pass','choose_sun','choose_hokm'],true);
        if(($state['phase']??'')==='choose_trump') return $action==='choose_trump'&&in_array($payload['suit']??'', ['clubs','diamonds','spades','hearts'],true);
        if(($state['phase']??'')==='playing'){
            $card=(string)($payload['card']??''); if($action!=='play_card'||!in_array($card,$state['hands'][$playerId]??[],true))return false;
            if(!empty($state['trick'])){ $lead=$this->suit((string)reset($state['trick'])); if($this->hasSuit($state['hands'][$playerId],$lead)&&$this->suit($card)!==$lead)return false; }
            return true;
        }
        return false;
    }
    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){ $state['last_error']='invalid_baloot_action'; return $state; }
        $players=$state['players'];
        if(($state['phase']??'')==='baloot_bid'){
            if($action==='pass'){ $state['passes']=(int)($state['passes']??0)+1; $state['messages'][]=$this->labelPlayer($playerId).' Pass'; if($state['passes']>=count($players)){ $state['bid']=['type'=>'sun','player'=>$players[0],'team'=>$this->teamOf($state,$players[0])]; $state['phase']='playing'; $state['turn']=$players[0]; $state['messages'][]='كل اللاعبين Pass، تم بدء صن تلقائيًا للتجربة.'; return $state; } $state['turn']=$this->playerKeyNext($players,$playerId); }
            elseif($action==='choose_sun'){ $state['bid']=['type'=>'sun','player'=>$playerId,'team'=>$this->teamOf($state,$playerId)]; $state['phase']='playing'; $state['turn']=$playerId; $state['messages'][]='تم شراء صن بواسطة '.$this->labelPlayer($playerId); }
            else { $state['bid']=['type'=>'hokm','player'=>$playerId,'team'=>$this->teamOf($state,$playerId)]; $state['phase']='choose_trump'; $state['turn']=$playerId; $state['messages'][]='تم شراء حكم. اختر نوع الحكم.'; }
            return $state;
        }
        if(($state['phase']??'')==='choose_trump'){ $state['trump']=$payload['suit']; $state['phase']='playing'; $state['turn']=$playerId; $state['messages'][]='الحكم: '.$this->suitName($payload['suit']); return $state; }
        $card=(string)$payload['card']; $this->removeCard($state['hands'][$playerId],$card); $state['trick'][$playerId]=$card;
        if(count($state['trick'])>=count($players)){ $winner=$this->balootTrickWinner($state); $team=$this->teamOf($state,$winner); $pts=$this->trickPoints($state); $state['round_tricks'][$team]++; $state['round_points'][$team]+=$pts; $state['last_trick']=$state['trick']; $state['trick']=[]; $state['turn']=$winner; $state['messages'][]='الفائز باللفة '.$this->labelPlayer($winner).'، نقاط اللفة '.$pts.' للفريق.'; }
        else $state['turn']=$this->playerKeyNext($players,$playerId);
        if($this->allHandsEmpty($state['hands'])) return $this->scoreRound($state);
        return $state;
    }
    private function balootTrickWinner(array $state): string { $trick=$state['trick']; $lead=$this->suit((string)reset($trick)); $type=$state['bid']['type']??'sun'; $trump=$state['trump']??null; $bestP=array_key_first($trick); $best=-1; foreach($trick as $p=>$c){ $s=$this->suit($c); $r=$this->rank($c); if($type==='hokm' && $s===$trump) $score=100+($this->hokmRankTrump[$r]??0); else $score=($s===$lead?50:0)+($this->sunRank[$r]??0); if($score>$best){$best=$score;$bestP=$p;} } return $bestP; }
    private function trickPoints(array $state): int { $mode=($state['bid']['type']??'sun')==='hokm'?'baloot_hokm':'baloot_sun'; $sum=0; foreach($state['trick'] as $c)$sum+=Scoring::cardPoints($c,$mode,$state['trump']??null); return $sum; }
    private function scoreRound(array $state): array { $buyer=$state['bid']['team']??'teamA'; $other=$buyer==='teamA'?'teamB':'teamA'; $buyerPts=(int)($state['round_points'][$buyer]??0); $otherPts=(int)($state['round_points'][$other]??0); if($buyerPts>$otherPts){$state['score'][$buyer]+=$buyerPts;$state['score'][$other]+=$otherPts;$state['messages'][]='نجح فريق الشراء وأخذ '.$buyerPts.' نقطة.';} else {$state['score'][$other]+=$buyerPts+$otherPts;$state['messages'][]='فشل فريق الشراء. نقاط الجولة ذهبت للفريق الخصم.';} $state['phase']='finished'; $state['winner_team']=($state['score']['teamA']??0)>=($state['score']['teamB']??0)?'teamA':'teamB'; return $state; }
}
