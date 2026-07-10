<?php
namespace App\Services\GameEngine;

class HokmRules extends TarneebRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $s=parent::initialState($players,['target'=>7]);
        $s['game_type']='hokm'; $s['phase']='choose_trump'; $s['bid']=['player'=>$s['turn'],'value'=>7,'team'=>$s['teams']?array_key_first($s['teams']):'teamA']; $s['round_tricks']=['teamA'=>0,'teamB'=>0]; $s['score']=['teamA'=>0,'teamB'=>0]; $s['messages']=['حكم: يختار أول لاعب الحكم ثم يبدأ اللعب. الفريق الذي يصل 7 لمّات يفوز بالجولة.'];
        return $s;
    }
}
