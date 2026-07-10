<?php
namespace App\Services\GameEngine;

class PinochleRules extends HandRules
{

    protected function points($cards): int|float
    {
        $sum=0.0;
        foreach($cards as $c){
            $r=$this->rank($c);
            if($r==='JOKER') $sum+=4;
            elseif($r==='2') $sum+=2;
            elseif(in_array($r,['3','4','5','6'],true)) $sum+=0.5;
            else $sum+=1;
        }
        return $sum;
    }

    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values($players); $deck=DeckFactory::pinochle();
        // بناكل كما في الألعاب العربية: 104 ورقة + جوكرين، 2 والجوكر أوراق مساعدة، و18 ورقة غالبًا لكل لاعب.
        $cardsEach=count($players)<=2?18:min(18,intdiv(max(1,count($deck)-1),max(1,count($players))));
        [$hands,$deck]=$this->deal($players,$deck,$cardsEach); $discard=[]; if($deck)$discard[]=array_shift($deck)->id();
        $prevScores=$options['previous_scores'] ?? array_fill_keys($players,0); $round=(int)($options['round'] ?? 1);
        return ['phase'=>'playing','game_type'=>'banakil','players'=>$players,'teams'=>$this->teams($players),'turn'=>$players[0]??null,'hands'=>$hands,'deck'=>array_map(fn($c)=>$c->id(),$deck),'discard'=>$discard,
            'melds'=>[],'first_meld_done'=>[],'drew_this_turn'=>[],'scores'=>$prevScores,'round'=>$round,'target'=>(int)($options['target']??222),'twos_wild'=>true,'jokers_wild'=>true,
            'messages'=>['بناكل: 18 ورقة، 2 والجوكر أوراق مساعدة، اسحب ثم نزّل مجموعات/سلاسل أو أضف على مجموعات الشريك، ثم ارمِ. الهدف الافتراضي 222.']];
    }
}
