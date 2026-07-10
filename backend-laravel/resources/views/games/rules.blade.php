@extends('layouts.app')
@section('content')
<div class="rules-hero"><h1>📜 قوانين الألعاب</h1><p>صفحة مستقلة لكل قواعد الألعاب؛ لم تعد القوانين تظهر داخل الغرفة حتى تبقى الطاولة نظيفة ومركزة على اللعب.</p></div>
<div class="rules-list">
@foreach($games as $game)
 <article class="rule-card" id="game-{{$game->key}}">
  <h2>{{$game->name['ar'] ?? $game->key}} <small>{{$game->name['en'] ?? ''}}</small></h2>
  <div class="rule-meta"><span>{{$game->min_players}}-{{$game->max_players}} لاعبين</span><span>{{$game->partnership?'شراكة':'فردي'}}</span></div>
  @php $guide=$ruleGuide[$game->key] ?? $ruleGuide[str_replace(['_partner','_400','_41'],['','',''],$game->key)] ?? null; @endphp
  <p>{{ $guide['summary'] ?? ($game->rules['text'] ?? $game->rules['summary'] ?? 'قواعد قابلة للتوسعة.') }}</p>
  @if($guide)
   <div class="rule-card-collapsed-v136"><ul class="core-rule-list">@foreach($guide['core'] as $r)<li>{{$r}}</li>@endforeach</ul></div>
   <details class="rule-readmore-v136"><summary>اقرأ المزيد عن قوانين {{ $game->name['ar'] ?? $game->key }}</summary><ul class="core-rule-list expanded">@foreach($guide['core'] as $r)<li>{{$r}}</li>@endforeach</ul></details>
  @endif
  @if(!empty($game->rules['translations']))<details class="rule-translations"><summary>الترجمات المتاحة</summary>@foreach($game->rules['translations'] as $lang=>$txt)<div><b>{{strtoupper($lang)}}:</b> {{$txt}}</div>@endforeach</details>@endif
  <a class="btn primary" href="{{route('rooms.index',$game->key)}}">دخول غرف اللعبة</a>
 </article>
@endforeach
</div>
@endsection
