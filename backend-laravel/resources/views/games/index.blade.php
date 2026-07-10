@extends('layouts.app')
@section('content')
@php
$familyLabels=['all'=>'الكل'];
$familyFor=fn($g)=>$g->rules['family'] ?? 'training';
$featured=['tarneeb','hand','trix_complex','baloot','domino','ludo','jackaroo','spades'];
@endphp

<section class="wz-lobby-v130" id="wzLobbyV130">
 <header class="wz-lobby-hero-v130">
  <div class="hero-copy-v130">
   <span>Warqna Pro Lobby</span>
   <h1>صالة ألعاب فخمة وسريعة</h1>
   <p>كل الألعاب مرتبة داخل شاشة واحدة بدون طفح، مع بحث سريع وتصنيفات ومحركات لعب جاهزة.</p>
  </div>
  <div class="hero-actions-v130">
   <a class="primary" href="{{ route('store') }}">💎 المتجر</a>
   <a href="{{ route('tournaments') }}">🏆 المنافسات</a>
   <a href="{{ route('clubs') }}">🏛️ المجموعات</a>
  </div>
 </header>

 <div class="wz-lobby-toolbar-v130">
  <input id="gameSearchV130" placeholder="ابحث عن لعبة...">
  <button type="button" id="compactGamesV130">⚡ ضغط البطاقات</button>
  <button type="button" id="showFeaturedV130">🔥 المميزة</button>
 </div>

 <nav class="wz-family-tabs-v130">
  @foreach($familyLabels as $key=>$label)
   <button type="button" data-family-filter-v130="{{$key}}" class="{{$key==='all'?'active':''}}">{{$label}}</button>
  @endforeach
 </nav>

 <main class="wz-games-wall-v130">
  @foreach($games as $game)
   @php
    $family=$familyFor($game);
    $engine=$game->rules['engine'] ?? 'engine';
    $icon=$game->rules['icon'] ?? game_icon($game->key);
    $isFeatured=in_array($game->key,$featured,true);
   @endphp
   <a class="wz-game-card-v130 {{$isFeatured?'featured':''}}"
      data-featured="{{$isFeatured?1:0}}"
      data-family="{{$family}}"
      data-name="{{ strtolower($game->key.' '.($game->name['ar'] ?? '').' '.($game->name['en'] ?? '').' '.$engine) }}"
      href="{{ route('rooms.index',$game->key) }}">
    <span class="game-orb-v130">{{$icon}}</span>
    <strong>{{ $game->name['ar'] ?? $game->key }}</strong>
    <small>{{ $game->min_players }}-{{ $game->max_players }} لاعبين • {{ $game->partnership ? 'شراكة' : 'فردي' }}</small>
    <em>{{ $engine }}</em>
   </a>
  @endforeach
 </main>
</section>

<script>
(function(){
 const root=document.getElementById('wzLobbyV130');
 const search=document.getElementById('gameSearchV130');
 const tabs=[...document.querySelectorAll('[data-family-filter-v130]')];
 const compact=document.getElementById('compactGamesV130');
 const featured=document.getElementById('showFeaturedV130');
 let onlyFeatured=false;
 function apply(){
  const q=(search?.value||'').toLowerCase().trim();
  const fam=document.querySelector('[data-family-filter-v130].active')?.dataset.familyFilterV130||'all';
  document.querySelectorAll('.wz-game-card-v130').forEach(card=>{
   const okFam=fam==='all'||card.dataset.family===fam;
   const okText=!q||(card.dataset.name||'').includes(q);
   const okFeatured=!onlyFeatured||card.dataset.featured==='1';
   card.hidden=!(okFam&&okText&&okFeatured);
  });
 }
 tabs.forEach(btn=>btn.addEventListener('click',()=>{tabs.forEach(b=>b.classList.toggle('active',b===btn));apply();}));
 search?.addEventListener('input',apply);
 compact?.addEventListener('click',()=>root.classList.toggle('compact'));
 featured?.addEventListener('click',()=>{onlyFeatured=!onlyFeatured; featured.classList.toggle('active',onlyFeatured); apply();});
 apply();
})();
</script>
@endsection
