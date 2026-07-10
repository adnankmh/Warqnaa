@extends('layouts.app')
@section('content')
<div class="search-hero"><h1>🔎 البحث عن لاعب</h1><p>اكتب اسم اللاعب أو جزءًا منه للعثور على حسابه، مستواه، دولته، وإرسال طلب صداقة.</p></div>
<form class="player-search-form" method="get" action="{{route('players.search')}}">
 <input name="q" value="{{$q}}" placeholder="مثال: Adnan أو جزء من الاسم" autofocus>
 <button class="primary">بحث</button>
</form>
@if($q==='')
 <div class="mini-card">اكتب اسم اللاعب في مربع البحث بالأعلى.</div>
@else
 <div class="player-results">
 @forelse($users as $u)
  <div class="player-result-card">
   <div class="name-orbit" style="--orbit:{{$u->profile?->name_color ?? '#facc15'}}">
    <img src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}">
   </div>
   <div class="player-result-main">
    <h3 style="color:{{$u->profile?->name_color ?? '#facc15'}}"><img class="flag-img" src="/assets/flags/{{strtoupper($u->profile?->country_code ?? 'PS')}}.svg" onerror="this.style.display='none'" alt="flag"> {{$u->username}} @if($u->profile?->badge)<small>{{$u->profile->badge}}</small>@endif</h3>
    <p>Level {{$u->profile?->level ?? 1}} • XP {{number_format($u->profile?->xp ?? 0)}} • فوز {{number_format($u->profile?->wins ?? 0)}} / ألعاب {{number_format($u->profile?->games_played ?? 0)}}</p>
   </div>
   <button type="button" class="btn" onclick="openProfile({{$u->id}})">عرض البروفايل</button>
   @if($u->id!==auth()->id())
    <form method="post" action="{{route('friends.request',$u)}}">@csrf<button>إضافة صديق</button></form><form method="post" action="{{route('friends.block',$u)}}">@csrf<button class="danger">حظر</button></form>
   @endif
  </div>
 @empty
  <div class="mini-card">لا يوجد لاعب مطابق لهذا البحث.</div>
 @endforelse
 </div>
@endif
@endsection
