<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use Illuminate\Support\Facades\{Hash,DB}; use App\Models\{User,Profile,Wallet,Club,ClubMember,Tournament}; use App\Services\Games\GameCatalog; use App\Services\WarqnaPro\StoreCatalogService;
class DatabaseSeeder extends Seeder { public function run(): void {
 // v136: country_name() now returns a scalar string, so Seeder can use it directly without helper variables.
 $admin=User::updateOrCreate(['email'=>env('ADMIN_EMAIL','adnanasd63@gmail.com')],['username'=>env('ADMIN_USERNAME','Adnan'),'password'=>Hash::make(env('ADMIN_PASSWORD','Adnan123')),'is_admin'=>true]);
 Profile::updateOrCreate(['user_id'=>$admin->id],['display_name'=>'Adnan','avatar'=>'🦁','country_code'=>'PS','country_name'=>'Palestine','level'=>99,'xp'=>9000000,'games_played'=>20000,'wins'=>15000,'name_color'=>'#facc15','chat_color'=>'#facc15','pasha_days'=>3650,'badge'=>'king']);
 Wallet::updateOrCreate(['user_id'=>$admin->id],['tokens'=>1000000000000000000,'gems'=>100000]);
 // v107 demo users: 3 ready test users
 $demoUsers = [
   ['Kareem','kareem@warqna.local','Kareem123','#38bdf8','JO',42,250000,'🦅'],
   ['Rami','rami@warqna.local','Rami12345','#22c55e','PS',35,180000,'🐺'],
   ['Lina','lina@warqna.local','Lina12345','#ec4899','EG',28,120000,'🌹'],
   ['Samar','samar@warqna.local','Samar12345','#a78bfa','PS',24,95000,'🦋'],
   ['Layla','layla@warqna.local','Layla12345','#f59e0b','JO',31,110000,'🌙'],
   ['Jameel','jameel@warqna.local','Jameel12345','#fb923c','PS',22,88000,'🐯'],
   ['Nour','nour@warqna.local','Nour12345','#fde047','EG',19,76000,'⭐'],
   ['Omar','omar@warqna.local','Omar12345','#60a5fa','PS',27,68000,'🛡️'],
   ['Sara','sara@warqna.local','Sara12345','#f472b6','LB',29,72000,'👑'],
   ['Basel','basel@warqna.local','Basel12345','#ef4444','SY',33,84000,'🔥'],
   ['Hala','hala@warqna.local','Hala12345','#22d3ee','PS',25,61000,'💎'],
   ['Yazan','yazan@warqna.local','Yazan12345','#facc15','JO',30,79000,'⚡'],
 ];
 $seededDemoUsers = [];
 foreach ($demoUsers as [$username,$email,$password,$color,$country,$level,$tokens,$avatar]) {
   $u=User::updateOrCreate(['email'=>$email],['username'=>$username,'password'=>Hash::make($password),'is_admin'=>false,'is_banned'=>false]);
   Profile::updateOrCreate(['user_id'=>$u->id],['display_name'=>$username,'avatar'=>$avatar,'country_code'=>$country,'country_name'=>country_name($country),'level'=>$level,'xp'=>$level*1200,'games_played'=>$level*15,'wins'=>$level*7,'name_color'=>$color,'chat_color'=>$color,'pasha_days'=>0,'badge'=>'pro']);
   Wallet::updateOrCreate(['user_id'=>$u->id],['tokens'=>$tokens,'gems'=>0]);
   $seededDemoUsers[strtolower($username)] = $u;
 }

 // V0.3 demo club: mixed levels and delegated permissions for testing groups.
 if (\Illuminate\Support\Facades\Schema::hasTable('clubs') && \Illuminate\Support\Facades\Schema::hasTable('club_members')) {
   $club = Club::updateOrCreate(
     ['name'=>'نخبة ورقنا'],
     ['owner_id'=>$admin->id,'logo'=>'👑','description'=>'نادي تجريبي لاختبار الصور والصلاحيات والمنافسات والسجل.','level'=>4,'weekly_points'=>2840,'total_points'=>42500,'treasury'=>250000,'capacity'=>50,'league_tier'=>'platinum','visibility'=>'public']
   );
   ClubMember::updateOrCreate(['club_id'=>$club->id,'user_id'=>$admin->id],['role'=>'owner','permissions'=>['all'=>true],'weekly_points'=>900]);
   foreach ([
     'kareem'=>['moderator',['manage_club'=>true,'accept_members'=>true,'kick_members'=>true,'create_tournaments'=>true,'manage_chat'=>true,'create_announcements'=>true],620],
     'rami'=>['moderator',['accept_members'=>true,'kick_members'=>false,'create_tournaments'=>true,'manage_chat'=>true,'create_announcements'=>false],480],
     'lina'=>['moderator',['accept_members'=>false,'kick_members'=>false,'create_tournaments'=>false,'manage_chat'=>true,'create_announcements'=>true],390],
     'samar'=>['member',[],220], 'layla'=>['member',[],180], 'jameel'=>['member',[],150],
   ] as $key=>[$role,$permissions,$points]) {
     if (!isset($seededDemoUsers[$key])) continue;
     ClubMember::updateOrCreate(['club_id'=>$club->id,'user_id'=>$seededDemoUsers[$key]->id],['role'=>$role,'permissions'=>$permissions,'weekly_points'=>$points]);
   }
 }

 
 // v131 only 15 working games: deactivate games outside curated stable catalog.
 if (\Illuminate\Support\Facades\Schema::hasTable('games')) { DB::table('games')->whereNotIn('key', ['tarneeb', 'tarneeb_41', 'tarneeb_61', 'syrian_tarneeb', 'tarneeb_400', 'hand', 'hand_partner', 'saudi_hand', 'pinochle', 'banakil', 'solitaire_multiplayer', 'trix', 'trix_partner', 'trix_complex', 'baloot', 'domino', 'basra', 'jackaroo', 'backgammon', 'chess'])->update(['active'=>false,'updated_at'=>now()]); DB::table('games')->whereIn('key', ['tarneeb', 'tarneeb_41', 'tarneeb_61', 'syrian_tarneeb', 'tarneeb_400', 'hand', 'hand_partner', 'saudi_hand', 'pinochle', 'banakil', 'solitaire_multiplayer', 'trix', 'trix_partner', 'trix_complex', 'baloot', 'domino', 'basra', 'jackaroo', 'backgammon', 'chess'])->update(['active'=>true,'updated_at'=>now()]); }

 // v128 premium store catalog sync: 40 tables + 40 card backs + Pasha 7 days = 10000 tokens.
 try { app(StoreCatalogService::class)->sync(); } catch (\Throwable $e) {}
 
 if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
  foreach ([['default_theme','royal','string','appearance','الثيم الافتراضي'],['force_global_theme','0','bool','appearance','فرض الثيم'],['store_enabled','1','bool','modules','تشغيل المتجر'],['clubs_enabled','1','bool','modules','تشغيل النوادي'],['tournaments_enabled','1','bool','modules','تشغيل المسابقات'],['chat_enabled','1','bool','modules','تشغيل الدردشة'],['support_enabled','1','bool','modules','تشغيل الدعم']] as [$key,$value,$type,$group,$label])
   DB::table('site_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'type'=>$type,'group'=>$group,'label'=>$label,'created_at'=>now(),'updated_at'=>now()]);
 
 // v86: final store tuning from uploaded store manifest: six boosters only, 24h active, 10-day validity, richer emoji tiers.
 DB::table('store_items')->where('category','xp_booster')->update(['active'=>false]);
 $v86Boosters=[
  ['xp_x1_25','مسرّع نقاط x1.25','XP Booster x1.25',1.25,12000,'#22c55e'],
  ['xp_x1_5','مسرّع نقاط x1.5','XP Booster x1.5',1.5,25000,'#38bdf8'],
  ['xp_x2','مسرّع نقاط x2','XP Booster x2',2.0,52000,'#a855f7'],
  ['xp_x3','مسرّع نقاط x3','XP Booster x3',3.0,110000,'#f97316'],
  ['xp_x4','مسرّع نقاط x4','XP Booster x4',4.0,190000,'#facc15'],
  ['xp_x5','مسرّع نقاط x5','XP Booster x5',5.0,320000,'#ef4444'],
 ];
 foreach($v86Boosters as [$key,$ar,$en,$mult,$price,$color]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'xp_booster','price'=>$price,'duration_days'=>1,'payload'=>json_encode(['multiplier'=>$mult,'color'=>$color,'valid_days'=>10,'label'=>'x'.$mult,'tier'=>'pro','preview_icon'=>'🚀'],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v86Emoji=[
  ['emoji_free_basic','إيموجي مجانية أساسية','Free Basic Emojis','😀😄😂👍👏👋',0,'free'],['emoji_beginner_fun','إيموجي مبتدئ مرحة','Beginner Fun Emojis','😊😉😎🤩🥳',1000,'beginner'],['emoji_medium_react','إيموجي متوسط تفاعل','Medium Reaction Emojis','😡😢😭😱🤔☕',5000,'medium'],['emoji_pro_power','إيموجي محترف قوية','Pro Power Emojis','🔥⚡💎🏆👑🛡️',10000,'pro'],['emoji_legend_big','إيموجي أسطورية كبيرة','Legendary Big Emojis','🦁🐉🦅🌌💥🎆',15000,'legendary'],['emoji_animated_vip','إيموجي متحركة VIP','Animated VIP Emojis','😂🔥👑💎⚡🏆🎉',15000,'animated'],
 ];
 foreach($v86Emoji as [$key,$ar,$en,$icons,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['emojis'=>$icons,'emoji_tier'=>$tier,'animated'=>in_array($tier,['animated','legendary','pro']),'large'=>in_array($tier,['legendary','animated'])],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);

}

 
 // v116 economy seed: seasons, offers and rare collectibles
 if (\Illuminate\Support\Facades\Schema::hasTable('economy_seasons')) {
  DB::table('economy_seasons')->updateOrInsert(['key'=>'season_royal_launch'],[
   'name'=>json_encode(['ar'=>'موسم الانطلاق الملكي','en'=>'Royal Launch Season'],JSON_UNESCAPED_UNICODE),
   'starts_at'=>now()->startOfMonth(),'ends_at'=>now()->addMonth(),'active'=>true,
   'rewards'=>json_encode(['top_1'=>'إطار أسطوري + 200 توكن','top_10'=>'شارة ملكية','daily'=>'مكافآت دخول'],JSON_UNESCAPED_UNICODE),
   'created_at'=>now(),'updated_at'=>now()
  ]);
 }
 if (\Illuminate\Support\Facades\Schema::hasTable('store_offers')) {
  DB::table('store_offers')->updateOrInsert(['key'=>'launch_boosters_25'],[
   'title'=>json_encode(['ar'=>'عرض المسرعات الملكي','en'=>'Royal Boosters Offer'],JSON_UNESCAPED_UNICODE),
   'description'=>json_encode(['ar'=>'خصم خاص على مسرعات XP خلال الموسم.'],JSON_UNESCAPED_UNICODE),
   'discount_percent'=>25,'starts_at'=>now(),'ends_at'=>now()->addDays(14),'active'=>true,
   'item_keys'=>json_encode(['xp_x2','xp_x3','xp_x4','xp_x5'],JSON_UNESCAPED_UNICODE),
   'created_at'=>now(),'updated_at'=>now()
  ]);
 }
 if (\Illuminate\Support\Facades\Schema::hasTable('rare_collectibles')) {
  DB::table('rare_collectibles')->updateOrInsert(['key'=>'rare_royal_shuttle'],[
   'name'=>json_encode(['ar'=>'مكوك الخبرة الملكي','en'=>'Royal XP Shuttle'],JSON_UNESCAPED_UNICODE),
   'rarity'=>'legendary','supply'=>500,'claimed'=>0,'active'=>true,
   'payload'=>json_encode(['icon'=>'🚀','glow'=>'gold','category'=>'xp_booster'],JSON_UNESCAPED_UNICODE),
   'created_at'=>now(),'updated_at'=>now()
  ]);
 }

 foreach(GameCatalog::all() as $key=>$g) DB::table('games')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$g['ar'],'en'=>$g['en']],JSON_UNESCAPED_UNICODE),'min_players'=>$g['min'],'max_players'=>$g['max'],'partnership'=>$g['partners'],'rules'=>json_encode(['engine'=>$g['engine'],'targets'=>$g['targets'],'summary'=>$g['summary'],'text'=>GameCatalog::rules($key),'translations'=>GameCatalog::translations($key)],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $colors=['red'=>'#ef4444','blue'=>'#3b82f6','green'=>'#22c55e','gold'=>'#facc15','brown'=>'#92400e','purple'=>'#a855f7','cyan'=>'#06b6d4','white'=>'#fff'];
 foreach($colors as $k=>$v){ DB::table('store_items')->updateOrInsert(['key'=>'name_'.$k],['name'=>json_encode(['ar'=>'لون اسم '.$k,'en'=>ucfirst($k).' name'],JSON_UNESCAPED_UNICODE),'category'=>'name_color','price'=>6500,'duration_days'=>30,'payload'=>json_encode(['color'=>$v]),'active'=>true,'created_at'=>now(),'updated_at'=>now()]); DB::table('store_items')->updateOrInsert(['key'=>'chat_'.$k],['name'=>json_encode(['ar'=>'لون كتابة '.$k,'en'=>ucfirst($k).' chat'],JSON_UNESCAPED_UNICODE),'category'=>'text_color','price'=>5200,'duration_days'=>30,'payload'=>json_encode(['color'=>$v]),'active'=>true,'created_at'=>now(),'updated_at'=>now()]); }
 foreach([['pasha_1',1,1700],['pasha_3',3,5000],['pasha_7',7,12000],['pasha_30',30,45000],['pasha_90',90,110000],['pasha_365',365,300000]] as [$key,$days,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>'باشا '.$days.' يوم','en'=>'Pasha '.$days.' days'],JSON_UNESCAPED_UNICODE),'category'=>'pasha','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['days'=>$days]),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 foreach([[2,12000,'#22c55e'],[3,26000,'#38bdf8'],[4,52000,'#a855f7'],[5,95000,'#f97316'],[6,155000,'#facc15'],[8,260000,'#ef4444']] as [$m,$price,$color]) DB::table('store_items')->updateOrInsert(['key'=>'xp_'.$m.'x'],['name'=>json_encode(['ar'=>'مسرّع XP x'.$m,'en'=>'XP booster x'.$m],JSON_UNESCAPED_UNICODE),'category'=>'xp_booster','price'=>$price,'duration_days'=>7,'payload'=>json_encode(['multiplier'=>$m,'color'=>$color]),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 foreach(['king'=>'👑','vip'=>'⭐','pro'=>'🔥','fairplay'=>'🛡️'] as $badge=>$icon) DB::table('store_items')->updateOrInsert(['key'=>'badge_'.$badge],['name'=>json_encode(['ar'=>'شارة '.$icon,'en'=>ucfirst($badge).' badge'],JSON_UNESCAPED_UNICODE),'category'=>'badge','price'=>30000,'duration_days'=>null,'payload'=>json_encode(['badge'=>$badge,'icon'=>$icon]),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $tables=[
  ['table_beginner_green','طاولة مبتدئ خضراء','Beginner Green Table','table-beginner-green','beginner',1500,30],
  ['table_beginner_blue','طاولة مبتدئ زرقاء','Beginner Blue Table','table-beginner-blue','beginner',2500,30],
  ['table_beginner_wood','طاولة مبتدئ خشبية','Beginner Wood Table','table-beginner-wood','beginner',4000,30],
  ['table_medium_emerald','طاولة متوسط زمردية','Medium Emerald Table','table-medium-emerald','medium',9000,45],
  ['table_medium_night','طاولة متوسط ليلية','Medium Night Table','table-medium-night','medium',13000,45],
  ['table_medium_desert','طاولة متوسط صحراوية','Medium Desert Table','table-medium-desert','medium',17000,45],
  ['table_pro_royal','طاولة محترف ملكية','Pro Royal Table','table-pro-royal','pro',35000,null],
  ['table_pro_fire','طاولة محترف نارية','Pro Fire Table','table-pro-fire','pro',50000,null],
  ['table_pro_ice','طاولة محترف جليدية','Pro Ice Table','table-pro-ice','pro',65000,null],
  ['table_legendary_gold','طاولة أسطوري ذهبية','Legendary Gold Table','table-legendary-gold','legendary',120000,null],
  ['table_legendary_galaxy','طاولة أسطوري مجرية','Legendary Galaxy Table','table-legendary-galaxy','legendary',175000,null],
  ['table_legendary_pasha','طاولة أسطوري باشا','Legendary Pasha Table','table-legendary-pasha','legendary',250000,null],
];
foreach($tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 
 // v52 premium items: richer cosmetics and higher economy prices.
 $cardBacks=[
  ['card_royal','ظهر ملكي','Royal Card Back','card-back-royal',42000,90],
  ['card_flame','ظهر ناري','Flame Card Back','card-back-flame',58000,90],
  ['card_ocean','ظهر محيطي','Ocean Card Back','card-back-ocean',62000,90],
  ['card_emerald','ظهر زمردي','Emerald Card Back','card-back-emerald',78000,null],
  ['card_galaxy','ظهر مجري أسطوري','Legendary Galaxy Back','card-back-galaxy',180000,null],
 ];
 foreach($cardBacks as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $frames=[['frame_diamond','إطار ماسي','Diamond Name Frame','frame-diamond',95000,null],['frame_royal','إطار ملكي','Royal Name Frame','frame-royal',140000,null],['frame_shadow','إطار بنفسجي','Shadow Name Frame','frame-shadow',115000,null],['frame_fire','إطار ناري','Fire Name Frame','frame-fire',125000,null]];
 foreach($frames as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'name_frame','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['frame'=>$css],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $effects=[['effect_confetti','مؤثر احتفال','Confetti Effect','effect-confetti',55000,30],['effect_neon','مؤثر نيون','Neon Effect','effect-neon',90000,60],['effect_crown','مؤثر التاج','Crown Effect','effect-crown',150000,null]];
 foreach($effects as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'effect','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['effect'=>$css],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $emojiPacks=[['emoji_fun','باقة إيموجي مرحة','Fun Emoji Pack','😄😂🔥👏',25000,null],['emoji_vip','باقة VIP','VIP Emoji Pack','👑💎⚡🏆',60000,null],['emoji_arabic','باقة عربية','Arabic Emoji Pack','☕🧿🌙🤍',45000,null]];
 foreach($emojiPacks as [$key,$ar,$en,$icons,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['emojis'=>$icons],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);



 $emojiSingles=[
  ['emoji_free_laugh_1','ضحكة مجانية 1','Free Laugh 1','😂',0],['emoji_free_laugh_2','ضحكة مجانية 2','Free Laugh 2','🤣',0],['emoji_free_love','حب مجاني','Free Love','😍',0],['emoji_free_hi','تحية مجانية','Free Hi','👋',0],['emoji_free_ok','موافق مجاني','Free OK','👍',0],
  ['emoji_laugh_bounce','ضحك متحرك','Animated Laugh','😂',1000],['emoji_laugh_tears','دموع الضحك','Laugh Tears','🤣',1000],['emoji_happy_star','فرحة نجوم','Happy Stars','🤩',1000],['emoji_clap','تصفيق','Clap','👏',1000],['emoji_wink','غمزة','Wink','😉',1000],
  ['emoji_angry_red','عصبية حمراء','Angry Red','😡',5000],['emoji_rage_fire','غضب ناري','Rage Fire','🔥',5000],['emoji_sad_blue','حزن أزرق','Sad Blue','😢',5000],['emoji_cry','بكاء','Cry','😭',5000],['emoji_shock','صدمة','Shock','😱',5000],['emoji_think','تفكير','Think','🤔',5000],['emoji_sleep','نعسان','Sleep','😴',5000],['emoji_coffee','قهوة','Coffee','☕',5000],['emoji_flower','وردة','Flower','🌹',5000],['emoji_moon','هلال','Moon','🌙',5000],
  ['emoji_firework','ألعاب نارية','Firework','🎆',10000],['emoji_trophy','كأس الفوز','Trophy','🏆',10000],['emoji_crown','تاج','Crown','👑',10000],['emoji_diamond','ماسة','Diamond','💎',10000],['emoji_lightning','برق','Lightning','⚡',10000],['emoji_party','احتفال','Party','🎉',10000],['emoji_money','توكنز','Money','🪙',10000],['emoji_shield','درع','Shield','🛡️',10000],['emoji_heart_fire','قلب ناري','Heart Fire','❤️‍🔥',10000],['emoji_ghost','شبح','Ghost','👻',10000],
  ['emoji_dragon','تنين أسطوري','Dragon','🐉',15000],['emoji_phoenix','عنقاء','Phoenix','🦅',15000],['emoji_royal_laugh','ضحكة ملكية','Royal Laugh','😂👑',15000],['emoji_royal_angry','غضب ملكي','Royal Angry','😡👑',15000],['emoji_royal_sad','حزن ملكي','Royal Sad','😢💎',15000],['emoji_neon_win','فوز نيون','Neon Win','🏆✨',15000],['emoji_magic','سحر','Magic','🪄',15000],['emoji_alien','فضائي','Alien','👽',15000],['emoji_bomb','قنبلة','Bomb','💣',15000],['emoji_rocket','صاروخ','Rocket','🚀',15000]
 ];
 foreach($emojiSingles as [$key,$ar,$en,$icons,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['emojis'=>$icons,'animated'=>$price>0,'single'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);

 $emojiPremium=[['emoji_animated_fire','إيموجي متحرك ناري','Animated Fire Emoji','🔥✨⚡🏆',120000,null],['emoji_animated_royal','إيموجي متحرك ملكي','Animated Royal Emoji','👑💎🥇🎉',180000,null],['emoji_animated_laugh','إيموجي متحرك مرح','Animated Fun Emoji','😂🤣😎👏',95000,60]];
 foreach($emojiPremium as [$key,$ar,$en,$icons,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['emojis'=>$icons,'animated'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $eliteColors=['ruby'=>'#dc2626','sapphire'=>'#2563eb','emerald_elite'=>'#059669','neon_pink'=>'#ec4899','royal_gold'=>'#f59e0b','ice_cyan'=>'#22d3ee'];
 foreach($eliteColors as $k=>$v){ DB::table('store_items')->updateOrInsert(['key'=>'elite_name_'.$k],['name'=>json_encode(['ar'=>'لون اسم فاخر '.$k,'en'=>'Elite name '.$k],JSON_UNESCAPED_UNICODE),'category'=>'name_color','price'=>22000,'duration_days'=>45,'payload'=>json_encode(['color'=>$v,'tier'=>'pro']),'active'=>true,'created_at'=>now(),'updated_at'=>now()]); DB::table('store_items')->updateOrInsert(['key'=>'elite_chat_'.$k],['name'=>json_encode(['ar'=>'لون كتابة فاخر '.$k,'en'=>'Elite chat '.$k],JSON_UNESCAPED_UNICODE),'category'=>'text_color','price'=>18000,'duration_days'=>45,'payload'=>json_encode(['color'=>$v,'tier'=>'pro']),'active'=>true,'created_at'=>now(),'updated_at'=>now()]); }


 // v57: merged player color + glowing frame cosmetics. One purchase controls name color, profile glow and avatar ring.
 $glowColors=[
  ['glow_ruby','Glow أحمر ياقوتي','Ruby Player Glow','#ef4444','glow-ruby',28000,45],
  ['glow_crimson','Glow قرمزي فاخر','Crimson Player Glow','#dc2626','glow-crimson',36000,60],
  ['glow_sapphire','Glow أزرق ياقوتي','Sapphire Player Glow','#2563eb','glow-sapphire',30000,45],
  ['glow_ocean','Glow محيطي','Ocean Player Glow','#06b6d4','glow-ocean',42000,60],
  ['glow_emerald','Glow زمردي','Emerald Player Glow','#10b981','glow-emerald',34000,45],
  ['glow_lime','Glow لايم نيون','Lime Neon Glow','#84cc16','glow-lime',45000,60],
  ['glow_violet','Glow بنفسجي','Violet Player Glow','#8b5cf6','glow-violet',38000,60],
  ['glow_pink','Glow وردي نيون','Pink Neon Glow','#ec4899','glow-pink',50000,60],
  ['glow_gold','Glow ذهبي ملكي','Royal Gold Glow','#f59e0b','glow-gold',65000,null],
  ['glow_diamond','Glow ألماسي','Diamond Glow','#e0f2fe','glow-diamond',90000,null],
  ['glow_fire','Glow ناري متحرك','Animated Fire Glow','#f97316','glow-fire',120000,null],
  ['glow_galaxy','Glow مجري أسطوري','Legendary Galaxy Glow','#a855f7','glow-galaxy',180000,null],
 ];
 foreach($glowColors as [$key,$ar,$en,$color,$frame,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'name_color','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['color'=>$color,'frame'=>$frame,'glow'=>$frame,'merged'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $chatColors=[
  ['chat_lava','كتابة حمم','Lava Chat','#ff6b00',26000,45],['chat_mint','كتابة نعناع','Mint Chat','#34d399',22000,45],['chat_sky','كتابة سماوية','Sky Chat','#38bdf8',24000,45],['chat_rose','كتابة وردية','Rose Chat','#fb7185',24000,45],['chat_neon','كتابة نيون','Neon Chat','#d946ef',48000,60],['chat_gold_plus','كتابة ذهبية فاخرة','Premium Gold Chat','#facc15',62000,null],['chat_ice','كتابة جليدية','Ice Chat','#cffafe',52000,60],['chat_shadow','كتابة بنفسجية','Shadow Chat','#c084fc',36000,45]
 ];
 foreach($chatColors as [$key,$ar,$en,$color,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'text_color','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['color'=>$color,'tier'=>'v57'],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v57Tables=[
  ['table_obsidian','طاولة أوبسيديان محترفة','Obsidian Pro Table','table-obsidian','pro',85000,null],['table_neon_city','طاولة مدينة نيون','Neon City Table','table-neon-city','pro',95000,null],['table_andalus','طاولة أندلسية','Andalus Table','table-andalus','medium',42000,90],['table_oasis','طاولة الواحة','Oasis Table','table-oasis','medium',38000,90],['table_dragon','طاولة التنين الأسطورية','Dragon Legendary Table','table-dragon','legendary',300000,null],['table_diamond_elite','طاولة النخبة الألماسية','Diamond Elite Table','table-diamond-elite','legendary',380000,null]
 ];
 foreach($v57Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v57Cards=[
  ['card_obsidian','ظهر ورق أوبسيديان','Obsidian Cards','card-back-obsidian',90000,null],['card_neon','ظهر ورق نيون','Neon Cards','card-back-neon',105000,null],['card_arabic','ظهر ورق عربي فاخر','Arabic Pattern Cards','card-back-arabic',75000,120],['card_dragon','ظهر ورق تنين','Dragon Cards','card-back-dragon',220000,null],['card_diamond','ظهر ورق ألماسي','Diamond Cards','card-back-diamond',280000,null]
 ];
 foreach($v57Cards as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v60 premium cosmetics: bigger badges, win effects, new text colors, tables and card backs.
 $v60Badges=[
  ['badge_royal_crown','شارة التاج الملكي','Royal Crown Badge','royal','👑',80000],
  ['badge_diamond_star','شارة النجمة الماسية','Diamond Star Badge','diamond','💎',95000],
  ['badge_dragon_fire','شارة التنين','Dragon Badge','dragon','🐉',120000],
  ['badge_guardian_shield','شارة الحارس','Guardian Badge','guardian','🛡️',70000],
  ['badge_galaxy_orb','شارة المجرة','Galaxy Badge','galaxy','🪐',150000],
  ['badge_phoenix','شارة العنقاء','Phoenix Badge','phoenix','🦅',130000],
  ['badge_ace_master','شارة الآس','Ace Master Badge','ace','🂡',110000],
  ['badge_lion_king','شارة الأسد','Lion King Badge','lion','🦁',160000],
  ['badge_legend_gold','شارة الأسطورة الذهبية','Golden Legend Badge','legend','🏆',200000],
  ['badge_fairplay_elite','شارة اللعب النظيف','Elite Fairplay Badge','fairplay','🤝',60000],
 ];
 foreach($v60Badges as [$key,$ar,$en,$badge,$icon,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'badge','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['badge'=>$badge,'icon'=>$icon],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v60Effects=[
  ['effect_gold_confetti','مؤثر ذهب متناثر','Gold Confetti Effect','effect-gold-confetti','🎊',85000,60],
  ['effect_fire_ring','مؤثر حلقة نارية','Fire Ring Effect','effect-fire-ring','🔥',120000,60],
  ['effect_diamond_burst','مؤثر انفجار ألماسي','Diamond Burst Effect','effect-diamond-burst','💎',150000,null],
  ['effect_neon_wave','مؤثر موجة نيون','Neon Wave Effect','effect-neon-wave','🌈',110000,60],
  ['effect_crown_rain','مؤثر مطر تيجان','Crown Rain Effect','effect-crown-rain','👑',180000,null],
  ['effect_lion_roar','مؤثر زئير الأسد','Lion Roar Effect','effect-lion-roar','🦁',220000,null],
  ['effect_phoenix_flare','مؤثر العنقاء','Phoenix Flare Effect','effect-phoenix-flare','🦅',210000,null],
  ['effect_galaxy_spark','مؤثر شرارة مجرية','Galaxy Spark Effect','effect-galaxy-spark','✨',200000,null],
  ['effect_victory_trophy','مؤثر كأس الفوز','Victory Trophy Effect','effect-victory-trophy','🏆',160000,null],
  ['effect_storm_flash','مؤثر عاصفة البرق','Storm Flash Effect','effect-storm-flash','⚡',140000,90],
 ];
 foreach($v60Effects as [$key,$ar,$en,$css,$icon,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'effect','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['effect'=>$css,'icon'=>$icon],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v60TextColors=[
  ['chat_neon_mint','كتابة نعناع نيون','Neon Mint Chat','#5eead4',8500],['chat_sunset','كتابة غروب','Sunset Chat','#fb923c',9000],['chat_rose','كتابة وردي','Rose Chat','#fb7185',9500],['chat_ice','كتابة جليدي','Ice Chat','#bae6fd',11000],['chat_laser','كتابة ليزر','Laser Chat','#22d3ee',12500],['chat_amethyst','كتابة جمشت','Amethyst Chat','#c084fc',13500],['chat_gold_elite','كتابة ذهبية فاخرة','Elite Gold Chat','#fde047',16000],['chat_ruby','كتابة ياقوت','Ruby Chat','#f43f5e',15000]
 ];
 foreach($v60TextColors as [$key,$ar,$en,$color,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'text_color','price'=>$price,'duration_days'=>60,'payload'=>json_encode(['color'=>$color],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v60Tables=[
  ['table_pro_lion_royal','طاولة محترف الأسد الملكي','Pro Royal Lion Table','table-lion-royal','pro',95000,null],
  ['table_pro_horse_gold','طاولة محترف الحصان الذهبي','Pro Golden Horse Table','table-horse-gold','pro',105000,null],
  ['table_pro_eagle_night','طاولة محترف النسر الليلي','Pro Night Eagle Table','table-eagle-night','pro',98000,null],
  ['table_advanced_wolf_ice','طاولة متقدم الذئب الجليدي','Advanced Ice Wolf Table','table-wolf-ice','advanced',75000,120],
  ['table_advanced_phoenix_fire','طاولة متقدم العنقاء النارية','Advanced Fire Phoenix Table','table-phoenix-fire','advanced',82000,120],
  ['table_legend_dragon','طاولة أسطوري التنين المتحرك','Legendary Moving Dragon Table','table-dragon-legend','legendary',320000,null],
  ['table_legend_signature_gold','طاولة أسطورية ذهبية باسم اللاعب','Legendary Signature Gold Table','table-signature-gold','legendary',450000,null],
 ];
 foreach($v60Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v60Cards=[
  ['card_sultan','ظهر السلطان','Sultan Card Back','card-back-sultan',125000,null],['card_mosaic','ظهر موزاييك عربي','Arabic Mosaic Back','card-back-mosaic',95000,120],['card_rose','ظهر الوردة','Rose Card Back','card-back-rose',80000,90],['card_panther','ظهر النمر الأسود','Black Panther Back','card-back-panther',140000,null],['card_sky','ظهر السماء','Sky Card Back','card-back-sky',70000,90]
 ];
 foreach($v60Cards as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v62: extra elite badges, win effects, legendary tables, premium card backs and bot identities.
 $v62Badges=[
  ['badge_sultan_gold','شارة السلطان الذهبية','Golden Sultan Badge','sultan','🕌',260000],
  ['badge_knight','شارة الفارس','Knight Badge','knight','♞',155000],
  ['badge_warqna_elite','شارة نخبة ورقنا','Warqna Elite Badge','warqna_elite','W',300000],
  ['badge_mastermind','شارة العقل المدبر','Mastermind Badge','mastermind','🧠',125000],
  ['badge_royal_ace','شارة الآس الملكي','Royal Ace Badge','royal_ace','A',175000],
  ['badge_stealth','شارة الظل','Stealth Badge','stealth','🥷',130000],
  ['badge_tornado','شارة الإعصار','Tornado Badge','tornado','🌪️',145000],
  ['badge_starfall','شارة الشهاب','Starfall Badge','starfall','☄️',170000],
  ['badge_ocean_king','شارة ملك البحر','Ocean King Badge','ocean_king','🔱',210000],
  ['badge_desert_hawk','شارة صقر الصحراء','Desert Hawk Badge','desert_hawk','🦅',190000],
 ];
 foreach($v62Badges as [$key,$ar,$en,$badge,$icon,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'badge','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['badge'=>$badge,'icon'=>$icon,'v62'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v62Effects=[
  ['effect_sultan_gate','مؤثر بوابة السلطان','Sultan Gate Effect','effect-sultan-gate','🕌',260000,null],
  ['effect_ace_storm','مؤثر عاصفة الآس','Ace Storm Effect','effect-ace-storm','🂡',185000,null],
  ['effect_blue_flame','مؤثر لهب أزرق','Blue Flame Effect','effect-blue-flame','🔥',170000,120],
  ['effect_gold_sword','مؤثر السيف الذهبي','Golden Sword Effect','effect-gold-sword','⚔️',230000,null],
  ['effect_royal_rain','مؤثر مطر ملكي','Royal Rain Effect','effect-royal-rain','👑',250000,null],
  ['effect_ocean_wave','مؤثر موج البحر','Ocean Wave Effect','effect-ocean-wave','🌊',160000,90],
  ['effect_desert_sand','مؤثر عاصفة رملية','Desert Sand Effect','effect-desert-sand','🏜️',150000,90],
  ['effect_shadow_smoke','مؤثر دخان الظل','Shadow Smoke Effect','effect-shadow-smoke','💨',145000,90],
  ['effect_laser_grid','مؤثر شبكة ليزر','Laser Grid Effect','effect-laser-grid','🔷',195000,null],
  ['effect_final_boss','مؤثر الزعيم الأخير','Final Boss Effect','effect-final-boss','💀',320000,null],
 ];
 foreach($v62Effects as [$key,$ar,$en,$css,$icon,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'effect','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['effect'=>$css,'icon'=>$icon,'v62'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v62Tables=[
  ['table_mythic_tiger','طاولة أسطورية النمر','Mythic Tiger Table','table-mythic-tiger','legendary',520000,null],
  ['table_mythic_falcon','طاولة أسطورية الصقر','Mythic Falcon Table','table-mythic-falcon','legendary',540000,null],
  ['table_mythic_whale','طاولة أسطورية الحوت الأزرق','Mythic Blue Whale Table','table-mythic-whale','legendary',500000,null],
  ['table_mythic_scorpion','طاولة أسطورية العقرب','Mythic Scorpion Table','table-mythic-scorpion','legendary',470000,null],
  ['table_pro_panther','طاولة محترف النمر الأسود','Pro Black Panther Table','table-pro-panther','pro',135000,null],
  ['table_pro_peacock','طاولة محترف الطاووس','Pro Peacock Table','table-pro-peacock','pro',125000,null],
  ['table_advanced_mosaic','طاولة متقدم موزاييك','Advanced Mosaic Table','table-advanced-mosaic','advanced',92000,120],
  ['table_advanced_marble','طاولة متقدم رخامية','Advanced Marble Table','table-advanced-marble','advanced',88000,120],
 ];
 foreach($v62Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'v62'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v62Cards=[
  ['card_royal_black','ظهر ورق أسود ملكي','Royal Black Cards','card-back-royal-black',160000,null],
  ['card_gold_edge','ظهر ورق حواف ذهبية','Gold Edge Cards','card-back-gold-edge',175000,null],
  ['card_neon_dragon','ظهر ورق تنين نيون','Neon Dragon Cards','card-back-neon-dragon',240000,null],
  ['card_oasis_blue','ظهر ورق واحة زرقاء','Blue Oasis Cards','card-back-oasis-blue',125000,120],
  ['card_lion_crest','ظهر ورق شعار الأسد','Lion Crest Cards','card-back-lion-crest',210000,null],
 ];
 foreach($v62Cards as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css,'v62'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v64 XP boosters: +25% to +500%, active for one day, must be used within 10 days.
 $v64Boosters=[
  ['xp_plus_25','مسرّع نقاط +25%','XP +25%',1.25,9000,'#22c55e'],
  ['xp_plus_50','مسرّع نقاط +50%','XP +50%',1.50,18000,'#38bdf8'],
  ['xp_plus_100','مسرّع نقاط +100%','XP +100%',2.00,36000,'#a855f7'],
  ['xp_plus_150','مسرّع نقاط +150%','XP +150%',2.50,65000,'#f97316'],
  ['xp_plus_200','مسرّع نقاط +200%','XP +200%',3.00,95000,'#facc15'],
  ['xp_plus_300','مسرّع نقاط +300%','XP +300%',4.00,160000,'#ef4444'],
  ['xp_plus_400','مسرّع نقاط +400%','XP +400%',5.00,240000,'#06b6d4'],
  ['xp_plus_500','مسرّع نقاط +500%','XP +500%',6.00,360000,'#f472b6'],
 ];
 foreach($v64Boosters as [$key,$ar,$en,$mult,$price,$color]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'xp_booster','price'=>$price,'duration_days'=>1,'payload'=>json_encode(['multiplier'=>$mult,'color'=>$color,'valid_days'=>10,'label'=>$ar],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v64SignatureTables=[
  ['table_signature_silver','طاولة باسم اللاعب - فضية','Player Signature Silver Table','table-signature-silver','legendary',380000,null],
  ['table_signature_rainbow','طاولة باسم اللاعب - ألوان مدمجة','Player Signature Rainbow Table','table-signature-rainbow','legendary',620000,null],
 ];
 foreach($v64SignatureTables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'signature'=>true,'v64'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v65: realistic elite tables, premium card backs, and pro store cosmetics.
 $v65Tables=[
  ['table_real_mahogany','طاولة واقعية خشب ماهوجني','Real Mahogany Table','table-real-mahogany','pro',180000,null],
  ['table_real_black_leather','طاولة جلد أسود واقعية','Real Black Leather Table','table-real-black-leather','pro',210000,null],
  ['table_real_casino_green','طاولة كازينو خضراء واقعية','Real Casino Green Table','table-real-casino-green','advanced',120000,120],
  ['table_real_blue_velvet','طاولة مخمل أزرق','Blue Velvet Table','table-real-blue-velvet','advanced',135000,120],
  ['table_elite_arabic_gold','طاولة نقش عربي ذهبي','Arabic Gold Engraved Table','table-elite-arabic-gold','legendary',420000,null],
  ['table_elite_marble_black','طاولة رخام أسود أسطورية','Legendary Black Marble Table','table-elite-marble-black','legendary',480000,null],
  ['table_signature_gold_big','طاولة اسم اللاعب ذهب كبير','Big Gold Player Name Table','table-signature-gold-big','signature',700000,null],
  ['table_signature_silver_big','طاولة اسم اللاعب فضة كبير','Big Silver Player Name Table','table-signature-silver-big','signature',620000,null],
  ['table_animated_lion_glow','طاولة أسد متحركة هادئة','Animated Lion Glow Table','table-animated-lion-glow','animated',850000,null],
  ['table_animated_horse_glow','طاولة حصان متحركة هادئة','Animated Horse Glow Table','table-animated-horse-glow','animated',780000,null],
  ['table_animated_dragon_smoke','طاولة تنين دخان متحرك','Animated Dragon Smoke Table','table-animated-dragon-smoke','animated',950000,null],

  ['table_glass_crystal','طاولة زجاج كريستال','Crystal Glass Table','table-glass-crystal','pro',260000,null],
  ['table_gold_palace','طاولة القصر الذهبية','Golden Palace Table','table-gold-palace','legendary',760000,null],
  ['table_glass_diamond','طاولة زجاج ألماسي','Diamond Glass Table','table-glass-diamond','legendary',680000,null],

 ];
 foreach($v65Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'signature'=>str_contains($css,'signature'),'animated'=>str_contains($css,'animated'),'v65'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v65Cards=[
  ['card_real_red','ظهر ورق أحمر واقعي','Real Red Card Back','card-back-real-red',90000,120],
  ['card_real_blue','ظهر ورق أزرق واقعي','Real Blue Card Back','card-back-real-blue',90000,120],
  ['card_black_gold','ظهر أسود وذهبي','Black Gold Card Back','card-back-black-gold',180000,null],
  ['card_silver_edge','ظهر حواف فضية','Silver Edge Card Back','card-back-silver-edge',165000,null],
  ['card_signature_gold','ظهر ورق اسم ذهبي','Gold Signature Card Back','card-back-signature-gold',330000,null],
 ];
 foreach($v65Cards as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css,'v65'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v77: final normalized XP boosters: six boosters only, 24h active duration, 10-day validity metadata.
 DB::table('store_items')->where('category','xp_booster')->update(['active'=>false]);
 $v77Boosters=[
  ['xp_x1_25','مسرّع نقاط x1.25','XP Booster x1.25',1.25,12000,'#22c55e'],
  ['xp_x1_5','مسرّع نقاط x1.5','XP Booster x1.5',1.5,25000,'#38bdf8'],
  ['xp_x2','مسرّع نقاط x2','XP Booster x2',2.0,52000,'#a855f7'],
  ['xp_x3','مسرّع نقاط x3','XP Booster x3',3.0,110000,'#f97316'],
  ['xp_x4','مسرّع نقاط x4','XP Booster x4',4.0,190000,'#facc15'],
  ['xp_x5','مسرّع نقاط x5','XP Booster x5',5.0,320000,'#ef4444'],
 ];
 foreach($v77Boosters as [$key,$ar,$en,$mult,$price,$color]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'xp_booster','price'=>$price,'duration_days'=>1,'payload'=>json_encode(['multiplier'=>$mult,'color'=>$color,'valid_days'=>10,'label'=>'x'.$mult],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 // v77: more table realism and richer emoji tiers.
 $v77Tables=[
  ['table_ultra_eagle','طاولة النسر الملكي الواقعية','Ultra Royal Eagle Table','table-ultra-eagle','legendary',880000,null],
  ['table_ultra_tiger','طاولة النمر الواقعية','Ultra Real Tiger Table','table-ultra-tiger','legendary',920000,null],
  ['table_ultra_panther','طاولة الفهد الأسود الواقعية','Ultra Black Panther Table','table-ultra-panther','legendary',930000,null],
  ['table_ultra_crystal_gold','طاولة كريستال ذهبية','Ultra Crystal Gold Table','table-ultra-crystal-gold','pro',720000,null],
  ['table_signature_crown','طاولة اسم اللاعب والتاج','Player Crown Signature Table','table-signature-crown','signature',990000,null],
 ];
 foreach($v77Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'signature'=>str_contains($css,'signature'),'v77'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v77Emojis=[
  ['emoji_tab_beginner','إيموجي مبتدئ','Beginner Emoji','🙂😄👍👏',0,'beginner'],
  ['emoji_tab_medium','إيموجي متوسط','Medium Emoji','😂🤣😎🤝',5000,'medium'],
  ['emoji_tab_pro','إيموجي محترف','Pro Emoji','🔥⚡🏆🎯',12000,'pro'],
  ['emoji_tab_legendary','إيموجي أسطوري','Legendary Emoji','👑💎🐉🦅',35000,'legendary'],
  ['emoji_tab_animated','إيموجي متحرك','Animated Emoji','✨🎉💥🌟',60000,'animated'],
  ['emoji_tab_big','إيموجي كبير','Big Emoji','😡😭😂👑',45000,'big'],
 ];
 foreach($v77Emojis as [$key,$ar,$en,$icons,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['emojis'=>$icons,'emoji_tier'=>$tier,'animated'=>in_array($tier,['animated','legendary']), 'v77'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);


 // v82: additional premium emoji tiers, realistic tables, richer card backs.
 $v82Emojis=[
  ['emoji_v82_free_big','مجاني كبير','Free Big Emoji','😀😁😅😇🙂🙃😉😊😍😘',0,'free'],
  ['emoji_v82_beginner_fun','مبتدئ مرح','Beginner Fun','😂🤣😄😆😎🤩👏👍🙌👌',1500,'beginner'],
  ['emoji_v82_medium_mood','متوسط مشاعر','Medium Mood','😡😤😢😭😱😳🤔😴😬😅',7000,'medium'],
  ['emoji_v82_pro_power','محترف قوي','Pro Power','🔥⚡💪🏆🎯🚀💣🛡️💰💎',18000,'pro'],
  ['emoji_v82_legendary_animals','أسطوري حيوانات','Legendary Animals','🦁🐯🦅🐉🐺🦂🐎🦊🐆🦚',45000,'legendary'],
  ['emoji_v82_animated_fx','متحرك مؤثرات','Animated FX','✨🎉💥🌟⚜️🔱👑💫🌈☄️',65000,'animated'],
  ['emoji_v82_huge_reactions','كبير ردود فعل','Huge Reactions','😂😂😂 👑👑👑 🔥🔥🔥 😡😡😡 😭😭😭',50000,'big'],
 ];
 foreach($v82Emojis as [$key,$ar,$en,$icons,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['emojis'=>$icons,'emoji_tier'=>$tier,'animated'=>in_array($tier,['animated','legendary','big']),'v82'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v82Tables=[
  ['table_v82_real_eagle_gold','طاولة النسر الذهبي الكبيرة','Big Golden Eagle Table','table-v82-eagle-gold','legendary',980000,null],
  ['table_v82_real_tiger_shadow','طاولة النمر الظل الواقعية','Real Shadow Tiger Table','table-v82-tiger-shadow','legendary',990000,null],
  ['table_v82_glass_ocean','طاولة زجاج المحيط','Ocean Glass Table','table-v82-glass-ocean','pro',480000,null],
  ['table_v82_royal_black_gold','طاولة ملكية أسود وذهب','Royal Black Gold Table','table-v82-black-gold','legendary',850000,null],
  ['table_v82_signature_neon','طاولة اسم اللاعب نيون','Neon Signature Player Table','table-v82-signature-neon','signature',900000,null],
 ];
 foreach($v82Tables as [$key,$ar,$en,$css,$tier,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'signature'=>str_contains($css,'signature'),'v82'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
 $v82Cards=[
  ['card_v82_luxury_black','ظهر ورق فاخر أسود','Luxury Black Back','card-back-v82-black',115000,null],
  ['card_v82_royal_gold','ظهر ورق ملكي ذهبي','Royal Gold Back','card-back-v82-gold',160000,null],
  ['card_v82_glass_blue','ظهر ورق زجاج أزرق','Glass Blue Back','card-back-v82-glass-blue',130000,null],
 ];
 foreach($v82Cards as [$key,$ar,$en,$css,$price,$days]) DB::table('store_items')->updateOrInsert(['key'=>$key],['name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>$days,'payload'=>json_encode(['card_back'=>$css,'v82'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);

 $club=Club::firstOrCreate(['name'=>'Warqna Elite'],['owner_id'=>$admin->id,'level'=>5,'weekly_points'=>95000,'treasury'=>1000000]); ClubMember::firstOrCreate(['club_id'=>$club->id,'user_id'=>$admin->id],['role'=>'owner','permissions'=>['all'=>true],'weekly_points'=>9999]);
 $tarneeb=DB::table('games')->where('key','tarneeb')->first(); if($tarneeb) Tournament::firstOrCreate(['creator_id'=>$admin->id,'game_id'=>$tarneeb->id,'status'=>'open'],['stages'=>3,'seats_per_match'=>4,'entry_fee'=>0,'prize_pool'=>200,'bracket'=>[]]);

 // v145 curated mobile catalog + additional test accounts.
 $v145Games=['tarneeb','trix','hand','banakil','baloot','basra','tarneeb_400','syrian_tarneeb','trix_complex','saudi_hand','hand_partner','trix_partner'];
 if (\Illuminate\Support\Facades\Schema::hasTable('games')) {
   DB::table('games')->update(['active'=>false,'updated_at'=>now()]);
   DB::table('games')->whereIn('key',$v145Games)->update(['active'=>true,'updated_at'=>now()]);
   DB::table('games')->where('key','banakil')->update(['name'=>json_encode(['ar'=>'بناكل','en'=>'Banakil'],JSON_UNESCAPED_UNICODE),'updated_at'=>now()]);
   DB::table('games')->where('key','basra')->update(['name'=>json_encode(['ar'=>'باصرة','en'=>'Basra'],JSON_UNESCAPED_UNICODE),'updated_at'=>now()]);
 }
 foreach ([
   ['Samar','samar@warqna.local','Samar12345','#f97316','PS',24,95000,'🦋'],
   ['Layla','layla@warqna.local','Layla12345','#c084fc','JO',31,110000,'🌙'],
   ['Jameel','jameel@warqna.local','Jameel12345','#22d3ee','PS',22,88000,'🐯'],
   ['Nour','nour@warqna.local','Nour12345','#f472b6','EG',19,76000,'⭐'],
   ['Yaser','yaser@warqna.local','Yaser12345','#a3e635','SA',27,130000,'🦅'],
   ['Omar','omar@warqna.local','Omar12345','#60a5fa','PS',32,90000,'🛡️'],
   ['Sara','sara@warqna.local','Sara12345','#f472b6','JO',29,85000,'👑'],
   ['Basel','basel@warqna.local','Basel12345','#34d399','PS',38,120000,'🔥'],
   ['Hala','hala@warqna.local','Hala12345','#c084fc','EG',26,78000,'💎'],
   ['Yazan','yazan@warqna.local','Yazan12345','#f59e0b','SA',41,150000,'⚡'],
 ] as [$username,$email,$password,$color,$country,$level,$tokens]) {
   $u=User::updateOrCreate(['email'=>$email],['username'=>$username,'password'=>Hash::make($password),'is_admin'=>false,'is_banned'=>false]);
   Profile::updateOrCreate(['user_id'=>$u->id],['display_name'=>$username,'country_code'=>$country,'country_name'=>country_name($country),'level'=>$level,'xp'=>$level*1200,'games_played'=>$level*15,'wins'=>$level*7,'name_color'=>$color,'chat_color'=>$color,'pasha_days'=>0,'badge'=>'pro']);
   Wallet::updateOrCreate(['user_id'=>$u->id],['tokens'=>$tokens,'gems'=>0]);
 }

// v105: normalized store manifest from uploaded docs: pasha year, 6 boosters, tiered tables/emojis, glow colors.
DB::table('store_items')->updateOrInsert(['key'=>'pasha_365'],[
 'name'=>json_encode(['ar'=>'باشا 365 يوم','en'=>'Pasha 365 days'],JSON_UNESCAPED_UNICODE),'category'=>'pasha','price'=>320000,'duration_days'=>365,'payload'=>json_encode(['days'=>365,'vip_year'=>true,'admin_frame'=>'gold'],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
DB::table('store_items')->where('category','xp_booster')->update(['active'=>false]);
$v105Boosters=[
 ['xp_v105_bronze_2x','المسرّع البرونزي x2','Bronze Booster x2',2,4000,'#b87333','bronze'],
 ['xp_v105_silver_2x','المسرّع الفضي x2','Silver Booster x2',2,10000,'#c0c0c0','silver'],
 ['xp_v105_gold_3x','المسرّع الذهبي x3','Gold Booster x3',3,12000,'#facc15','gold'],
 ['xp_v105_diamond_3x','المسرّع الماسي x3','Diamond Booster x3',3,40000,'#38bdf8','diamond'],
 ['xp_v105_royal_4x','المسرّع الملكي x4','Royal Booster x4',4,80000,'#a855f7','royal'],
 ['xp_v105_legend_5x','المسرّع الأسطوري x5','Legendary Booster x5',5,150000,'#ef4444','legendary'],
];
foreach($v105Boosters as [$key,$ar,$en,$mult,$price,$color,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'xp_booster','price'=>$price,'duration_days'=>1,'payload'=>json_encode(['multiplier'=>$mult,'color'=>$color,'valid_days'=>10,'label'=>'x'.$mult,'tier'=>$tier,'v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
$v105GlowColors=[
 ['glow_standard_gold','ذهبي أساسي Glow','Standard Gold Glow','#facc15',5000,'standard'],
 ['glow_standard_green','أخضر أساسي Glow','Standard Green Glow','#22c55e',5000,'standard'],
 ['glow_neon_blue','أزرق نيون Glow','Neon Blue Glow','#38bdf8',9000,'neon'],
 ['glow_neon_purple','بنفسجي نيون Glow','Neon Purple Glow','#a855f7',9000,'neon'],
 ['glow_rgb_royal','ملكي متحرك RGB','Royal RGB Animated Glow','#ffffff',15000,'animated'],
];
foreach($v105GlowColors as [$key,$ar,$en,$color,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'name_color','price'=>$price,'duration_days'=>30,'payload'=>json_encode(['color'=>$color,'glow'=>'glow-'.$tier,'frame'=>'glow-'.$tier,'tier'=>$tier,'animated'=>$tier==='animated','v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
$v105ChatColors=[
 ['chat_dark_red','كتابة أحمر غامق','Dark Red Chat','#991b1b',5000,'standard'],
 ['chat_navy','كتابة كحلي','Navy Chat','#1e3a8a',5000,'standard'],
 ['chat_fire','كتابة ناري','Fire Chat','#ef4444',10000,'vip'],
 ['chat_cyan','كتابة سيان مشع','Cyan Glow Chat','#22d3ee',10000,'vip'],
 ['chat_gradient_royal','كتابة ملكية متدرجة','Royal Gradient Chat','#facc15',15000,'animated'],
];
foreach($v105ChatColors as [$key,$ar,$en,$color,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'text_color','price'=>$price,'duration_days'=>30,'payload'=>json_encode(['color'=>$color,'tier'=>$tier,'gradient'=>$tier==='animated','v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
$v105Tables=[
 ['table_v105_wood','طاولة الخشب العتيق','Antique Wood Table','table-v105-wood','beginner',20000],
 ['table_v105_blue_velvet','طاولة المخمل الأزرق','Blue Velvet Table','table-v105-blue','medium',45000],
 ['table_v105_orient','طاولة النقش الشرقي','Oriental Pattern Table','table-v105-orient','featured',60000],
 ['table_v105_neon','طاولة النيون المضيئة','Neon Casino Table','table-v105-neon','pro',90000],
 ['table_v105_palace','طاولة القصر الأسطورية','Legendary Palace Table','table-v105-palace','legendary',150000],
 ['table_v105_emerald_animated','طاولة الزمرد المتحركة','Animated Emerald Table','table-v105-emerald-animated','animated',220000],
];
foreach($v105Tables as [$key,$ar,$en,$css,$tier,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'table','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['table'=>$css,'tier'=>$tier,'tab'=>$tier,'signature'=>in_array($tier,['legendary','animated']),'v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
$v105Cards=[
 ['card_v105_black_gold','ظهر ورق أسود ذهبي','Black Gold Cards','cardback-v105-black-gold',25000],
 ['card_v105_royal_red','ظهر ورق ملكي أحمر','Royal Red Cards','cardback-v105-royal-red',55000],
 ['card_v105_emerald','ظهر ورق زمردي','Emerald Cards','cardback-v105-emerald',75000],
 ['card_v105_animated_star','ظهر ورق نجمي متحرك','Animated Star Cards','cardback-v105-star-animated',120000],
];
foreach($v105Cards as [$key,$ar,$en,$css,$price]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'card_back','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['card_back'=>$css,'v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
$v105Emoji=[
 ['emoji_v105_free','إيموجي مجانية','Free Emojis','😀 😄 👍 👏 👋',0,'free'],
 ['emoji_v105_laugh','إيموجي الضحك','Laugh Emojis','😂 🤣 😆 😹',1000,'laugh'],
 ['emoji_v105_happy','إيموجي الفرح','Happy Emojis','😊 🥳 🤩 🎉',5000,'happy'],
 ['emoji_v105_angry','إيموجي العصبية','Angry Emojis','😡 😤 🤬 🔥',10000,'angry'],
 ['emoji_v105_sad','إيموجي الحزن','Sad Emojis','😢 😭 💔 😞',10000,'sad'],
 ['emoji_v105_vip','إيموجي VIP','VIP Emojis','👑 💎 🏆 ⚡',15000,'vip'],
 ['emoji_v105_animated','إيموجي متحرك','Animated Emojis','✨ 💥 🌟 🎆',15000,'animated'],
];
foreach($v105Emoji as [$key,$ar,$en,$icons,$price,$tier]) DB::table('store_items')->updateOrInsert(['key'=>$key],[
 'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),'category'=>'emoji_pack','price'=>$price,'duration_days'=>null,'payload'=>json_encode(['emojis'=>$icons,'emoji_tier'=>$tier,'animated'=>in_array($tier,['animated','vip']),'large'=>in_array($tier,['animated','vip']),'v105'=>true],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now()
]);
 }}
