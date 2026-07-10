@extends('layouts.app')
@section('content')
<section class="wz-audit-v124">
 <header><h1>🛡️ Engine Audit v124</h1><p>فحص سريع لكل الألعاب: هل لها محرك؟ هل ترجع حالة لعب صالحة؟</p><p class="muted">محركات عميقة: {{ $score['deep_engines'] ?? 0 }} — محركات fallback آمنة: {{ $score['fallback_engines'] ?? 0 }}</p></header>
 <div class="audit-grid-v124">
 @foreach($rows as $r)
  <article class="audit-card-v124 {{ $r['ok'] ? 'ok' : 'bad' }}">
   <b>{{ $r['name'] }}</b>
   <span>{{ $r['key'] }}</span>
   <small>{{ $r['family'] }} • {{ $r['engine'] }}</small>
   <em>{{ $r['phase'] }} / {{ $r['turn'] }}</em>
   @if(!$r['ok'])<p>{{ $r['error'] ?? 'غير جاهز' }}</p>@endif
  </article>
 @endforeach
 </div>
</section>
@endsection
