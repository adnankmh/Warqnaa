<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Game,Room,RoomPlayer,Notification};
use App\Services\Security\AntiCheatService;
use App\Services\Wallet\WalletService;
use App\Services\GameEngine\GameFactory;
use App\Services\WarqnaPro\PlayActionNormalizer;
use Illuminate\Support\Facades\{DB,Hash,Schema,Log};

class RoomController
{
 private array $seatOrder = ['south','south_east','east','north','west','south_west'];
 private array $seatLabels = ['south'=>'أسفل','north'=>'أعلى','west'=>'يسار','east'=>'يمين','south_west'=>'أسفل يسار','south_east'=>'أسفل يمين'];

 public function index(Game $game){
  $uid=auth()->id();
  $rooms=Room::with(['players.user.profile','game'])->where('game_id',$game->id)->whereIn('status',['waiting','bidding','playing'])
   ->where(function($q) use($uid){ $q->where('visibility','public')->orWhere('owner_id',$uid)->orWhereHas('players',fn($p)=>$p->where('user_id',$uid)); })
   ->latest()->get();
  $leaders=\App\Models\User::with('profile')->whereHas('profile')->get()->sortByDesc(fn($u)=>$u->profile->games_played ?? 0)->take(10);
  $allowedSeats=$this->allowedSeatCounts($game);
  return view('room.index',compact('game','rooms','leaders','allowedSeats'));
 }
 public function store(Request $r, WalletService $wallet){
  try{
   $data=$r->validate([
    'game_id'=>'required|exists:games,id','room_type'=>'nullable|in:public,private,voice','visibility'=>'nullable|in:public,friends,private',
    'password'=>'nullable|string|max:80','max_players'=>'nullable|integer|min:2|max:6','min_level'=>'nullable|integer|min:1|max:100',
    'target_score'=>'nullable|string|max:20','voice_room'=>'nullable|boolean','speed'=>'nullable|in:slow,medium,fast','allow_owner_kick'=>'nullable|boolean','leave_xp_penalty'=>'nullable|boolean'
   ]);
   $user=auth()->user();
   if(!$user) return $this->friendlyRoomFail('يجب تسجيل الدخول أولًا.');
   // Ensure profile/wallet exist after fresh DB reset or old copied accounts.
   if(!$user->profile){ $user->profile()->create(['display_name'=>$user->username,'country_code'=>'PS','country_name'=>country_name('PS'),'level'=>1,'xp'=>0]); $user->load('profile'); }
   if(!$user->wallet){ $user->wallet()->create(['tokens'=>$user->is_admin ? 1000000000000000000 : 50000]); $user->load('wallet'); }

   $game=Game::where('active',true)->findOrFail($data['game_id']);
   $activeRoom=Room::whereHas('players',fn($q)=>$q->where('user_id',$user->id)->where('is_bot',false)->where('connected',true))
    ->whereIn('status',['waiting','bidding','playing'])->latest()->first();
   if($activeRoom){
    $msg='أنت داخل لعبة أخرى بالفعل. افتح غرفتك الحالية أو غادرها قبل إنشاء غرفة جديدة.';
    if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>$msg,'url'=>route('rooms.show',$activeRoom->code)]);
    return redirect()->route('rooms.show',$activeRoom->code)->with('ok',$msg);
   }

   $roomType=$data['room_type'] ?? ($data['visibility'] ?? 'public');
   $visibility=$roomType==='private' ? 'private' : 'public';
   $voiceRoom=($roomType==='voice') || !empty($data['voice_room']);
   if($visibility==='private' && empty($data['password'])) return $this->friendlyRoomFail('أدخل كلمة سر للغرفة الخاصة.');

   $allowed=$this->allowedSeatCounts($game);
   $max=(int)($data['max_players']??($allowed[0] ?? $game->max_players));
   if(!in_array($max,$allowed,true)) return $this->friendlyRoomFail('عدد اللاعبين غير مناسب لهذه اللعبة. الخيارات الصحيحة: '.implode(' / ',$allowed));

   $creatorLevel=(int)($user->profile?->level ?? 1);
   if(((int)($data['min_level'] ?? 1)) > $creatorLevel) return $this->friendlyRoomFail('الحد الأدنى للمستوى لا يمكن أن يكون أعلى من مستواك الحالي.');
   $target=$data['target_score'] ?? null;
   if(in_array($game->key,['tarneeb','tarneeb_400','tarneeb_41'],true)) $target=in_array((string)($target ?: '31'), ['31','41','61'], true) ? (string)($target ?: '31') : '31';

   // v142: gameplay and voice rooms are free. Tokens are deducted only for confirmed store purchases.

   $room=DB::transaction(function() use($game,$user,$visibility,$data,$max,$target,$voiceRoom){
    $state=['phase'=>'waiting','score'=>['teamA'=>0,'teamB'=>0],'voice_room'=>$voiceRoom,'voice_fee'=>0,'allow_owner_kick'=>!empty($data['allow_owner_kick']),'leave_xp_penalty'=>!empty($data['leave_xp_penalty']),'leave_xp_penalty_amount'=>200,
     'speed'=>$this->normalizeSpeed($data['speed'] ?? 'medium')[0],'turn_timeout_seconds'=>$this->normalizeSpeed($data['speed'] ?? 'medium')[1],
     'plain_room_password'=>$visibility==='private' ? (string)$data['password'] : null,
     'log'=>[],'messages'=>['تم إنشاء الغرفة بنجاح. اضغط بدء اللعبة للجولة الأولى فقط، وبعدها تنتقل الجولات تلقائيًا.']];
    $room=Room::create($this->safeColumns('rooms',[
     'code'=>$this->uniqueRoomCode(),'game_id'=>$game->id,'owner_id'=>$user->id,'visibility'=>$visibility,
     'password'=>($visibility==='private') ? Hash::make((string)$data['password']) : null,'entry_fee'=>0,'min_level'=>$data['min_level']??1,
     'target_score'=>$target,'max_players'=>$max,'status'=>'waiting','state'=>$state
    ]));
    $room->players()->create($this->safeColumns('room_players',['user_id'=>$user->id,'seat'=>'south','is_bot'=>false,'connected'=>true,'missed_turns'=>0]));
    return $room;
   });

   if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>'تم إنشاء الغرفة بنجاح','url'=>route('rooms.show',$room->code)]);
   return redirect()->route('rooms.show',$room->code)->with('ok','تم إنشاء الغرفة بنجاح');
  }catch(\Illuminate\Validation\ValidationException $e){ return $this->friendlyRoomFail($e->validator->errors()->first() ?: 'بيانات إنشاء الغرفة غير صحيحة.');
  }catch(\Throwable $e){ Log::error('Warqna room create failed',['error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]); return $this->friendlyRoomFail('تعذر إنشاء الغرفة: '.$this->safeError($e->getMessage()).'. إذا حذفت قاعدة البيانات شغّل reset-database-windows.bat ثم start-windows.bat.'); }
 }


 private function uniqueRoomCode(): string
 {
  do { $code=(string)random_int(100000,999999); } while(Room::where('code',$code)->exists());
  return $code;
 }

 private function safeColumns(string $table, array $data): array{
  try{ return array_filter($data, fn($v,$k)=>Schema::hasColumn($table,$k), ARRAY_FILTER_USE_BOTH); }
  catch(\Throwable $e){ return $data; }
 }
 private function safeError(string $message): string{
  $message=trim($message);
  if(str_contains($message,'no such column')) return 'قاعدة البيانات تحتاج تحديث. شغّل reset-database-windows.bat أو setup-windows.bat مرة أخرى';
  if(str_contains($message,'NOT NULL')) return 'يوجد حقل مطلوب ناقص في نموذج إنشاء الغرفة';
  if(str_contains($message,'Integrity constraint')) return 'يوجد تعارض في بيانات الغرفة أو رمزها، حاول مرة أخرى';
  return mb_substr($message ?: 'خطأ غير معروف',0,180);
 }


 private function normalizeSpeed(?string $speed): array{
  $speed=in_array($speed,['slow','medium','fast'],true)?$speed:'medium';
  // v131: لا يزيد عداد الدور عن 10 ثواني في أي لعبة.
  return [$speed, ['slow'=>10,'medium'=>7,'fast'=>5][$speed]];
 }
 private function realPlayersCount(Room $room, bool $connectedOnly=false): int{
  $q=$room->players()->where('is_bot',false)->whereNotNull('user_id');
  if($connectedOnly) $q->where('connected',true);
  return (int)$q->count();
 }
 private function closeIfNoRealPlayers(Room $room, array $state, string $reason='تم إغلاق الغرفة لأن كل اللاعبين الحقيقيين خرجوا.'): bool{
  $room->refresh();
  // A temporary disconnect must not close the room. Keep it recoverable while
  // at least one real-player seat still exists; inactivity replacement is
  // handled separately and can be reclaimed by the same user.
  if($this->realPlayersCount($room,false)===0){
   $state['messages'][]=$reason;
   $state['closed_reason']=$reason;
   $state['closed_at']=now()->toIso8601String();
   $room->update(['state'=>$state,'status'=>'closed','finished_at'=>now()]);
   return true;
  }
  return false;
 }

 private function friendlyRoomFail(string $message){
  if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>false,'message'=>$message],200);
  return back()->withInput()->withErrors(['msg'=>$message]);
 }

 public function show(Room $room){
  $activeRoom=Room::where('id','!=',$room->id)->whereHas('players',fn($q)=>$q->where('user_id',auth()->id())->where('is_bot',false)->where('connected',true))->whereIn('status',['waiting','bidding','playing'])->latest()->first();
  if($activeRoom) return redirect()->route('rooms.show',$activeRoom->code)->with('ok','أنت داخل لعبة أخرى بالفعل. غادرها أولًا قبل فتح لعبة جديدة.');
  $room->load('game','players.user.profile');
  $room->players()->where('user_id',auth()->id())->where('is_bot',false)->update(['connected'=>true]);
  $room->load('game','players.user.profile');
  $seatPlayers=$room->players->keyBy('seat');
  $seats=array_slice($this->seatOrder,0,$room->max_players);
  $myKey='user:'.auth()->id();
  $state=$room->state ?: [];
  $myHand=$state['hands'][$myKey] ?? [];
  
  $activeTableSkin=$this->bestActiveCosmetic($room,'table','table') ?: (auth()->user()->profile?->active_table_skin);
  $activeCardBack=$this->bestActiveCosmetic($room,'card_back','card_back') ?: (auth()->user()->profile?->active_card_back);
  $activeTableImage=$this->bestActiveCosmetic($room,'table','table_image');
  $activeCardBackImage=$this->bestActiveCosmetic($room,'card_back','card_back_image');
  return view('room.show',compact('room','seatPlayers','seats','myKey','myHand','activeTableSkin','activeCardBack','activeTableImage','activeCardBackImage'));
 }
 public function join(Room $room, Request $r, WalletService $wallet){
  abort_if(in_array($room->status,['closed','finished'],true),403,'الغرفة مغلقة');
  $other=Room::where('id','!=',$room->id)->whereHas('players',fn($q)=>$q->where('user_id',auth()->id())->where('is_bot',false))->whereIn('status',['waiting','bidding','playing'])->first();
  abort_if($other,403,'أنت داخل لعبة أخرى بالفعل. غادرها أولًا ثم ادخل هذه الغرفة.');
  $room->load('game','players.user.profile');
  $state=$room->state ?: [];
  $banned=array_map('intval',$state['banned_user_ids'] ?? []);
  abort_if(in_array(auth()->id(),$banned,true),403,'تم إخراجك من هذه اللعبة ولا يمكنك العودة لنفس الغرفة.');
  $existing=$room->players()->where('user_id',auth()->id())->first();
  if($existing){ if(!$existing->connected){$existing->update(['connected'=>true,'missed_turns'=>0]); return redirect()->route('rooms.show',$room->code)->with('ok','عدت إلى نفس مقعدك في الغرفة');} abort(409,'أنت داخل الغرفة بالفعل'); }
  $manualExits=(array)($state['manual_exit_counts'] ?? $state['manual_leave_counts'] ?? []);
  abort_if((int)($manualExits[auth()->id()] ?? 0) >= 3,403,'تم منع العودة إلى هذه الغرفة بعد ثلاث مرات خروج يدوي.');
  $displaced=$state['disconnected_replacements'][auth()->id()] ?? null;
  if($displaced){
   $bot=$room->players->firstWhere('id',(int)($displaced['room_player_id'] ?? 0));
   if($bot && $bot->is_bot){
    $oldKey='bot:'.$bot->id; $newKey='user:'.auth()->id();
    $bot->update(['user_id'=>auth()->id(),'is_bot'=>false,'bot_key'=>null,'connected'=>true,'missed_turns'=>0]);
    if(isset($state['hands'][$oldKey])){ $state['hands'][$newKey]=$state['hands'][$oldKey]; unset($state['hands'][$oldKey]); }
    if(($state['turn'] ?? null)===$oldKey) $state['turn']=$newKey;
    if(!empty($state['players'])) $state['players']=array_values(array_map(fn($p)=>$p===$oldKey?$newKey:$p,$state['players']));
    $state['disconnected_replacements'][auth()->id()]['returns']=(int)($displaced['returns'] ?? 0)+1;
    $state['messages'][]=auth()->user()->username.' عاد إلى نفس مقعده بعد انقطاع مؤقت.';
    $room->update(['state'=>$state]);
    return redirect()->route('rooms.show',$room->code)->with('ok','عدت إلى نفس مقعدك. الانقطاع المؤقت لا يُحسب خروجًا يدويًا.');
   }
  }
  abort_if((auth()->user()->profile?->level ?? 1) < $room->min_level,403,'مستواك أقل من المطلوب');
  if($room->visibility==='private') abort_unless($room->password && Hash::check((string)$r->input('password'), $room->password),403,'كلمة السر غير صحيحة');
  // v142: joining any room is free; no gameplay token deduction.

  $allowed=array_slice($this->seatOrder,0,$room->max_players);
  $requestedSeat=(string)$r->input('seat','');
  $target=null;
  if($requestedSeat && in_array($requestedSeat,$allowed,true)) $target=$room->players->firstWhere('seat',$requestedSeat);
  if(!$requestedSeat) $target=null;

  if($target && $target->is_bot && $requestedSeat){
   $oldKey='bot:'.$target->id; $newKey='user:'.auth()->id();
   $target->update(['user_id'=>auth()->id(),'is_bot'=>false,'bot_key'=>null,'connected'=>true,'missed_turns'=>0]);
   if(isset($state['hands'][$oldKey])){ $state['hands'][$newKey]=$state['hands'][$oldKey]; unset($state['hands'][$oldKey]); }
   if(($state['turn'] ?? null)===$oldKey) $state['turn']=$newKey;
   if(!empty($state['players'])) $state['players']=array_values(array_map(fn($p)=>$p===$oldKey?$newKey:$p,$state['players']));
   $state['messages'][]=auth()->user()->username.' جلس مكان البوت في المقعد '.$target->seat.'.';
   $room->update(['state'=>$state]);
   return redirect()->route('rooms.show',$room->code)->with('ok','جلست مكان البوت بنجاح');
  }

  abort_if($room->players()->count() >= $room->max_players,403,'الغرفة ممتلئة ولا يوجد مقعد فارغ.');
  $taken=$room->players()->pluck('seat')->all();
  abort_if(!$requestedSeat || !in_array($requestedSeat,$allowed,true),403,'اختر مقعدًا واضحًا. لن يتم نقلك تلقائيًا إلى مقعد آخر.');
  abort_if(in_array($requestedSeat,$taken,true),403,'هذا المقعد مشغول. اختر مقعدًا فارغًا فقط.');
  $seat=$requestedSeat;
  $room->players()->create(['user_id'=>auth()->id(),'seat'=>$seat,'is_bot'=>false,'connected'=>true,'missed_turns'=>0]);
  return redirect()->route('rooms.show',$room->code);
 }
 public function addBot(Room $room){
  abort_unless($room->owner_id===auth()->id() || auth()->user()->is_admin,403);
  abort_if($room->status!=='waiting',422,'لا يمكن إضافة بوت بعد بداية الجولة');
  abort_if($room->players()->count() >= $room->max_players,403,'الغرفة ممتلئة');
  $bot=$this->pickBotIdentity($room);
  $taken=$room->players()->pluck('seat')->all();
  $seat=collect(array_slice($this->seatOrder,0,$room->max_players))->first(fn($s)=>!in_array($s,$taken,true));
  $room->players()->create(['bot_key'=>$bot['name'],'seat'=>$seat,'is_bot'=>true,'connected'=>true]);
  return back()->with('ok','تمت إضافة بوت للغرفة');
 }
 public function start(Room $room){
  try{
   abort_unless($room->owner_id===auth()->id() || auth()->user()->is_admin,403,'فقط صاحب الغرفة أو الإدارة يمكنه بدء اللعبة.');
   $room->load('game','players.user.profile');
   if($room->players()->where('is_bot',false)->count() < 1) return $this->friendlyGameStartFail('يجب وجود لاعب حقيقي واحد على الأقل.');
   $this->fillBots($room);
   $room->refresh()->load('game','players.user.profile');
   if($room->players()->count() != $room->max_players) return $this->friendlyGameStartFail('تعذر اكتمال المقاعد تلقائيًا. أعد المحاولة.');
   $players=$room->players->sortBy(fn($p)=>array_search($p->seat,$this->seatOrder,true))->map(fn($p)=>$this->playerKey($p))->values()->all();
   $engine=GameFactory::make($room->game->key);
   $oldState=$room->state ?: [];
   $engineOptions=['target'=>$room->target_score ?: ($room->game->rules['targets'][0] ?? 31),'partners'=>(bool)$room->game->partnership,'deal_nonce'=>bin2hex(random_bytes(6))];
   if(in_array($room->game->key,['hand','hand_partner','pinochle','banakil','konkan'],true) && !empty($oldState['scores'])){ $engineOptions['previous_scores']=$oldState['scores']; $engineOptions['round']=min(((int)($oldState['round'] ?? 1))+1, 5); }
   $state=$engine->initialState($players,$engineOptions);
   if(!empty($oldState['score']) && empty($oldState['winner_team'])) { $state['score']=$oldState['score']; $state['round']=(int)($oldState['round'] ?? 1)+1; }
   $state['room_code'] = $room->code;
   $state['game'] = $room->game->key;
   $state['seat_partners'] = $this->partnerMap($room->max_players);
   $state['play_direction'] = 'counterclockwise';
   $state['next_player_side'] = 'right';
   if(!empty($oldState['tournament_id'])){ $state['tournament_id']=$oldState['tournament_id']; $state['tournament_stage']=$oldState['tournament_stage'] ?? null; $state['recording_enabled']=true; $state['video_frames']=$oldState['video_frames'] ?? []; }
   [$fixedSpeed,$fixedTimeout]=$this->normalizeSpeed($oldState['speed'] ?? ($room->state['speed'] ?? 'medium'));
   $state['speed']=$fixedSpeed;
   $state['turn_timeout_seconds']=$fixedTimeout;
   $state['log'][]=['system'=>(($state['round'] ?? 1) <= 1 ? 'تم ملء المقاعد الفارغة وتوزيع ورق الجولة الأولى' : 'انتقال تلقائي إلى الجولة التالية واحتساب النقاط'),'at'=>now()->toIso8601String()];
   $state['messages'][]=(($state['round'] ?? 1) <= 1 ? 'تم بدء الجولة الأولى وتوزيع الورق.' : 'تم الانتقال تلقائيًا إلى الجولة التالية بعد احتساب النقاط.');
   $state=$this->autoBots($room,$state);
   $room->update(['status'=>($state['phase']==='bidding'?'bidding':'playing'),'started_at'=>now(),'state'=>$state]);
   $fresh=$room->fresh()->load('players.user.profile');
   if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'message'=>'تم ملء المقاعد الفارغة، توزيع الورق وبدأت الجولة','state'=>$this->publicState($state,'user:'.auth()->id()),'seats'=>$this->seatPayload($fresh)]);
   return back()->with('ok','تم ملء المقاعد الفارغة، توزيع الورق وبدأت الجولة');
  }catch(\Throwable $e){ Log::error('Warqna room start failed',['room'=>$room->code,'error'=>$e->getMessage()]); return $this->friendlyGameStartFail('تعذر بدء اللعبة: '.$this->safeError($e->getMessage())); }
 }
 private function friendlyGameStartFail(string $message){ if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>false,'message'=>$message],200); return back()->withErrors(['msg'=>$message]); }





 private function botCatalog(): array
 {
  return [
   ['name'=>'معتصم 3D','avatar'=>'/assets/bots/3d/bot-01.svg'],
   ['name'=>'يمان 3D','avatar'=>'/assets/bots/3d/bot-02.svg'],
   ['name'=>'عدنان 3D','avatar'=>'/assets/bots/3d/bot-03.svg'],
   ['name'=>'عاصم 3D','avatar'=>'/assets/bots/3d/bot-04.svg'],
   ['name'=>'كنان 3D','avatar'=>'/assets/bots/3d/bot-05.svg'],
   ['name'=>'جميل 3D','avatar'=>'/assets/bots/3d/bot-06.svg'],
   ['name'=>'همام 3D','avatar'=>'/assets/bots/3d/bot-07.svg'],
   ['name'=>'معاذ 3D','avatar'=>'/assets/bots/3d/bot-08.svg'],
   ['name'=>'مصطفى 3D','avatar'=>'/assets/bots/3d/bot-09.svg'],
   ['name'=>'مهند 3D','avatar'=>'/assets/bots/3d/bot-10.svg'],
  ];
 }

 private function pickBotIdentity(Room $room, ?int $seed=null): array
 {
  $catalog=$this->botCatalog();
  $used=$room->players()->where('is_bot',true)->pluck('bot_key')->map(fn($n)=>trim(preg_replace('/\s*BOT\s*$/iu','',(string)$n)))->filter()->values()->all();
  $available=array_values(array_filter($catalog, fn($b)=>!in_array($b['name'],$used,true)));
  $pool=!empty($available)?$available:$catalog;
  if($seed!==null) return $pool[$seed % max(count($pool),1)];
  return $pool[array_rand($pool)];
 }

 private function botAvatarFromName(?string $name, ?int $seed=null): string
 {
  $clean=trim(preg_replace('/\s*BOT\s*$/iu','',(string)$name));
  foreach($this->botCatalog() as $i=>$bot) if($bot['name']===$clean) return $bot['avatar'];
  $i=(int)($seed ?? 0);
  $catalog=$this->botCatalog();
  return $catalog[$i % max(count($catalog),1)]['avatar'] ?? '/assets/bots/bot01.svg';
 }

 private function allowedSeatCounts(Game $game): array
 {
  return match($game->key){
   'tarneeb','tarneeb_400','tarneeb_41','tarneeb_61','syrian_tarneeb','trix','trix_partner','trix_complex','trix_kingdoms','baloot','hokm','kout4','basra','spades','ludo','jackaroo' => [4],
   'kout6' => [6],
   'pinochle','banakil' => [2,4],
   'hand','hand_partner','saudi_hand','hand_saudi','rummy','konkan','domino' => [2,3,4],
   'backgammon','chess' => [2],
   'estimation','leekha','hearts' => [4],
   default => array_values(array_unique(range((int)$game->min_players, min((int)$game->max_players,6))))
  };
 }

 private function fillBots(Room $room): void
 {
  $allowed=array_slice($this->seatOrder,0,$room->max_players);
  while($room->players()->count() < $room->max_players){
   $taken=$room->players()->pluck('seat')->all();
   $seat=collect($allowed)->first(fn($s)=>!in_array($s,$taken,true));
   if(!$seat) break;
   $bot=$this->pickBotIdentity($room,(int)$room->players()->count());
   $room->players()->create(['bot_key'=>$bot['name'],'seat'=>$seat,'is_bot'=>true,'connected'=>true]);
  }
 }

 private function partnerMap(int $maxPlayers): array
 {
  if($maxPlayers===4) return ['south'=>'north','north'=>'south','west'=>'east','east'=>'west'];
  if($maxPlayers===6) return ['south'=>'north','north'=>'south','west'=>'south_east','south_east'=>'west','east'=>'south_west','south_west'=>'east'];
  return [];
 }

 private function bestActiveCosmetic(Room $room, string $category, string $payloadKey): ?string
 {
  $userIds=$room->players()->where('is_bot',false)->whereNotNull('user_id')->pluck('user_id')->all();
  if(empty($userIds)) return null;
  $best=\App\Models\InventoryItem::with('storeItem')
   ->whereIn('user_id',$userIds)->where('active',true)
   ->whereHas('storeItem',fn($q)=>$q->where('category',$category))
   ->get()->filter(fn($inv)=>$inv->storeItem)
   ->sortByDesc(fn($inv)=>$inv->storeItem->price ?? 0)->first();
  $payload=$best?->storeItem?->payload ?: [];
  return $payload[$payloadKey] ?? null;
 }

 public function action(Room $room, Request $r, AntiCheatService $antiCheat, PlayActionNormalizer $normalizer){
  try{
   $data=$r->validate(['action'=>'required|string|max:40','payload'=>'nullable|array']);
   if(!$room->players()->where('user_id',auth()->id())->exists()) return response()->json(['ok'=>false,'valid'=>false,'message'=>'أنت لست داخل هذه الغرفة.'],403);
   $room->load('game','players'); $state=$room->state ?: [];
   $playerKey='user:'.auth()->id(); $engine=GameFactory::make($room->game->key);
   if(isset($state['turn']) && str_starts_with((string)$state['turn'],'bot:')){
    $state=$this->autoBots($room,$state);
    $room->update(['state'=>$state,'status'=>($state['phase']??'playing')==='finished'?'finished':(($state['phase']??'playing')==='bidding'?'bidding':'playing')]);
    if(($state['turn'] ?? null)!==$playerKey) return response()->json(['ok'=>true,'valid'=>true,'message'=>'تم تحديث الدور بعد حركة البوت.','state'=>$this->publicState($state,$playerKey),'seats'=>$this->seatPayload($room)]);
   }
   $action=$data['action']; $payload=$data['payload']??[];
   [$action,$payload]=$normalizer->normalize($action,$payload,$state,$playerKey);
   $tooFast=$antiCheat->tooFast($room,auth()->id(),0);
   $engineValid=$engine->validate($state,$playerKey,$action,$payload);
   // v126: لا نرفض الورقة الصحيحة لمجرد أن الضغط كان سريعًا؛ نسجل السرعة فقط ونترك محرك اللعبة يحكم قانونية الحركة.
   if($tooFast){ $antiCheat->log($room,auth()->id(),'fast_action_warning',1,['action'=>$action,'payload'=>$payload,'phase'=>$state['phase']??null]); }
   $valid=$engineValid;
   DB::table('game_actions')->insert(['room_id'=>$room->id,'user_id'=>auth()->id(),'action'=>$action,'payload'=>json_encode($payload,JSON_UNESCAPED_UNICODE),'valid'=>$valid,'ip'=>$r->ip(),'created_at'=>now(),'updated_at'=>now()]);
   if(!$valid){
    $diag=$normalizer->diagnostic($state,$playerKey,$action,$payload);
    $msg=$this->actionErrorMessage($state,$playerKey,$action,$payload,$diag);
    $antiCheat->log($room,auth()->id(),'invalid_game_action',3,['action'=>$action,'payload'=>$payload,'turn'=>$state['turn']??null,'phase'=>$state['phase']??null,'diagnostic'=>$diag]);
    return response()->json(['ok'=>false,'valid'=>false,'message'=>$msg,'diagnostic'=>$diag,'state'=>$this->publicState($state,$playerKey)]);
   }
   $state=$engine->apply($state,$playerKey,$action,$payload);
   $state['last_action']=['player'=>$playerKey,'action'=>$action,'payload'=>$payload,'at'=>now()->toIso8601String(),'valid'=>true];
   $state['log']=array_slice(array_merge($state['log']??[],[$state['last_action']]),-200);
   $state=$this->appendReplayFrame($room,$state,$playerKey,$action,$payload);
   $state=$this->autoBots($room,$state);
   $state=$this->awardProfilePointsIfFinished($room,$state);
   $state=$this->autoAdvanceNextRound($room,$state);
   $room->update(['status'=>($state['phase']??'playing')==='finished'?'finished':(($state['phase']??'playing')==='bidding'?'bidding':'playing'),'finished_at'=>($state['phase']??'')==='finished'?now():$room->finished_at,'state'=>$state]);
   return response()->json(['ok'=>true,'valid'=>true,'state'=>$this->publicState($state,$playerKey),'seats'=>$this->seatPayload($room)]);
  }catch(\Throwable $e){
   return response()->json(['ok'=>false,'valid'=>false,'message'=>'حدث خطأ بسيط أثناء تنفيذ الحركة، لم تتوقف اللعبة. جرّب تحديث الدور أو حركة أخرى.'],200);
  }
 }

 private function actionErrorMessage(array $state,string $playerKey,string $action,array $payload,array $diag=[]): string
 {
  if(!empty($state['last_error_message'])) return (string)$state['last_error_message'];
  $phase=$state['phase'] ?? 'waiting';
  if(($state['turn'] ?? null)!==$playerKey) return 'بانتظار دور لاعب آخر. اضغط تحديث الدور إذا كان الدور عالقًا.';
  if($phase==='bidding'){
   if($action==='bid') return 'الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.';
   if($action==='pass') return 'لا يمكنك تمرير الدور أكثر من مرة في نفس مرحلة الطلب.';
  }
  if($phase==='choose_trump') return 'اختر نوع الطرنيب أولًا من الأزرار الظاهرة.';
  if($phase==='playing'){
   if(($diag['type'] ?? '')==='not_in_hand') return 'هذه الورقة غير موجودة في يدك الحالية. اضغط تحديث الدور؛ قد تكون الشاشة قديمة أو تم لعب الورقة سابقًا.';
   if(($diag['type'] ?? '')==='must_follow_suit') return 'يجب اتباع نوع أول ورقة في اللفة. لديك ورقة من نفس النوع المطلوب، لذلك اختر من الأوراق المظللة فقط.';
   if(($diag['type'] ?? '')==='looks_legal') return 'الورقة تبدو صحيحة لكن الحالة قديمة أو الدور تغيّر. اضغط تحديث الدور ثم جرّب مرة واحدة.';
   return 'حركة غير صحيحة: تأكد أن الدور عليك وأن الورقة موجودة في يدك.';
  }
  return 'الحركة غير متاحة في هذه المرحلة.';
 }

 private function autoBots(Room $room, array $state): array {
  $engine=GameFactory::make($room->game->key); $guard=0;
  while(isset($state['turn']) && (str_starts_with((string)$state['turn'],'bot:') || $this->playerIsAway($state,(string)$state['turn'])) && $guard++<30 && !in_array($state['phase']??'', ['finished','waiting'], true)){
   $bot=$state['turn'];
   [$action,$payload]=$this->smartBotMove($engine,$state,$bot);
   if(!$action || !$engine->validate($state,$bot,$action,$payload)) break;
   $state=$engine->apply($state,$bot,$action,$payload);
   $state['last_action']=['player'=>$bot,'action'=>$action,'payload'=>$payload,'at'=>now()->toIso8601String(),'valid'=>true,'auto'=>true];
   $state['log']=array_slice(array_merge($state['log']??[],[$state['last_action']]),-200);
   $state=$this->appendReplayFrame($room,$state,$bot,$action,$payload,true);
  }
  return $state;
 }


 private function appendReplayFrame(Room $room, array $state, string $playerKey, string $action, array $payload, bool $auto=false): array
 {
  if(empty($state['tournament_id']) && empty($state['recording_enabled'])) return $state;
  $frame=[
   't'=>now()->toIso8601String(),
   'player'=>$playerKey,
   'action'=>$action,
   'payload'=>$payload,
   'auto'=>$auto,
   'phase'=>$state['phase'] ?? '',
   'trick'=>$state['trick'] ?? [],
   'scores'=>$state['score'] ?? ($state['scores'] ?? []),
   'message'=>($auto?'بوت: ':'لاعب: ').$action,
  ];
  $state['video_frames']=array_slice(array_merge($state['video_frames'] ?? [],[$frame]),-600);
  $state['recording_enabled']=true;
  return $state;
 }

 private function smartBotMove($engine,array $state,string $playerKey): array{
  $phase=$state['phase']??''; $gt=$state['game_type']??'';
  if($phase==='bidding'){
   if($gt==='estimation') return ['bid',['value'=>min(13,max(0,(int)floor(count($state['hands'][$playerKey]??[])/4)))]];
   $current=(int)($state['bid']['value'] ?? 6); $hand=$state['hands'][$playerKey]??[];
   $strength=0; foreach($hand as $c){ if(str_starts_with($c,'A_')||str_starts_with($c,'K_')) $strength++; }
   $try=$current+1; if($try<=13 && $strength>=3 && random_int(1,100)>45) return ['bid',['value'=>$try]];
   return ['pass',[]];
  }
  if($phase==='choose_trump'){
   $counts=['clubs'=>0,'diamonds'=>0,'spades'=>0,'hearts'=>0]; foreach(($state['hands'][$playerKey]??[]) as $c){$s=explode('_',$c)[1]??''; if(isset($counts[$s]))$counts[$s]++;}
   arsort($counts); return ['choose_trump',['suit'=>array_key_first($counts) ?: 'clubs']];
  }
  if($phase==='choose_contract'){ $contracts=$state['available_contracts']??['tricks']; return ['choose_contract',['contract'=>$contracts[array_rand($contracts)]]]; }
  if($phase==='baloot_bid') return random_int(1,100)>65?['choose_sun',[]]:['pass',[]];
  if($phase==='playing'){
   if(in_array($gt,['hand','banakil','konkan'],true)){
    if(empty($state['drew_this_turn'][$playerKey])) return !empty($state['discard']) && random_int(1,100)>55 ? ['draw_discard',[]] : ['draw_deck',[]];
    $meld=$this->firstLegalMeld($engine,$state,$playerKey); if($meld) return ['meld',['cards'=>$meld]];
    $hand=$state['hands'][$playerKey]??[]; if(!$hand) return [null,[]];
    usort($hand,fn($a,$b)=>$this->simpleCardPoint($a)<=>$this->simpleCardPoint($b)); return ['discard',['card'=>$hand[0]]];
   }
   if($gt==='domino'){
    foreach(($state['hands'][$playerKey]??[]) as $tile) foreach(['right','left'] as $side){$payload=['tile'=>$tile,'side'=>$side]; if($engine->validate($state,$playerKey,'play_tile',$payload)) return ['play_tile',$payload];}
    return !empty($state['boneyard'])?['draw',[]]:['pass',[]];
   }
   if($gt==='backgammon') return empty($state['moves_left'])?['roll',[]]:['pass',[]];
   $card=$this->bestLegalCard($engine,$state,$playerKey); return $card?['play_card',['card'=>$card]]:[null,[]];
  }
  return [null,[]];
 }

 private function bestLegalCard($engine,array $state,string $playerKey): ?string{
  $hand=$state['hands'][$playerKey]??[]; if(!$hand) return null;
  $legal=[]; foreach($hand as $c) if($engine->validate($state,$playerKey,'play_card',['card'=>$c])) $legal[]=$c;
  if(empty($legal)) return null;
  usort($legal,fn($a,$b)=>$this->simpleCardPoint($a)<=>$this->simpleCardPoint($b));
  if(empty($state['trick'])) return end($legal) ?: $legal[0];
  return $legal[0];
 }

 private function firstLegalMeld($engine,array $state,string $playerKey): ?array{
  $hand=array_values($state['hands'][$playerKey]??[]); $n=count($hand); if($n<3) return null;
  for($i=0;$i<$n;$i++) for($j=$i+1;$j<$n;$j++) for($k=$j+1;$k<$n;$k++){ $cards=[$hand[$i],$hand[$j],$hand[$k]]; if($engine->validate($state,$playerKey,'meld',['cards'=>$cards])) return $cards; }
  return null;
 }

 private function simpleCardPoint(string $card): int{
  $rank=explode('_',$card)[0]??''; return ['JOKER'=>25,'2'=>20,'A'=>15,'K'=>10,'Q'=>10,'J'=>10,'10'=>10,'9'=>5,'8'=>5,'7'=>5,'6'=>5,'5'=>5,'4'=>5,'3'=>5][$rank] ?? 1;
 }

 private function seatPayload(Room $room): array
 {
  $room->loadMissing('players.user.profile');
  return $room->players->map(function($p){
   $profile=$p->user?->profile; $code=safe_country_code($profile?->country_code ?? 'PS');
   $botNo=str_pad((string)((($p->id ?? 1) % 8)+1),2,'0',STR_PAD_LEFT);
   return [
    'id'=>$p->id,'user_id'=>$p->user_id,'key'=>$this->playerKey($p),'seat'=>$p->seat,'is_bot'=>(bool)$p->is_bot,
    'name'=>$p->user?->username ?: $p->bot_key,
    'avatar'=>$p->is_bot ? $this->botAvatarFromName($p->bot_key, (int)($p->id ?? 1)) : ($profile?->avatar ?: '/assets/avatars/default.svg'),
    'color'=>$profile?->name_color ?: ($p->is_bot ? '#38bdf8' : '#facc15'),
    'flag_url'=>$p->is_bot ? null : flag_url($code),
    'country'=>$p->is_bot ? 'BOT' : country_name($code),
    'frame'=>$profile?->active_name_frame ?: ($p->is_bot ? 'glow-ocean' : 'glow-gold'),
    'connected'=>(bool)$p->connected,
   ];
  })->values()->all();
 }

 private function playerKey(RoomPlayer $p): string { return $p->is_bot ? 'bot:'.$p->id : 'user:'.$p->user_id; }
 private function publicState(array $state, string $myKey): array {
  [$fixedSpeed,$fixedTimeout]=$this->normalizeSpeed($state['speed'] ?? 'medium');
  $state['speed']=$fixedSpeed;
  $state['turn_timeout_seconds']=$fixedTimeout;
  $copy=$state; $copy['hand']=$state['hands'][$myKey] ?? []; if(!empty($state['score_popups'][$myKey])) $copy['score_popup']=$state['score_popups'][$myKey];
  if(isset($copy['deck']) && is_array($copy['deck'])) $copy['deck_count']=count($copy['deck']);
  if(isset($copy['boneyard']) && is_array($copy['boneyard'])) $copy['boneyard_count']=count($copy['boneyard']);
  unset(
   $copy['_global_engine'], $copy['_tarneeb_v2'], $copy['deck'], $copy['boneyard'], $copy['seed'],
   $copy['plain_room_password'], $copy['kicked_user_ids'], $copy['banned_user_ids'], $copy['disconnected_replacements']
  );
  $copy['legal_cards']=[];
  if(($state['phase'] ?? '')==='playing' && !empty($copy['hand'])){
   try{ $engine=\App\Services\GameEngine\GameFactory::make($state['game'] ?? ($state['game_type'] ?? 'tarneeb')); foreach($copy['hand'] as $c){ if($engine->validate($state,$myKey,'play_card',['card'=>$c]) || in_array(($state['game_type']??''),['hand','banakil','konkan'],true)) $copy['legal_cards'][]=$c; } }catch(\Throwable $e){ $copy['legal_cards']=$copy['hand']; }
  }
  if(isset($copy['hands']) && !(($copy['phase'] ?? '')==='finished' && !empty($copy['tournament_id']))) foreach($copy['hands'] as $k=>$h) if($k!==$myKey) $copy['hands'][$k]=array_fill(0,count($h),'back');
  return $copy;
 }

 public function leave(Room $room){
  $player=$room->players()->where('user_id',auth()->id())->first();
  abort_unless($player,404,'أنت لست داخل هذه الغرفة');
  $state=$room->state ?: [];
  $uid=auth()->id(); $oldKey='user:'.$uid;
  $counts=(array)($state['manual_exit_counts'] ?? $state['manual_leave_counts'] ?? []);
  $counts[$uid]=(int)($counts[$uid] ?? 0)+1;
  $state['manual_exit_counts']=$counts;
  unset($state['manual_leave_counts']);
  if(!empty($state['leave_xp_penalty']) && auth()->user()->profile){ auth()->user()->profile->xp=max(0,(int)auth()->user()->profile->xp-200); auth()->user()->profile->save(); $state['messages'][]='تم خصم 200 XP بسبب الخروج اليدوي من اللعبة حسب إعدادات الغرفة.'; }
  if($counts[$uid] >= 3){ $banned=$state['banned_user_ids'] ?? []; $banned[]=(int)$uid; $state['banned_user_ids']=array_values(array_unique($banned)); }
  $oldName=auth()->user()->username; $playerId=$player->id; $newKey='bot:'.$playerId;
  $bot=$this->pickBotIdentity($room,$playerId);
  $player->update(['user_id'=>null,'is_bot'=>true,'bot_key'=>$bot['name'],'connected'=>true,'missed_turns'=>0]);
  $returns=(int)(($state['disconnected_replacements'][$uid]['returns'] ?? 0));
  $state['disconnected_replacements'][$uid]=['room_player_id'=>$playerId,'seat'=>$player->seat,'returns'=>$returns];
  if(isset($state['hands'][$oldKey])){ $state['hands'][$newKey]=$state['hands'][$oldKey]; unset($state['hands'][$oldKey]); }
  if(($state['turn'] ?? null)===$oldKey) $state['turn']=$newKey;
  if(!empty($state['players'])) $state['players']=array_values(array_map(fn($p)=>$p===$oldKey?$newKey:$p,$state['players']));
  $state['messages'][]=$oldName.' غادر الغرفة. الكمبيوتر سيكمل بدله إذا بقي لاعبون حقيقيون. عدد الخروج اليدوي: '.$counts[$uid].'/3.';
  if($counts[$uid] >= 3) $state['messages'][]='تم منع '.$oldName.' من العودة لهذه الغرفة بعد 3 مرات خروج.';
  if($counts[$uid] >= 3 && $this->closeIfNoRealPlayers($room,$state,'تم إغلاق الغرفة وإخفاؤها بعد استنفاد آخر لاعب حقيقي فرص العودة.')){
   $url = route('rooms.index',$room->game?->key ?? 'tarneeb');
   if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'redirect'=>$url,'left'=>true,'closed'=>true,'message'=>'خرجت من اللعبة وتم إغلاق الغرفة لأن كل اللاعبين الحقيقيين خرجوا.']);
   return redirect()->route('rooms.index',$room->game?->key ?? 'tarneeb')->with('ok','خرجت من اللعبة وتم إغلاق الغرفة لأن كل اللاعبين الحقيقيين خرجوا.');
  }
  $state=$this->autoBots($room,$state);
  $room->update(['state'=>$state,'status'=>($state['phase']??'playing')==='finished'?'finished':(($state['phase']??'playing')==='bidding'?'bidding':'playing')]);
  $url = route('rooms.index',$room->game?->key ?? 'tarneeb');
  $msg = $counts[$uid]>=3?'خرجت من الغرفة وتم منع العودة بعد 3 مرات خروج.':'تمت مغادرة الغرفة، والبوت سيكمل مكانك إذا بقي لاعبون حقيقيون.';
  if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>true,'redirect'=>$url,'left'=>true,'message'=>$msg]);
  return redirect()->route('rooms.index',$room->game?->key ?? 'tarneeb')->with('ok',$msg);
 }

 public function timeoutAutoPlay(Room $room){
  $room->load('game','players'); $state=$room->state ?: []; $playerKey='user:'.auth()->id();
  if(($state['turn'] ?? null) !== $playerKey) return response()->json(['ok'=>true,'skipped'=>true,'state'=>$this->publicState($state,$playerKey)]);
  $rp=$room->players()->where('user_id',auth()->id())->first(); abort_unless($rp,403);
  $rp->increment('missed_turns');
  $engine=GameFactory::make($room->game->key);
  [$action,$payload]=$this->smartBotMove($engine,$state,$playerKey);
  if($action && $engine->validate($state,$playerKey,$action,$payload)){
   $state=$engine->apply($state,$playerKey,$action,$payload);
   $state['messages'][]='⏱️ تم لعب حركة تلقائية بدل '.$rp->user?->username.' بعد انتهاء عداد الدور.';
  }
  if($rp->fresh()->missed_turns>=3){
   $uid=(int)auth()->id(); $returns=(int)(($state['disconnected_replacements'][$uid]['returns'] ?? 0));
   $oldKey='user:'.$uid; $newKey='bot:'.$rp->id; $name=$rp->user?->username ?: 'لاعب';
   $bot=$this->pickBotIdentity($room,$rp->id);
   $rp->update(['user_id'=>null,'is_bot'=>true,'bot_key'=>$bot['name'],'connected'=>true,'missed_turns'=>0]);
   if(isset($state['hands'][$oldKey])){ $state['hands'][$newKey]=$state['hands'][$oldKey]; unset($state['hands'][$oldKey]); }
   if(($state['turn'] ?? null)===$oldKey) $state['turn']=$newKey;
   if(!empty($state['players'])) $state['players']=array_values(array_map(fn($p)=>$p===$oldKey?$newKey:$p,$state['players']));
   $state['disconnected_replacements'][$uid]=['room_player_id'=>$rp->id,'seat'=>$rp->seat,'returns'=>$returns];
   $state['messages'][]='🚪 '.$name.' غاب 3 لفات، البوت يكمل في نفس المقعد ويمكنه العودة لاحقًا ما لم يتجاوز 3 مرات.';
  }
  $state=$this->autoBots($room,$state); $state=$this->awardProfilePointsIfFinished($room,$state); $state=$this->autoAdvanceNextRound($room,$state); $room->update(['state'=>$state,'status'=>($state['phase']??'playing')==='finished'?'finished':(($state['phase']??'playing')==='bidding'?'bidding':'playing')]);
  return response()->json(['ok'=>true,'state'=>$this->publicState($state,$playerKey)]);
 }

 private function friendlyInvalidMessage(array $state,string $action,array $payload,string $playerKey): string{
  if(($state['turn'] ?? null)!==$playerKey) return 'بانتظار دور لاعب آخر. انتظر حتى يصبح الدور عليك أو اضغط تحديث الدور.';
  $phase=$state['phase'] ?? '';
  if($phase==='bidding' && $action==='bid') return 'الطلب غير صحيح. يجب أن يكون بين 7 و 13 وأعلى من الطلب الحالي.';
  if($phase==='bidding' && $action==='pass') return 'لا يمكن تمرير الدور أكثر من مرة في نفس الطلب.';
  if($phase==='choose_trump') return 'اختر نوع الطرنيب أولًا: سنك، ديناري، بستوني، أو كبة.';
  if($phase==='playing') return 'الحركة غير مقبولة: اختر ورقة من يدك، وإذا كان لديك نفس نوع أول ورقة في اللفة يجب أن تلعب من نفس النوع.';
  return 'لا يمكن عمل هذه الخطوة الآن.';
 }

 private function fallbackMove($engine,array $state,string $playerKey): array{
  $gt=$state['game_type']??''; $phase=$state['phase']??'';
  if($phase==='bidding') return ['pass',[]];
  if($phase==='choose_trump') return ['choose_trump',['suit'=>'clubs']];
  if($phase==='choose_contract') return ['choose_contract',['contract'=>($state['available_contracts'][0] ?? 'tricks')]];
  if($phase==='baloot_bid') return ['pass',[]];
  if($phase==='playing'){
   if($gt==='domino') { foreach(($state['hands'][$playerKey]??[]) as $tile){ foreach(['right','left'] as $side){$p=['tile'=>$tile,'side'=>$side]; if($engine->validate($state,$playerKey,'play_tile',$p)) return ['play_tile',$p];}} return !empty($state['boneyard'])?['draw',[]]:['pass',[]]; }
   if($gt==='backgammon') return empty($state['moves_left'])?['roll',[]]:['pass',[]];
   if(in_array($gt,['hand','banakil','konkan'],true)) return ['discard',['card'=>$state['hands'][$playerKey][0]??null]];
   foreach(($state['hands'][$playerKey]??[]) as $candidate){
    $try=['card'=>$candidate];
    if($engine->validate($state,$playerKey,'play_card',$try)) return ['play_card',$try];
   }
   return ['pass',[]];
  }
  return [null,[]];
 }


 public function sync(Room $room){
  abort_unless($room->players()->where('user_id',auth()->id())->exists(),403);
  $room->load('game','players.user.profile');
  $state=$room->state ?: [];
  $engine=GameFactory::make($room->game->key);
  $changed=false;
  if(isset($state['turn']) && str_starts_with((string)$state['turn'],'bot:')){
   $state=$this->autoBots($room,$state);
   $state=$this->awardProfilePointsIfFinished($room,$state);
   $state=$this->autoAdvanceNextRound($room,$state);
   $changed=true;
  }
  if(isset($state['turn']) && str_starts_with((string)$state['turn'],'user:')){
   $uid=(int)str_replace('user:','',(string)$state['turn']);
   $rp=$room->players->firstWhere('user_id',$uid);
   if($rp && (!$rp->connected || $this->playerIsAway($state,'user:'.$uid))){
    [$action,$payload]=$this->smartBotMove($engine,$state,'user:'.$uid);
    if($action && $engine->validate($state,'user:'.$uid,$action,$payload)){
     $state=$engine->apply($state,'user:'.$uid,$action,$payload);
     $state['messages'][]='🤖 تم لعب حركة تلقائية بدل لاعب منقطع حتى لا تتوقف الطاولة.';
     $state=$this->autoBots($room,$state);
     $state=$this->awardProfilePointsIfFinished($room,$state);
     $state=$this->autoAdvanceNextRound($room,$state);
     $changed=true;
    }
   }
  }
  if($changed){$room->update(['state'=>$state,'status'=>($state['phase']??'playing')==='finished'?'finished':(($state['phase']??'playing')==='bidding'?'bidding':'playing')]);}
  return response()->json(['ok'=>true,'state'=>$this->publicState($state,'user:'.auth()->id()),'seats'=>$this->seatPayload($room),'room_messages'=>$this->recentRoomMessages($room)]);
 }

 public function roomChat(Room $room, Request $r){
  if(!$room->players()->where('user_id',auth()->id())->exists()) return response()->json(['ok'=>false,'message'=>'أنت لست داخل هذه الغرفة.'],200);
  $data=$r->validate(['body'=>'required|string|max:500']);
  $body=$this->cleanChat((string)$data['body']);
  if($body==='') return response()->json(['ok'=>false,'message'=>'لا يمكن إرسال رسالة فارغة أو مخالفة لقواعد الدردشة.'],200);
  $msg=\App\Models\Message::create(['sender_id'=>auth()->id(),'room_id'=>$room->id,'body'=>$body]);
  return response()->json(['ok'=>true,'message'=>['id'=>$msg->id,'sender_id'=>auth()->id(),'mine'=>true,'name'=>auth()->user()->username,'avatar'=>auth()->user()->profile?->avatar ?: '/assets/avatars/default.svg','body'=>$msg->body,'color'=>auth()->user()->profile?->chat_color ?: (auth()->user()->profile?->text_color ?: '#fff'),'time'=>$msg->created_at?->format('H:i')]]);
 }


 private function cleanChat(string $body): string{
  $bad=['كلب','حمار','حقير','قذر','تافه','لعنة','غبي','وسخ','اهبل','زبالة','خرا','شرموط','قحبة','كس','نيك','يلعن','fuck','shit','bitch','asshole'];
  $body=trim(strip_tags($body));
  foreach($bad as $w) $body=preg_replace('/'.preg_quote($w,'/').'/iu','***',$body);
  return mb_substr($body,0,500);
 }

 public function replacePlayerWithBot(Room $room, RoomPlayer $player){
  abort_unless($room->owner_id===auth()->id() || auth()->user()->is_admin,403);
  abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0 || auth()->user()->is_admin,403,'هذه الميزة متاحة للباشا أو الإدارة فقط.');
  abort_unless(!empty(($room->state ?: [])['allow_owner_kick']) || auth()->user()->is_admin,403,'الطرد غير مفعّل في إعدادات إنشاء هذه الغرفة.');
  abort_unless($player->room_id===$room->id,404);
  abort_if($player->is_bot,422,'هذا المقعد بوت بالفعل.');
  abort_if($player->user_id===$room->owner_id && !auth()->user()->is_admin,422,'لا يمكن إخراج صاحب الغرفة.');
  $oldName=$player->user?->username ?: 'لاعب';
  $oldUserId=$player->user_id; $oldKey='user:'.$oldUserId;
  $bot=$this->pickBotIdentity($room,$player->id);
  $player->update(['user_id'=>null,'is_bot'=>true,'bot_key'=>$bot['name'],'connected'=>true,'missed_turns'=>0]);
  $newKey='bot:'.$player->id;
  $state=$room->state ?: [];
  $banned=$state['banned_user_ids'] ?? []; if($oldUserId) $banned[]=(int)$oldUserId; $state['banned_user_ids']=array_values(array_unique($banned));
  if(isset($state['hands'][$oldKey])){ $state['hands'][$newKey]=$state['hands'][$oldKey]; unset($state['hands'][$oldKey]); }
  if(($state['turn'] ?? null)===$oldKey) $state['turn']=$newKey;
  if(!empty($state['players'])) $state['players']=array_values(array_map(fn($p)=>$p===$oldKey?$newKey:$p,$state['players']));
  $state['messages'][]='👑 تم استبدال '.$oldName.' ببوت بواسطة صاحب الغرفة الباشا. لن يستطيع العودة إلى نفس الغرفة.'; $room->update(['state'=>$state]);
  if(request()->expectsJson()) return response()->json(['ok'=>true,'message'=>'تم استبدال اللاعب ببوت.']);
  return back()->with('ok','تم استبدال اللاعب ببوت.');
 }

 public function toggleAway(Room $room){
  $room->load('players');
  $rp=$room->players()->where('user_id',auth()->id())->first();
  abort_unless($rp,403,'أنت لست داخل هذه الغرفة');
  abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0,403,'وضع الغائب متاح لأعضاء الباشا فقط');
  $state=$room->state ?: [];
  $away=$state['away_players'] ?? [];
  $key='user:'.auth()->id();
  if(!empty($away[$key])){ unset($away[$key]); $state['messages'][]=auth()->user()->username.' عاد من وضع الغائب.'; }
  else { $away[$key]=true; $state['messages'][]=auth()->user()->username.' فعّل وضع الغائب، الكمبيوتر سيلعب بدله مؤقتًا.'; }
  $state['away_players']=$away;
  $state=$this->autoBots($room,$state);
  $room->update(['state'=>$state]);
  return back()->with('ok',!empty($away[$key])?'تم تفعيل وضع الغائب':'تم إلغاء وضع الغائب');
 }

 private function playerIsAway(array $state,string $playerKey): bool { return !empty(($state['away_players'] ?? [])[$playerKey]); }


 private function seatsPayload(Room $room): array{
  return $room->players->map(function($p){$profile=$p->user?->profile; $code=safe_country_code($profile?->country_code ?? 'PS'); return ['id'=>$p->id,'key'=>$p->is_bot?'bot:'.$p->id:'user:'.$p->user_id,'user_id'=>$p->user_id,'bot'=>$p->is_bot,'seat'=>$p->seat,'name'=>$p->user?->username ?: $p->bot_key,'avatar'=>$p->is_bot?$this->botAvatarFromName($p->bot_key, (int)($p->id ?? 1)):($profile?->avatar ?: '/assets/avatars/default.svg'),'color'=>$profile?->name_color ?: ($p->is_bot?'#38bdf8':'#facc15'),'flag_url'=>flag_url($code),'country'=>country_name($code),'connected'=>$p->connected];})->values()->all();
 }
 private function friendlyGameError(array $state,string $action,array $payload): string{
  $phase=$state['phase']??''; $turn=$state['turn']??null; $mine='user:'.auth()->id();
  if($turn && $turn!==$mine) return str_starts_with((string)$turn,'bot:') ? 'الدور عند بوت الآن، اضغط تحديث الدور أو انتظر لحظة.' : 'بانتظار دور لاعب آخر.';
  if($phase==='bidding' && $action==='bid') return 'الطلب غير صحيح: يجب أن يكون أعلى من الطلب الحالي وبين 7 و13 في الطرنيب.';
  if($phase==='choose_trump') return 'اختر نوع الطرنيب أولًا حتى يبدأ اللعب.';
  if($phase==='playing' && $action==='play_card') return 'هذه الورقة غير قانونية الآن. يجب اتباع نوع الورقة إذا كان موجودًا في يدك.';
  return 'لا يمكن تنفيذ هذه الحركة الآن.';
 }


 private function autoAdvanceNextRound(Room $room,array $state): array{
  if(($state['phase'] ?? '')!=='finished') return $state;
  if(!empty($state['winner_team']) || !empty($state['game_over']) || !empty($state['overall_winner']) || !empty($state['winner_final'])) return $state;
  if(!empty($state['auto_advanced_at']) && (time() - strtotime($state['auto_advanced_at'])) < 2) return $state;
  try{
   $players=array_values($state['players'] ?? []);
   if(count($players)<2) return $state;
   $engine=GameFactory::make($room->game->key);
   $options=['target'=>$state['target'] ?? ($room->target_score ?: ($room->game->rules['targets'][0] ?? 31)),'partners'=>(bool)$room->game->partnership];
   if(in_array($room->game->key,['hand','hand_partner','pinochle','banakil','konkan'],true)){
    $options['previous_scores']=$state['scores'] ?? array_fill_keys($players,0);
    $options['round']=((int)($state['round'] ?? 1))+1;
   }
   $new=$engine->initialState($players,$options);
   if(isset($state['score'])) $new['score']=$state['score'];
   if(isset($state['scores'])) $new['scores']=$state['scores'];
   $new['round']=((int)($state['round'] ?? 1))+1;
   $new['room_code']=$room->code; $new['game']=$room->game->key; $new['seat_partners']=$state['seat_partners'] ?? $this->partnerMap($room->max_players);
   $new['voice_room']=$state['voice_room'] ?? false; $new['voice_fee']=$state['voice_fee'] ?? 0;
   $new['speed']=$state['speed'] ?? 'medium'; $new['turn_timeout_seconds']=$this->normalizeSpeed($new['speed'])[1];
   foreach(['tournament_id','tournament_stage','recording_enabled'] as $k) if(isset($state[$k])) $new[$k]=$state[$k];
   $new['video_frames']=$state['video_frames'] ?? [];
   $new['messages']=array_values(array_merge(array_slice($state['messages'] ?? [],-6),['✅ تم احتساب نقاط الجولة والانتقال تلقائيًا إلى الجولة رقم '.$new['round'].' بدون زر توزيع أو تأكيد جديد.']));
   $new['log']=array_slice(array_merge($state['log'] ?? [],[['system'=>'auto_next_round','round'=>$new['round'],'at'=>now()->toIso8601String()]]),-200);
   $new['auto_advanced_at']=now()->toIso8601String();
   return $new;
  }catch(\Throwable $e){ $state['messages'][]='تعذر الانتقال التلقائي للجولة التالية، يمكن للإدارة مراجعة سجل الأخطاء.'; return $state; }
 }


 private function awardProfilePointsIfFinished(Room $room,array $state): array{
  $phase=(string)($state['phase'] ?? '');
  if(!in_array($phase,['round_end','finished'],true)) return $state;
  $round=(int)($state['round'] ?? 1);
  $isFinal=$phase==='finished' && (array_key_exists('winner_team',$state) || !empty($state['game_over']) || !empty($state['overall_winner']) || !empty($state['winner_final']) || !empty($state['winner']));
  $eventType=$isFinal ? 'match_complete' : 'round_complete';
  $awardedRounds=(array)($state['profile_points_awarded_rounds'] ?? []);
  $awardMarker=$eventType.':'.$round;
  if(in_array($awardMarker,$awardedRounds,true)) return $state;
  try{
   $room->loadMissing('players.user.profile','game');
   $winnerTeam=$state['winner_team'] ?? $state['round_winner_team'] ?? null;
   $winner=$state['winner'] ?? $state['round_winner'] ?? null;
   $teams=$state['teams'] ?? [];
   $mode=!empty($state['tournament_id']) ? (!empty($state['sponsored'])?'sponsored':'tournament') : (!empty($state['club_id'])?'club':'normal');
   $pop=[];
   $service=app(\App\Services\Progression\ProgressionService::class);
   foreach($room->players as $rp){
    if($rp->is_bot || !$rp->user) continue;
    $key='user:'.$rp->user_id;
    $win=$winner===$key;
    if($winnerTeam!==null && isset($teams[$winnerTeam]) && is_array($teams[$winnerTeam])) $win=in_array($key,$teams[$winnerTeam],true);
    $eventKey='web-room:'.$room->id.':round:'.$round.':user:'.$rp->user_id.':'.$eventType;
    $pop[$key]=$service->award($rp->user,$eventKey,[
     'room_id'=>$room->id,'event_type'=>$eventType,'mode'=>$mode,'won'=>$win,
     'stage'=>(string)($state['tournament_stage'] ?? ($isFinal && $win?'champion':'round')),
     'game'=>$room->game?->key,'round'=>$round,
     'same_club_team'=>(bool)($state['same_club_team'] ?? false),
    ]);
   }
   $awardedRounds[]=$awardMarker;
   $state['profile_points_awarded_rounds']=array_values(array_unique($awardedRounds));
   if($isFinal) $state['profile_points_awarded']=true;
   $state['score_popups']=array_merge((array)($state['score_popups'] ?? []),$pop);
   if($pop) $state['messages'][]='✅ تم احتساب نقاط '.$eventType.': الباشا ×2، المسرّعات تراكمية، والمسابقات أعلى من اللعب العادي.';
  }catch(\Throwable $e){ Log::warning('progression_award_failed',['room'=>$room->id,'error'=>$e->getMessage()]); $state['messages'][]='انتهت الجولة، وسيُعاد احتساب النقاط تلقائيًا دون تكرار عند عودة الخدمة.'; }
  return $state;
}

 private function activeXpMultiplier(\App\Models\User $user): float
 {
  $profileMultiplier=max(1,(float)($user->profile?->xp_boost_multiplier ?? 1));
  try{
   $inventoryMultiplier=$user->inventoryItems()
    ->where('active',true)
    ->whereHas('storeItem',fn($q)=>$q->where('category','xp_booster'))
    ->with('storeItem')->get()
    ->map(fn($inv)=>max(1,(float)($inv->storeItem?->payload['multiplier'] ?? 1)))
    ->max();
   return max($profileMultiplier,(float)($inventoryMultiplier ?: 1));
  }catch(\Throwable $e){ return $profileMultiplier; }
}


 private function recentRoomMessages(Room $room): array{
  try{
   return \App\Models\Message::with('sender.profile')
    ->where('room_id',$room->id)->latest()->limit(35)->get()->reverse()->values()
    ->map(function($m){return [
     'id'=>$m->id,
     'name'=>$m->sender?->username ?: 'لاعب',
     'body'=>$m->body,
     'color'=>$m->sender?->profile?->chat_color ?: ($m->sender?->profile?->text_color ?: '#fff'),
     'time'=>$m->created_at?->format('H:i'),
    ];})->all();
  }catch(\Throwable $e){ return []; }
 }

 public function presence(Room $room, Request $r){
  $player=$room->players()->where('user_id',auth()->id())->where('is_bot',false)->first();
  if(!$player) return response()->json(['ok'=>false],200);
  $connected=$r->boolean('connected',true);
  $player->update(['connected'=>$connected]);
  $state=$room->state ?: [];
  if(!$connected){ $state['messages'][]=auth()->user()->username.' خرج من الصفحة مؤقتًا.'; }
  else { $state['messages'][]=auth()->user()->username.' عاد إلى الطاولة.'; }
  // Leaving the page or losing the network is temporary. Keep the room alive
  // so the player can reclaim the same seat; three missed turns are handled by
  // timeoutAutoPlay without consuming manual-exit allowance.
  $room->update(['state'=>$state]);
  return response()->json(['ok'=>true,'connected'=>$connected]);
 }

 public function invite(Room $room, Request $r){
  $data=$r->validate(['user_id'=>'required|exists:users,id']);
  $uid=(int)$data['user_id'];
  $isFriend=\App\Models\Friendship::where('status','accepted')->where(function($q) use($uid){
    $q->where(function($a) use($uid){$a->where('requester_id',auth()->id())->where('addressee_id',$uid);})
      ->orWhere(function($a) use($uid){$a->where('requester_id',$uid)->where('addressee_id',auth()->id());});
  })->exists();
  abort_unless($isFriend || auth()->user()->is_admin,403,'يمكنك دعوة الأصدقاء فقط.');
  DB::table('room_invites')->updateOrInsert(['room_id'=>$room->id,'receiver_id'=>$uid],['sender_id'=>auth()->id(),'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
  $state=$room->state ?: [];
  $passText=($room->visibility==='private' && !empty($state['plain_room_password'])) ? ' — كلمة السر: '.$state['plain_room_password'] : '';
  Notification::create(['user_id'=>$uid,'type'=>'room_invite','title'=>['ar'=>'دعوة لعبة','en'=>'Room invite'],'body'=>['ar'=>auth()->user()->username.' دعاك للعب في غرفة '.$room->code.$passText],'url'=>route('rooms.show',$room->code)]);
  if($r->expectsJson() || $r->ajax()) return response()->json(['ok'=>true,'message'=>'تم إرسال الدعوة للصديق وسيظهر له إشعار أعلى الصفحة.']);
  return back()->with('ok','تم إرسال الدعوة للصديق وسيظهر له إشعار.');
 }
}
