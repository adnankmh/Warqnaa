<?php
namespace App\Services\GameEngine;
class Tarneeb41Rules extends TarneebRules
{
    public function initialState(array $players,array $options=[]): array { $s=parent::initialState($players,['target'=>41]); $s['game_type']='tarneeb_41'; $s['target']=41; $s['messages'][]='طرنيب 41: نفس آلية الطلب والطرنيب واللمّات، والهدف 41.'; return $s; }
}
