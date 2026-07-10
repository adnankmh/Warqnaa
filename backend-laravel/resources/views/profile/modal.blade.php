@php
$profile=$user->profile; $wallet=$user->wallet; $mine=$user->id===auth()->id();
$xp=(int)($profile?->xp ?? 0); $level=(int)($profile?->level ?? 1); $next=app(\App\Services\Leveling\XpService::class)->requiredXp($level); $need=max(0,$next-$xp); $percent=$next?min(100,round(($xp/$next)*100)):0;
$code=safe_country_code($profile?->country_code ?? 'PS');
$relation=$relation ?? null;
@endphp
<div class="profile-modal-card compact-profile-card {{$mine ? 'profile-self-card' : 'profile-other-card'}}">
 <button type="button" class="modal-x" onclick="document.getElementById('profileModal').classList.add('hidden')">×</button>
 <div class="profile-head">
  <img class="avatar-lg" src="{{$profile?->avatar ?: '/assets/avatars/default.svg'}}" alt="avatar">
  <div>
   <h2 style="color:{{$profile?->name_color ?: '#facc15'}}">{{$profile?->display_name ?: $user->username}}</h2>
   <div class="country-line">{!! flag_img($code,'flag-img flag-small') !!} <b>{{country_name($code)}}</b><a class="btn store-inventory-profile-link" href="{{ route('store') }}#inventory">🎒 مشترياتي</a></div>
   <small>{{$mine ? 'بروفايلك الشخصي' : 'لاعب في Warqna'}}</small><div class="favorite-game-line-v112">🎮 اللعبة المفضلة: <b>{{ optional(\App\Models\Game::where('key', $profile?->favorite_game_key)->first())->name['ar'] ?? ($profile?->favorite_game_key ?: 'غير محددة') }}</b></div>
  </div>
 </div>
 <div class="profile-mini-grid"><div><b>{{$level}}</b><span>المستوى</span></div><div><b>{{number_format($profile?->wins ?? 0)}}</b><span>فوز</span></div><div><b>{{number_format($profile?->games_played ?? 0)}}</b><span>لعبة</span></div><div><b>{{number_format($wallet?->tokens ?? 0)}}</b><span>توكنز</span></div></div>
 <div class="xp-box"><div class="xp-text">يحتاج اللاعب <b>{{number_format($need)}}</b> XP للصعود للمستوى التالي</div><div class="progress"><span style="width:{{$percent}}%"></span></div></div>
 @if($mine)<div class="profile-self-details"><div><b>{{ number_format($profile?->xp ?? 0) }}</b><span>XP الحالي</span></div><div><b>{{ number_format($profile?->losses ?? 0) }}</b><span>خسارة</span></div><div class="profile-pasha-days-v134"><img src="/assets/store/basha1.png" alt="باشا"><b>{{ number_format($profile?->pasha_days ?? 0) }}</b><span>أيام الباشا</span></div><div><b>{{ number_format($wallet?->tokens ?? 0) }}</b><span>الرصيد</span></div></div>@endif
	 <div class="active-items"><b>المقتنيات المفعلة</b><div class="active-chip-row">@foreach($user->inventoryItems->where('active',true)->take($mine?12:6) as $inv)<span class="active-chip">{{$inv->storeItem?->payload['preview_icon'] ?? '✨'}} {{$inv->storeItem?->name['ar'] ?? 'مقتنى'}}</span>@endforeach @if($user->inventoryItems->where('active',true)->count()===0)<span class="muted">لا توجد مقتنيات مفعلة.</span>@endif</div></div>
 <div class="profile-actions">
  @if($mine)
   <a class="btn primary" href="{{route('profile.show',$user)}}">تعديل البروفايل</a><a class="btn" href="{{route('store')}}#inventory">مشترياتي</a><a class="btn" href="{{route('tokens')}}">التوكنز</a>
  @else
   @if($relation && $relation->status==='accepted')
    <button class="btn primary" onclick="setChatMode('friends');openChatThread({{$user->id}})">رسالة</button>
   @elseif($relation && $relation->status==='pending')
    <button class="btn" disabled>تم إرسال الطلب</button>
   @elseif($relation && $relation->status==='blocked')
    <button class="btn danger" disabled>محظور</button>
   @else
    <form method="post" action="{{route('friends.request',$user)}}" data-ajax-profile-action="1">@csrf<button class="btn primary" type="submit">طلب صداقة</button></form>
   @endif
   <form method="post" action="{{route('friends.block',$user)}}" data-confirm="هل تريد حظر هذا اللاعب؟">@csrf<button class="btn danger" type="submit">حظر</button></form>
   <form class="inline-transfer" method="post" action="{{route('wallet.transfer')}}" data-confirm="هل أنت متأكد من إرسال التوكنز؟ سيتم خصم عمولة 10%.">@csrf<input type="hidden" name="receiver" value="{{$user->username}}"><input name="amount" type="number" min="1" placeholder="توكنز"><button class="btn" type="submit">إرسال توكنز</button></form>
  @endif
 </div>
</div>
