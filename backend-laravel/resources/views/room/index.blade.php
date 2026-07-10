@extends('layouts.app')
@section('content')
@php
$userLevel = (int)(auth()->user()->profile?->level ?? 1);
$isTarneeb = in_array($game->key,['tarneeb','tarneeb_400','tarneeb_41'],true);
$targets = $isTarneeb ? [31,41,61] : ((array)($game->rules['targets'] ?? []));
@endphp
<h1>غرف {{ $game->name['ar'] }}</h1>
<p class="muted">قوانين اللعبة في صفحة القوانين فقط. <a class="btn" href="{{route('game.rules')}}#game-{{$game->key}}">عرض القوانين</a></p>
<div class="rooms-layout">
 <section class="leaders"><h3>المتصدرون</h3>@foreach($leaders as $u)<div>{!! flag_img($u->profile?->country_code) !!} {{$u->username}} • {{number_format($u->profile?->games_played ?? 0)}} لعبة</div>@endforeach</section>
 <section class="rooms-list">
  @forelse($rooms as $room)
   <div class="room-card"><a href="{{route('rooms.show',$room->code)}}"><b>غرفة {{ $room->code }}</b></a><span class="room-status-pill">{{ $room->status==='waiting' ? 'مفتوحة' : ($room->status==='playing' ? 'جارية' : $room->status) }} • {{ $room->players->count() }}/{{$room->max_players}}</span><div>@foreach($room->players as $p)<span class="seat-mini"><img src="{{$p->user?->profile?->avatar ?: ($p->is_bot?'/assets/bots/player.svg':'/assets/avatars/default.svg')}}"> {{$p->user?->username ?: $p->bot_key}}</span>@endforeach</div>@unless($room->players->contains('user_id',auth()->id()))<form method="post" action="{{route('rooms.join',$room->code)}}">@csrf @if($room->visibility==='private')<input name="password" placeholder="كلمة السر">@endif<button>دخول</button></form>@endunless</div>
  @empty
   <div class="empty-room"><button class="create-new-room-hero large-empty" type="button" onclick="openCreateRoomModal()">＋ <span>أنشئ لعبة جديدة</span></button><p>لا توجد غرف مفتوحة لهذه اللعبة.</p></div>
  @endforelse
 </section>
 <aside class="create-room compact-create-room" id="createRoomPanel"><button class="create-new-room-hero" type="button" onclick="openCreateRoomModal()">＋ <span>أنشئ لعبة جديدة</span></button><h2>إنشاء لعبة</h2>
  <form method="post" action="{{route('rooms.store')}}" data-ajax-room="1">@csrf
   <input type="hidden" name="game_id" value="{{$game->id}}">
   <input type="hidden" name="voice_room" id="voiceRoomFlag" value="0">
   <label>نوع اللعبة</label>
   <select name="room_type" id="roomTypeSelect" onchange="roomTypeChanged(this)"><option value="public">عامة</option><option value="private">خاصة</option><option value="voice">لعبة صوتية - تخصم 100 توكنز من كل لاعب</option></select><small class="voice-fee-hint hidden">اللعبة الصوتية تخصم 100 توكنز عند إنشاء/دخول الغرفة.</small>
   <input id="privatePasswordInput" class="hidden" name="password" placeholder="كلمة السر للعبة الخاصة">
   <label>عدد المقاعد</label>
   @if(count($allowedSeats)===1)
    <select name="max_players"><option value="{{$allowedSeats[0]}}" selected>{{$allowedSeats[0]}} مقاعد</option></select>
   @else
    <select name="max_players">@foreach($allowedSeats as $i)<option value="{{$i}}" {{$i==$game->max_players?'selected':''}}>{{$i}} مقاعد</option>@endforeach</select>
   @endif
   <label>سرعة اللعب</label><select name="speed"><option value="slow">بطيئة - 10 ثوانٍ</option><option value="medium" selected>متوسطة - 7 ثوانٍ</option><option value="fast">سريعة - 5 ثوانٍ</option></select>
   <label>إمكانية الطرد لصاحب الغرفة الباشا</label><select name="allow_owner_kick"><option value="0" selected>معطلة</option><option value="1">مفعّلة — للباشا فقط</option></select>
   <label>خصم XP عند الخروج اليدوي</label><select name="leave_xp_penalty"><option value="0" selected>بدون خصم</option><option value="1">خصم 200 XP إذا خرج اللاعب وحده أثناء اللعبة</option></select>
   <label>أقل مستوى للدخول</label><select name="min_level">@for($lvl=1;$lvl<=min(100,$userLevel);$lvl++)<option value="{{$lvl}}">المستوى {{$lvl}}</option>@endfor</select>
   @if(count($targets))<label>نهاية اللعبة</label><select name="target_score">@foreach($targets as $target)<option value="{{$target}}">{{$target}}</option>@endforeach</select>@endif
   <button class="btn primary create-room-submit" type="submit">إنشاء الغرفة</button>
  </form>
 </aside>
</div>
<div id="createRoomModal" class="create-room-modal hidden" onclick="if(event.target===this)this.classList.add('hidden')">
 <div class="create-room-modal-card">
  <button type="button" class="modal-x" onclick="document.getElementById('createRoomModal').classList.add('hidden')">×</button>
  <h2>إنشاء لعبة جديدة</h2>
  <p class="muted">اختر نفس خيارات الإنشاء بسرعة من منتصف الصفحة، ثم سيتم فتح الغرفة مباشرة.</p>
  <div id="createRoomModalBody"></div>
 </div>
</div>
<script>
function roomTypeChanged(sel){const scope=sel.closest('form')||document; const pass=scope.querySelector('[name="password"]'),voice=scope.querySelector('[name="voice_room"]'),hint=scope.querySelector('.voice-fee-hint'); if(pass)pass.classList.toggle('hidden',sel.value!=='private'); if(voice)voice.value=sel.value==='voice'?'1':'0'; if(hint)hint.classList.toggle('hidden',sel.value!=='voice');}
function openCreateRoomModal(){const modal=document.getElementById('createRoomModal'),body=document.getElementById('createRoomModalBody'),form=document.querySelector('#createRoomPanel form'); if(!modal||!body||!form)return; const clone=form.cloneNode(true); clone.removeAttribute('id'); clone.classList.add('modal-create-room-form'); clone.querySelectorAll('[id]').forEach((el,i)=>{el.id=el.id+'Modal'+i}); body.innerHTML=''; body.appendChild(clone); modal.classList.remove('hidden'); clone.querySelector('select')?.focus();}
</script>
@endsection
