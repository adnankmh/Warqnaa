@php
$p = $player ?? null;
$key = $p ? ($p->is_bot ? 'bot:'.$p->id : 'user:'.$p->user_id) : 'empty';
$isTurn = $p && (($room->state['turn'] ?? null) === $key);
$color = $p?->user?->profile?->name_color ?: ($p?->is_bot ? '#38bdf8' : '#facc15');
$avatar = $p ? ($p->is_bot ? bot_avatar_url($p->bot_key, (int)($p->id ?? 1)) : ($p->user?->profile?->avatar ?: '/assets/avatars/default.svg')) : bot_avatar_url('معتصم', 1);
$frame = $p?->user?->profile?->active_name_frame ?: ($p?->is_bot ? 'glow-ocean' : 'glow-gold');
$countryCode = $p?->user?->profile?->country_code ?? 'PS';
$isSeatedInRoom = $room->players->where('user_id',auth()->id())->count() > 0;
@endphp
@if($p)
 <div class="seat-box {{$p->is_bot ? 'bot-box' : 'human-box'}}">
  <button type="button" class="seat-profile player-glow {{$frame}} {{$p->is_bot ? 'bot-seat' : 'human-seat'}} {{$isTurn ? 'is-turn' : ''}}" data-player-key="{{$key}}" style="--player-color:{{$color}}" @if(!$p->is_bot && $p->user_id) onclick="openProfile({{$p->user_id}})" @endif>
   <span class="player-ring"><img src="{{$avatar}}" alt="avatar"></span>
   <span class="player-name" style="color:{{$color}}">{{$p->user?->username ?: $p->bot_key}}</span>
   <small>@if($p->user){!! flag_img($countryCode) !!} {{country_name($countryCode)}} @else 🤖 BOT جاهز @endif • {{$seatName ?? $p->seat}} @if(!$p->connected) • منقطع @endif</small>
  </button>
  @if($p->is_bot && !$room->players->where('user_id',auth()->id())->count())
   
  @endif
  @if($p->user_id && $p->user_id !== auth()->id() && (($room->owner_id === auth()->id() && (auth()->user()->profile?->pasha_days ?? 0) > 0) || auth()->user()->is_admin))
   <details class="pasha-seat-control pasha-seat-dropdown-v136"><summary>👑 خيارات الباشا</summary><form method="post" action="{{route('rooms.replacePlayer', [$room->code, $p->id])}}" data-confirm="استبدال هذا اللاعب ببوت؟">@csrf<button type="submit">استبدال اللاعب ببوت</button></form></details>
  @endif

  @if(!empty(($room->state['voice_room'] ?? false)) && !$p->is_bot)
   <div class="voice-seat-icons" title="تحكم صوت هذا اللاعب">
    @if($p->user_id===auth()->id())
     <button type="button" title="تشغيل المايك" onclick="WarqnaVoice?.start()">🎙️</button>
     <button type="button" title="كتم/تشغيل نفسي" onclick="WarqnaVoice?.mute()">🔇</button>
     <button type="button" title="إيقاف المايك" onclick="WarqnaVoice?.stop()">⏹️</button>
    @else
     <button type="button" title="كتم هذا اللاعب عندي فقط" onclick="WarqnaVoice?.mutePeer?.('{{$key}}')">🔇</button>
    @endif
   </div>
  @endif
  <div class="seat-played-card" data-player-key="{{$key}}"></div>
 </div>
@else
 <div class="empty-seat bot-placeholder">
  <span class="player-ring"><img src="{{ bot_avatar_url('معتصم',1) }}" alt="bot"></span><b>مقعد فارغ</b><small>{{$seatName ?? ''}}</small>
  @unless($isSeatedInRoom)<form method="post" action="{{route('rooms.join',$room->code)}}">@csrf<input type="hidden" name="seat" value="{{$seat ?? ''}}"><button type="submit">اجلس هنا</button></form>@else<small class="seat-locked-v136">تم اختيار مقعدك، لا يمكن تغيير المكان داخل نفس اللعبة.</small>@endunless
 </div>
@endif
