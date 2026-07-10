@extends('layouts.app')
@section('content')
@php
$gameName = $tournament->game->name['ar'] ?? $tournament->game->key;
@endphp
<div class="replay-page pro-card">
 <h1>📼 تسجيل المسابقة #{{ $tournament->id }} — {{ $gameName }}</h1>
 <p class="muted">هذا تسجيل فيديو داخل المتصفح يتم توليده من سجل حركات المسابقة. بعد الضغط على زر التسجيل سيتم إنتاج فيديو WebM قابل للتشغيل والتحميل، ومع نهاية المباراة تظهر أوراق جميع اللاعبين.</p>
 <div class="replay-toolbar">
  <a class="btn" href="{{ route('tournaments') }}">العودة للمسابقات</a>
  @if($room)<a class="btn success" href="{{ route('rooms.show',$room->code) }}">فتح الغرفة {{ $room->code }}</a>@endif
  <button type="button" class="primary" onclick="playTournamentReplay(false)">تشغيل العرض</button>
  <button type="button" class="primary" onclick="playTournamentReplay(true)">توليد فيديو التسجيل</button>
 </div>
 <div class="replay-stage-wrap">
  <canvas id="tournamentReplayCanvas" width="1280" height="720"></canvas>
  <video id="tournamentReplayVideo" controls class="hidden"></video>
 </div>
 <div id="replayStatus" class="mini-card">جاهز للتشغيل.</div>
 <section class="replay-log-list">
  <h2>سجل الحركات</h2>
  @forelse($frames as $f)
   <div class="replay-log-row"><b>{{ $f['title'] }}</b><span>{{ $f['body'] }}</span><small>{{ $f['at'] }}</small></div>
  @empty <p class="muted">لا يوجد سجل حتى الآن.</p>@endforelse
 </section>
</div>
<script>
window.TOURNAMENT_REPLAY_FRAMES = @json($frames, JSON_UNESCAPED_UNICODE);
window.TOURNAMENT_FINAL_HANDS = @json($finalHands, JSON_UNESCAPED_UNICODE);
window.TOURNAMENT_REPLAY_TITLE = @json('مسابقة #'.$tournament->id.' — '.$gameName, JSON_UNESCAPED_UNICODE);
</script>
@endsection
