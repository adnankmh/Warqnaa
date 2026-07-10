@extends('layouts.app')
@section('content')
<div class="club-hero premium-module-hero"><h1>👥 المجموعات</h1><p>مجموعات بالعرض مع ملخص سريع. اضغط على أي مجموعة لفتح صفحته الكاملة، الأعضاء، المدير، المشرفين والصلاحيات.</p><div class="club-levels"><span>Level 1: 20</span><span>Level 2: 30</span><span>Level 3: 40</span><span>Level 4: 50</span><span>Level 5: 70</span><span>Level 6: 100</span></div></div>
<form class="inline-card club-create-form" method="post" action="{{route('clubs.store')}}">@csrf
 <input name="name" placeholder="اسم المجموعة الجديد"><small class="pasha-create-note">👑 إنشاء المجموعات متاح للباشا عند توفر التوكنز.</small>
 <button class="primary">إنشاء مجموعة للباشا - 5000 توكنز</button>
</form>
<div class="clubs-horizontal-grid">
@foreach($clubs as $club)
 @php $isMember=$club->members->where('user_id',auth()->id())->count()>0; $online=$club->members->filter(fn($m)=>$m->user?->last_seen_at && $m->user->last_seen_at->gt(now()->subMinutes(5)))->count(); @endphp
 <article class="club-tile pro-card">
  <a class="club-tile-main" href="{{route('clubs.show',$club)}}">
   <div class="club-emblem">👥</div>
   <div><h3>{{$club->name}}</h3><p>المالك: <b>{{$club->owner?->username}}</b></p><small>أنشئ في: {{$club->created_at?->format('Y-m-d')}}</small></div>
  </a>
  <div class="club-meta-row"><span class="club-league-badge">{{$clubLeagues[min(6,max(1,$club->level))] ?? 'برونزي'}}</span><span class="pill">Level {{$club->level}}</span><span class="pill">{{$club->members->count()}}/{{$clubCaps[min(6,max(1,$club->level))] ?? 50}}</span><span class="pill online-pill">🟢 {{$online}} Online</span></div>
  <p class="muted club-summary">نقاط أسبوعية: {{$club->weekly_points}} • نقاط تراكمية: {{number_format($club->total_points ?? 0)}} • خزينة: {{number_format($club->treasury)}} توكنز</p>
  <div class="club-members-preview">@foreach($club->members->take(6) as $m)<button type="button" onclick="openProfile({{$m->user_id}})" title="{{$m->user?->username}}"><img src="{{$m->user?->profile?->avatar ?: '/assets/avatars/default.svg'}}"><span>{{$m->user?->username}}</span></button>@endforeach</div>
  <div class="club-actions-row">
   <a class="btn" href="{{route('clubs.show',$club)}}">فتح المجموعة</a>
   @if($isMember)<span class="success pill">عضو</span>@else<form method="post" action="{{route('clubs.join',$club)}}">@csrf<button>طلب انضمام</button></form>@endif
  </div>
 </article>
@endforeach
</div>
@endsection
