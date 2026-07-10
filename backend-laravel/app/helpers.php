<?php
if(!function_exists('safe_country_code')){
 function safe_country_code($code){$code=strtoupper((string)($code ?: 'PS')); if($code==='IL') return 'PS'; return preg_match('/^[A-Z]{2}$/',$code)?$code:'PS';}
}
if(!function_exists('flag_url')){
 function flag_url($code){$code=safe_country_code($code); return file_exists(public_path('assets/flags/'.$code.'.svg')) ? asset('assets/flags/'.$code.'.svg') : asset('assets/flags/PS.svg');}
}
if(!function_exists('flag_img')){
 function flag_img($code,$class='flag-img'){ $code=safe_country_code($code); $name=e(country_name($code)); return '<img class="'.e($class).'" src="'.flag_url($code).'" alt="'.$name.'" title="'.$name.'">';}
}
function flag_emoji($code){return flag_img($code);}
if(!function_exists('country_name')){
 function country_name($code,$locale='ar'){
  $c=safe_country_code($code);
  $map=config('countries',[]);
  $name=$map[$c]??$c;
  if(is_array($name)){
   $locale=in_array($locale,['ar','en'],true)?$locale:'ar';
   return (string)($name[$locale] ?? $name['ar'] ?? $name['en'] ?? $c);
  }
  return (string)($name ?: $c);
 }
}
if(!function_exists('country_label')){
 function country_label($code){
  $c=safe_country_code($code); $row=config('countries.'.$c);
  if(is_array($row)) return trim(($row['flag'] ?? '').' '.($row['ar'] ?? $c).' — '.($row['en'] ?? $c));
  return country_name($c);
 }
}

if(!function_exists('color_label')){
 function color_label($hex){
  $h=strtolower(trim((string)$hex));
  $map=['#facc15'=>'ذهبي','#ffffff'=>'أبيض','#000000'=>'أسود','#ef4444'=>'أحمر','#dc2626'=>'أحمر ملكي','#22c55e'=>'أخضر','#10b981'=>'زمردي','#3b82f6'=>'أزرق','#38bdf8'=>'سماوي','#8b5cf6'=>'بنفسجي','#a855f7'=>'بنفسجي فاخر','#ec4899'=>'وردي','#f97316'=>'برتقالي','#14b8a6'=>'فيروزي','#eab308'=>'ذهبي داكن','#06b6d4'=>'كريستالي'];
  return $map[$h] ?? strtoupper($h ?: 'افتراضي');
 }
}
if(!function_exists('cosmetic_label')){
 function cosmetic_label($value){
  $v=(string)($value ?: 'افتراضي');
  $map=['table-ultra-eagle'=>'طاولة النسر الواقعي','table-ultra-tiger'=>'طاولة النمر الواقعي','table-glass-crystal'=>'طاولة زجاج كريستال','table-gold-palace'=>'قصر ذهبي','table-signature-gold'=>'اسم ذهبي','card-back-gold'=>'ظهر ذهبي','glow-gold'=>'Glow ذهبي','glow-ocean'=>'Glow محيطي'];
  return $map[$v] ?? str_replace(['table-','card-back-','glow-','_','-'],['طاولة ','ظهر ','Glow ',' ',' '],$v);
 }
}

if(!function_exists('game_icon')){
 function game_icon($key){
  $icons=[
   'tarneeb'=>'🂡','tarneeb_400'=>'💯','tarneeb_41'=>'4️⃣1️⃣','pinochle'=>'🃏','banakil'=>'🃏','hand'=>'✋','trix'=>'👑','trix_partner'=>'🤝','baloot'=>'☀️','leekha'=>'♥️','estimation'=>'🎯','hearts'=>'💔','hokm'=>'⚖️','kout4'=>'♦️','kout6'=>'✳️','basra'=>'🧹','konkan'=>'🧩','domino'=>'▦','backgammon'=>'🎲','quick'=>'⚡'
  ];
  return $icons[$key] ?? '🂠';
 }
}


if(!function_exists('bot_catalog')){
 function bot_catalog(){
  return [
   ['name'=>'معتصم','avatar'=>asset('assets/bots/royal/01-mutasim.svg')],
   ['name'=>'يمان','avatar'=>asset('assets/bots/royal/02-yaman.svg')],
   ['name'=>'عدنان','avatar'=>asset('assets/bots/royal/03-adnan.svg')],
   ['name'=>'عاصم','avatar'=>asset('assets/bots/royal/04-asim.svg')],
   ['name'=>'كنان','avatar'=>asset('assets/bots/royal/05-kinan.svg')],
   ['name'=>'جميل','avatar'=>asset('assets/bots/royal/06-jamil.svg')],
   ['name'=>'همام','avatar'=>asset('assets/bots/royal/07-hammam.svg')],
   ['name'=>'معاذ','avatar'=>asset('assets/bots/royal/08-muath.svg')],
   ['name'=>'مصطفى','avatar'=>asset('assets/bots/royal/09-mustafa.svg')],
   ['name'=>'مهند','avatar'=>asset('assets/bots/royal/10-muhannad.svg')],
  ];
 }
}
if(!function_exists('bot_clean_name')){
 function bot_clean_name($value){
  $v=trim((string)$value);
  return trim(preg_replace('/\s*BOT\s*$/iu','',$v));
 }
}
if(!function_exists('bot_avatar_url')){
 function bot_avatar_url($name=null,$fallbackIndex=0){
  $list=bot_catalog();
  $clean=bot_clean_name($name);
  foreach($list as $i=>$bot){ if($clean!=='' && $bot['name']===$clean) return $bot['avatar']; }
  $i=(int)$fallbackIndex; if($i<0) $i=0; $bot=$list[$i % max(count($list),1)] ?? null;
  return $bot['avatar'] ?? asset('assets/bots/bot01.svg');
 }
}
