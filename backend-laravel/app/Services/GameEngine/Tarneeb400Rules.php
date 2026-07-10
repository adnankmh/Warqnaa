<?php
namespace App\Services\GameEngine;
class Tarneeb400Rules extends TarneebRules
{
    public function initialState(array $players,array $options=[]): array { $s=parent::initialState($players,['target'=>400]); $s['game_type']='tarneeb_400'; $s['target']=400; $s['messages'][]='طرنيب 400: الطلب واللعب مثل الطرنيب مع هدف 400 نقطة للعبة.'; return $s; }
}
