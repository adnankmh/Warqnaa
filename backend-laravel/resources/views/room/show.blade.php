@extends('layouts.app')
@section('content')
@php
$state = $room->state ?: [];
$phase = $state['phase'] ?? 'waiting';
$fixedTimeout = max(5, min(10, (int)($state['turn_timeout_seconds'] ?? 7)));
$speedLabel = ($state['speed'] ?? 'medium')==='fast' ? 'سريعة' : ((($state['speed'] ?? 'medium')==='slow') ? 'بطيئة' : 'متوسطة');
$score = $state['score'] ?? ['teamA'=>0,'teamB'=>0];
$roundTricks = $state['round_tricks'] ?? ['teamA'=>0,'teamB'=>0];
$bid = $state['bid'] ?? null;
$trump = $state['trump'] ?? null;
$gameKey = $room->game->key;
$handLike = in_array($gameKey,['hand','hand_partner','saudi_hand','banakil','pinochle','solitaire_multiplayer'],true);
$needsBid = in_array($gameKey,['tarneeb','tarneeb_400','tarneeb_41','estimation','hokm','kout4','kout6'],true);
$needsTrump = in_array($gameKey,['tarneeb','tarneeb_400','tarneeb_41','hokm','kout4','kout6','baloot'],true);
$seatClasses = ['south'=>'seat-south','north'=>'seat-north','west'=>'seat-west','east'=>'seat-east','south_west'=>'seat-south-west','south_east'=>'seat-south-east'];
$seatNames = ['south'=>'أنت / الجنوب','north'=>'الشريك / الشمال','west'=>'الغرب','east'=>'الشرق','south_west'=>'جنوب غرب','south_east'=>'جنوب شرق'];
$suitNames=['clubs'=>'♣ سنك','diamonds'=>'♦ ديناري','spades'=>'♠ بستوني','hearts'=>'♥ كبة'];

$teamAName = collect($seatPlayers)->filter(fn($p)=>in_array($p->seat,['south','west'],true))->map(fn($p)=>$p->user?->username ?: $p->bot_key)->filter()->implode(' + ') ?: 'اللاعبون';
$teamBName = collect($seatPlayers)->filter(fn($p)=>in_array($p->seat,['north','east'],true))->map(fn($p)=>$p->user?->username ?: $p->bot_key)->filter()->implode(' + ') ?: 'المنافسون';
@endphp

@php
$acceptedFriendsV132=\App\Models\Friendship::with(['requester.profile','addressee.profile'])
 ->where('status','accepted')
 ->where(function($q){$q->where('requester_id',auth()->id())->orWhere('addressee_id',auth()->id());})
 ->get()
 ->map(fn($f)=>$f->requester_id===auth()->id()?$f->addressee:$f->requester)
 ->filter();
@endphp

<div class="room-shell pro-room v108-room-shell room-wide-v131" data-room="{{$room->code}}" data-game="{{$gameKey}}">
 <aside class="room-info pro-panel compact-panel">
  <h3>غرفة {{$room->code}}</h3>
  <p><b>اللعبة:</b> {{$room->game->name['ar'] ?? $room->game->key}}</p>
  <p><b>الحالة:</b> <span class="status-pill">{{$phase}}</span></p>
  <p><b>اللاعبون:</b> {{$room->players->where('is_bot',false)->count()}}/{{$room->max_players}}</p>
  <p class="speed-clean-v108"><b>سرعة الدور:</b> <b id="turnTimer">{{$fixedTimeout}}</b> ثواني فقط</p>
  
   <div class="room-friend-invite-v132">
    <button type="button" onclick="document.getElementById('friendInviteBoxV132')?.classList.toggle('hidden')">📨 دعوة صديق</button>
    <div id="friendInviteBoxV132" class="friend-invite-box-v132 hidden">
     <b>اختر صديقًا لدعوته لهذه اللعبة</b>
     @forelse($acceptedFriendsV132 as $fr)
      <form method="post" action="{{ route('rooms.invite',$room->code) }}">@csrf<input type="hidden" name="user_id" value="{{$fr->id}}"><button type="submit"><img src="{{$fr->profile?->avatar ?: '/assets/avatars/default.svg'}}"> {{$fr->username}}</button></form>
     @empty
      <p class="muted">لا يوجد أصدقاء بعد. أرسل طلبات صداقة من صفحة اللاعبين.</p>
     @endforelse
    </div>
   </div>

  <div class="score-card">
   <h4>النتيجة</h4>
   <div class="score-row"><span id="teamAName">{{$teamAName}}</span><b id="scoreA">{{$score['teamA'] ?? 0}}</b><small>لمات: <span id="tricksA">{{$roundTricks['teamA'] ?? 0}}</span></small></div>
   <div class="score-row"><span id="teamBName">{{$teamBName}}</span><b id="scoreB">{{$score['teamB'] ?? 0}}</b><small>لمات: <span id="tricksB">{{$roundTricks['teamB'] ?? 0}}</span></small></div>
   @if($needsBid)
    <div class="score-meta request-only">الطلب الحالي: <b id="currentBid">{{$bid ? (($bid['value'] ?? '').' - '.(($bid['team'] ?? '')==='teamA'?'الفريق A':'الفريق B')) : 'لا يوجد'}}</b></div>
   @endif
   @if($needsTrump)
    <div class="score-meta trump-only">الطرنيب: <b id="currentTrump">{{$trump ? ($suitNames[$trump] ?? $trump) : 'لم يحدد'}}</b></div>
   @endif
  </div>
  @if((auth()->user()->profile?->pasha_days ?? 0)>0)
   @php $isAway = !empty(($state['away_players'] ?? [])[$myKey]); @endphp
   <div class="away-status {{$isAway ? 'active' : ''}}">{{$isAway ? '🟡 أنت الآن في وضع الغائب، الكمبيوتر يلعب بدلك.' : '🟢 أنت حاضر وتلعب بنفسك.'}}</div>
   <form method="post" action="{{route('rooms.away',$room->code)}}" data-confirm="تفعيل/إلغاء وضع الغائب؟ الكمبيوتر سيلعب بدلًا عنك مؤقتًا.">@csrf<button type="submit" class="btn big-action away-btn">{{$isAway ? '✅ العودة للعب بنفسي' : '🕒 تفعيل وضع الغائب'}}</button></form>
  @endif
  <form method="post" action="{{route('rooms.leave',$room->code)}}" data-confirm="هل تريد الخروج من اللعبة؟ إذا خرجت 3 مرات من نفس اللعبة لن تستطيع العودة لها مرة أخرى.">@csrf<button class="danger big-action">🚪 خروج والعودة لغرف {{$room->game->name['ar'] ?? $room->game->key}}</button></form>
  <a class="btn big-action" href="{{route('rooms.index',$room->game->key)}}">العودة للغرف</a>
 </aside>
 <section class="table-wrap">
  <div class="game-table premium-table seats-{{$room->max_players}}  square-table seats-{{$room->max_players}} {{$handLike ? 'hand-like-table' : 'single-row-table'}} {{$activeTableSkin}} {{auth()->user()->profile?->active_effect}}" @if(!empty($activeTableImage)) style="background-image:linear-gradient(rgba(3,7,18,.22),rgba(3,7,18,.38)),url('{{$activeTableImage}}');background-size:cover;background-position:center;" @endif>
   <div class="table-aura"></div>
   <div class="deck-stack"><span></span><span></span><span></span></div>
   @if(($room->owner_id===auth()->id() || auth()->user()->is_admin) && $phase==='waiting')
    <form id="centerStartForm" class="center-start-form" method="post" action="{{route('rooms.start',$room->code)}}" data-ajax-start="1">@csrf<button class="primary start-game-orb" type="submit">▶️ بدء اللعبة</button></form>
   @endif
   @foreach($seats as $seat)
    <div class="player-seat {{$seatClasses[$seat] ?? ''}}" data-seat="{{$seat}}">
     @include('room.seat',['player'=>$seatPlayers[$seat] ?? null,'seatName'=>$seatNames[$seat] ?? $seat,'seat'=>$seat])
    </div>
   @endforeach
   <div class="center-board">
    <div class="phase-title" id="phaseTitle">{{ $phase==='waiting' ? 'بانتظار بدء الجولة' : ($phase==='bidding' ? 'مرحلة الطلب' : ($phase==='choose_trump' ? 'اختيار الطرنيب' : ($phase==='finished' ? 'انتهت الجولة' : 'اللعب جارٍ'))) }}</div>
    <div class="last-trick" id="lastAction">{{ $state['messages'][count($state['messages'] ?? [])-1] ?? ($state['last_action']['action'] ?? 'لم تبدأ الحركة بعد') }}</div>
    <div id="tableTrick" class="table-trick"></div>
    <div class="quick-reactions-mini-v132">
     <button type="button" class="reaction-toggle-v132" onclick="document.getElementById('quickReactionsV132')?.classList.toggle('hidden')">⚡</button>
     <div id="quickReactionsV132" class="quick-reactions-box-v132 hidden">
      <button type="button" onclick="sendEmojiChat?.('🔥')">🔥</button>
      <button type="button" onclick="sendEmojiChat?.('😂')">😂</button>
      <button type="button" onclick="sendEmojiChat?.('👑')">👑</button>
      <button type="button" onclick="sendEmojiChat?.('👏')">👏</button>
      <button type="button" onclick="sendEmojiChat?.('😮')">😮</button>
     </div>
    </div>

    <div id="lastTrickMini" class="last-trick-mini hidden"><b>اللفة السابقة</b><div></div></div>
   </div>
   <div class="bid-panel action-panel game-action-panel action-panel-{{$room->game->key}} {{$handLike ? 'hand-controls-panel' : ''}}" id="actionPanel">
     <div class="tarneeb-request-panel">
      <div class="tarneeb-request-title">مرحلة الطلب</div>
      <button data-action="pass" class="pass-btn tarneeb-pass">تمرير</button>
      <div class="tarneeb-bid-grid">
       @for($i=7;$i<=13;$i++) <button data-action="bid" data-value="{{$i}}" class="tarneeb-bid pro-bid-btn" title="اعتماد طلب {{$i}}"><span>{{$i}}</span></button> @endfor
      </div>
      <div class="estimation-bid-grid">
       @for($i=0;$i<=6;$i++) <button data-action="bid" data-value="{{$i}}" class="estimation-bid">{{$i}}</button> @endfor
      </div>
     </div>
     <div class="trump-chooser-panel">
      <div class="trump-title">اختر نوع الطرنيب بعد تأكيد الطلب</div>
      <button data-action="choose_trump" data-suit="hearts" class="trump-card red-suit"><b>♥</b><span>كبة</span></button>
      <button data-action="choose_trump" data-suit="diamonds" class="trump-card red-suit"><b>♦</b><span>ديناري</span></button>
      <button data-action="choose_trump" data-suit="spades" class="trump-card black-suit"><b>♠</b><span>بستوني</span></button>
      <button data-action="choose_trump" data-suit="clubs" class="trump-card black-suit"><b>♣</b><span>سباتي</span></button>
     </div>
     <button data-action="draw_deck" class="hand-btn">اسحب من الدك</button><button data-action="draw_discard" class="hand-btn">اسحب من الرمي</button><button type="button" class="meld-btn hand-btn" onclick="meldSelectedCards()">تجميع المحدد</button><button type="button" class="meld-btn hand-btn" onclick="arrangeMelds()">اعتماد ترتيب المجموعات</button><button type="button" class="sort-btn hand-btn" onclick="sortHandVisual()">ترتيب الورق</button>
     <button data-action="roll" class="backgammon-btn">🎲 رمي النرد</button><button data-action="draw" class="domino-btn">اسحب دومينو</button>
    </div>
    @if($handLike)
    <div class="meld-zone" id="meldZone"><b>مجموعاتك الجاهزة للنزول</b><small>اسحب الورق هنا أو حدده ثم اضغط نزّل مجموعة. يسمح بإعادة ترتيب المجموعات حسب القانون.</small><div id="myMelds"><div class="meld-slot" data-cards="[]">ضع المجموعة هنا</div><div class="meld-slot" data-cards="[]">مجموعة أخرى</div><div class="meld-slot" data-cards="[]">مجموعة ثالثة</div></div></div>
   @endif
   <div class="hand-label">أوراقك</div>
   <div class="hand-row pro-hand" id="myHand"></div>
   @if($handLike)<div class="hand-quick-controls"><button type="button" onclick="roomAction('draw_deck')">اسحب من الدك</button><button type="button" onclick="roomAction('draw_discard')">اسحب من الرمي</button><button type="button" onclick="sortHandVisual()">رتّب الورق</button><button type="button" onclick="meldSelectedCards()">نزّل مجموعة</button></div>@endif
   <div class="game-log" id="gameLog"></div>
</div>
  </div>
 </section>

 @if(!empty(($state['voice_room'] ?? false)))
 <aside id="voiceRoomPanel" class="voice-room-panel pro-panel">
  <div class="voice-head"><b>🎙️ اللعبة الصوتية</b><small>تم خصم 100 توكنز من كل لاعب ينضم لهذه الغرفة الصوتية.</small></div>
  <div class="voice-status" id="voiceStatus">اضغط تشغيل المايك للسماح بالصوت.</div>
  <div class="voice-controls">
   <button type="button" class="primary voice-icon-only" title="تشغيل المايك" onclick="WarqnaVoice?.start()">🎙️</button>
   <button type="button" class="voice-icon-only" title="كتم/تشغيل" onclick="WarqnaVoice?.mute()">🔇</button>
   <button type="button" class="danger voice-icon-only" title="إيقاف" onclick="WarqnaVoice?.stop()">⏹️</button>
  </div>
  <div id="voicePeers" class="voice-peers"><small>يمكنك كتم أي لاعب من الأيقونات أمام اسمه على الطاولة.</small></div><div class="voice-note">ملاحظة تقنية: اسمح للمايك من المتصفح. للصوت بين الأجهزة شغّل سيرفر Socket/WebRTC من ملفات المشروع.</div>
 </aside>
 @endif
 <aside id="gameRoomChat" class="game-room-chat pro-panel">
  <div class="game-room-chat-head"><b>💬 دردشة اللعبة</b><small>ظاهرة فقط للاعبين داخل هذه الغرفة</small></div>
  <div id="gameRoomChatBody" class="game-room-chat-body"><p class="muted">اكتب رسالتك وسيشاهدها كل لاعبي الغرفة.</p></div>
  <form id="gameRoomChatForm" class="game-room-chat-form" onsubmit="sendEmbeddedRoomChat(event)">
   <input id="gameRoomChatInput" maxlength="500" placeholder="اكتب رسالة داخل اللعبة...">
   <button type="submit">إرسال</button>
  </form>
 </aside>

</div>
<script>
window.ROOM_CODE=@json($room->code);
window.ROOM_ACTION_URL=@json(route('rooms.action',$room->code));
window.ROOM_TIMEOUT_URL=@json(route('rooms.timeout',$room->code));
window.ROOM_SYNC_URL=@json(route('rooms.sync',$room->code));
window.ROOM_CHAT_URL=@json(route('rooms.chat',$room->code));
window.ROOM_PRESENCE_URL=@json(route('rooms.presence',$room->code));
window.ROOM_TURN_TIMEOUT={{$fixedTimeout}};
window.CSRF=@json(csrf_token());
window.MY_PLAYER_KEY=@json($myKey);
window.INITIAL_STATE=@json($state);
window.INITIAL_HAND=@json($myHand);
window.MY_CARD_BACK=@json($activeCardBack);
window.MY_CARD_BACK_IMAGE=@json($activeCardBackImage);
window.GAME_KEY=@json($gameKey);
window.HAND_LIKE={{$handLike ? 'true' : 'false'}};
</script>
@endsection
