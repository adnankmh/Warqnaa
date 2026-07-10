<?php
return [
 'currencies'=>[
  'coins'=>['ar'=>'عملات','soft'=>true,'earnable'=>true],
  'tokens'=>['ar'=>'توكنز','soft'=>false,'earnable'=>true],
  'gems'=>['ar'=>'جواهر','hard'=>true,'paid'=>true],
 ],
 'throwables'=>[
  'tomato'=>['icon'=>'🍅','ar'=>'بندورة','cost'=>10,'tier'=>'free'],
  'rose'=>['icon'=>'🌹','ar'=>'وردة','cost'=>50,'tier'=>'vip'],
  'coffee'=>['icon'=>'☕','ar'=>'قهوة','cost'=>25,'tier'=>'free'],
  'shoe'=>['icon'=>'👟','ar'=>'شبشب','cost'=>15,'tier'=>'free'],
  'smoke'=>['icon'=>'💨','ar'=>'دخان','cost'=>120,'tier'=>'premium'],
  'royal_crown'=>['icon'=>'👑','ar'=>'تاج ملكي','cost'=>500,'tier'=>'legendary'],
 ],
 'daily_rewards'=>[1=>50,2=>50,3=>100,4=>50,5=>100,6=>100,7=>200],
 'ads'=>['rewarded_coins'=>50,'daily_limit'=>5],
];
