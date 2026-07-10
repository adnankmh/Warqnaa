<?php
namespace App\Services\GameEngine;

class ConcanRules extends HandRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $s=parent::initialState($players,['target'=>$options['target']??100]);
        $s['game_type']='konkan'; $s['target']=(int)($options['target']??100);
        $s['messages']=['كونكان: اسحب من الدك أو الرمي، كوّن مجموعات أو سلاسل من 3 أوراق فأكثر، ثم ارمِ ورقة. تنتهي اليد عند التخلص من كل الأوراق، وتُخصم أوراق الخصوم.'];
        return $s;
    }
}
