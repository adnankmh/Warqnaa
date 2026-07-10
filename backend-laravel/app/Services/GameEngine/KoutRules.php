<?php
namespace App\Services\GameEngine;

class KoutRules extends TarneebRules
{
    public function initialState(array $players,array $options=[]): array
    {
        $s=parent::initialState($players,['target'=>101]);
        $s['game_type']=count($players)>=6?'kout6':'kout4'; $s['target']=101; $s['messages'][]='كوت: طلب ولفات بنظام الفرق. الطلب من 7 إلى 13، الالتزام بالنوع، والفوز عند بلوغ الهدف.';
        return $s;
    }
}
