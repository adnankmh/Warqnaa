@extends('layouts.app')
@section('content')
@php $online=$club->members->filter(fn($m)=>$m->user?->last_seen_at && $m->user->last_seen_at->gt(now()->subMinutes(5))); @endphp
<div class="club-page-shell">
 <section class="club-profile-hero pro-card">
  <div class="club-emblem big">{{$club->logo ?: '👥'}}</div>
  <div><h1>{{$club->name}}</h1><p>المالك: <b>{{$club->owner?->username}}</b> • تاريخ الإنشاء: {{$club->created_at?->format('Y-m-d')}}</p><div class="club-meta-row"><span class="club-league-badge">{{$clubLeagues[min(6,max(1,$club->level))] ?? 'برونزي'}}</span><span class="pill">Level {{$club->level}}</span><span class="pill">{{$club->members->count()}}/{{$clubCaps[min(6,max(1,$club->level))] ?? 50}}</span><span class="pill online-pill">🟢 {{$online->count()}} Online</span><span class="pill">🪙 {{number_format($club->treasury)}}</span></div></div>
  @if($club->owner_id===auth()->id() || auth()->user()->is_admin)
  <form method="post" action="{{route('clubs.delete',$club)}}" data-confirm="هل تريد حذف المجموعة نهائيًا؟ سيتم حذف الأعضاء والطلبات الخاصة به.">@csrf<button class="danger">حذف المجموعة نهائيًا</button></form>
  @endif
 </section>
 @if($canManageClub)
 <section class="pro-card"><h2>🎨 هوية وإعدادات النادي</h2><form method="post" action="{{route('clubs.settings.update',$club)}}" class="system-grid">@csrf<input name="name" value="{{$club->name}}" required maxlength="120"><input name="logo" value="{{$club->logo}}" maxlength="500" placeholder="شعار Emoji أو رابط صورة"><textarea name="description" maxlength="1000" placeholder="وصف النادي">{{$club->description}}</textarea><select name="visibility"><option value="public" {{$club->visibility==='public'?'selected':''}}>عام</option><option value="request" {{$club->visibility==='request'?'selected':''}}>بطلب انضمام</option><option value="private" {{$club->visibility==='private'?'selected':''}}>خاص</option></select><button class="primary">حفظ الهوية</button></form></section>
 @endif
 <section class="pro-card v105-club-economy"><h2>📊 نظام المجموعة</h2><div class="system-grid"><span>الدوري الحالي: {{$clubLeagues[min(6,max(1,$club->level))] ?? 'برونزي'}}</span><span>السعة: {{$clubCaps[min(6,max(1,$club->level))] ?? 20}}</span><span>نقاط أسبوعية: {{$club->weekly_points}}</span><span>الخزنة: 🪙 {{number_format($club->treasury)}}</span><span>اللعب الجماعي يعطي Bonus x2</span><span>المالك/المشرف يوزع الخزنة حسب الصلاحيات</span></div></section>
 <section class="pro-card"><h2>اللاعبون الموجودون الآن</h2><div class="club-online-list">@forelse($online as $m)<button type="button" onclick="openProfile({{$m->user_id}})"><img src="{{$m->user?->profile?->avatar ?: '/assets/avatars/default.svg'}}">{{$m->user?->username}}</button>@empty<p class="muted">لا يوجد أعضاء Online حاليًا.</p>@endforelse</div></section>
 <section class="pro-card"><h2>أعضاء المجموعة</h2><div class="club-member-table">@foreach($club->members as $m)<div class="club-member-row"><button type="button" class="user-chip" onclick="openProfile({{$m->user_id}})"><img class="avatar-xs" src="{{$m->user?->profile?->avatar ?: '/assets/avatars/default.svg'}}">{{$m->user?->username}}</button><span class="pill">{{$m->role}}</span><span>نقاط: {{$m->weekly_points}}</span><small>{{($m->user?->last_seen_at && $m->user->last_seen_at->gt(now()->subMinutes(5)))?'Online':'Offline'}}</small>@if(($club->owner_id===auth()->id() || auth()->user()->is_admin) && $m->role!=='owner')<form class="club-permission-form" method="post" action="{{route('clubs.memberAction',[$club,$m])}}">@csrf<input type="hidden" name="action" value="moderator"><label><input type="checkbox" name="accept_members" {{$m->permissions['accept_members']??false?'checked':''}}> قبول</label><label><input type="checkbox" name="kick_members" {{$m->permissions['kick_members']??false?'checked':''}}> طرد</label><label><input type="checkbox" name="create_tournaments" {{$m->permissions['create_tournaments']??false?'checked':''}}> مسابقات</label><label><input type="checkbox" name="manage_chat" {{$m->permissions['manage_chat']??false?'checked':''}}> دردشة</label><label><input type="checkbox" name="create_announcements" {{$m->permissions['create_announcements']??false?'checked':''}}> إعلانات</label><label><input type="checkbox" name="manage_club" {{$m->permissions['manage_club']??false?'checked':''}}> هوية النادي</label><button>جعله مشرف</button></form><form method="post" action="{{route('clubs.memberAction',[$club,$m])}}" data-confirm="هل تريد طرد هذا اللاعب؟">@csrf<input type="hidden" name="action" value="kick"><button class="danger">طرد</button></form>@endif</div>@endforeach</div></section>
 @if($canAccept)
 <section class="pro-card"><h2>طلبات الانضمام</h2>@forelse($club->joinRequests->where('status','pending') as $req)<form class="inline-card" method="post" action="{{route('clubs.respond',$req)}}">@csrf<b>{{$req->user->username}}</b><button class="success" name="status" value="accepted">قبول</button><button class="danger" name="status" value="rejected">رفض</button></form>@empty<p class="muted">لا توجد طلبات معلقة.</p>@endforelse</section>
 @endif

 <section class="pro-card club-announcements-v161"><h2>📣 إعلانات النادي</h2>
  @if($canAnnounce)
  <form method="post" action="{{route('clubs.announcements.store',$club)}}" class="club-announcement-form">@csrf
   <input name="title" required maxlength="140" placeholder="عنوان الإعلان">
   <textarea name="body" required maxlength="2000" placeholder="نص الإعلان للأعضاء"></textarea>
   <label><input type="checkbox" name="pinned" value="1"> تثبيت الإعلان</label>
   <button class="primary">نشر الإعلان</button>
  </form>
  @endif
  <div class="club-announcement-list">
  @forelse($club->announcements as $announcement)
   <article class="mini-card {{$announcement->pinned?'pinned':''}}"><header><b>{{$announcement->pinned?'📌 ':''}}{{$announcement->title}}</b><small>{{$announcement->author?->username}} • {{$announcement->created_at?->diffForHumans()}}</small></header><p>{!! nl2br(e($announcement->body)) !!}</p>
   @if($club->owner_id===auth()->id() || auth()->user()->is_admin || $announcement->author_id===auth()->id())<form method="post" action="{{route('clubs.announcements.delete',[$club,$announcement])}}">@csrf<button class="danger small">حذف</button></form>@endif</article>
  @empty<p class="muted">لا توجد إعلانات بعد.</p>@endforelse
  </div>
 </section>
 @if($canTournament)
 <section class="pro-card club-tournament-builder-v161"><h2>🏆 إنشاء مسابقة للنادي</h2><p class="muted">يسمح بها للمالك أو المشرف الذي منحه المالك صلاحية المسابقات.</p>
  <form method="post" action="{{route('clubs.tournaments.store',$club)}}" class="system-grid">@csrf
   <select name="game_id" required>@foreach(\App\Models\Game::where('active',true)->get() as $game)<option value="{{$game->id}}">{{$game->name}}</option>@endforeach</select>
   <select name="stages"><option value="1">مرحلة واحدة — النهائي</option><option value="2">مرحلتان — نصف نهائي ونهائي</option><option value="3">3 مراحل — ربع/نصف/نهائي</option><option value="4">4 مراحل — ثمن/ربع/نصف/نهائي</option></select>
   <select name="seats_per_match"><option value="2">مقعدان</option><option value="4" selected>4 مقاعد</option><option value="6">6 مقاعد</option></select>
   <input type="number" name="entry_fee" value="0" min="0" placeholder="رسوم الدخول">
   <input type="number" name="prize_pool" value="10000" min="0" placeholder="صندوق الجوائز">
   <button class="primary">إنشاء مسابقة النادي</button>
  </form>
 </section>
 @endif
 <section class="pro-card"><h2>📜 سجل النادي</h2><div class="club-announcement-list">@forelse($club->activityLogs->take(50) as $log)<article class="mini-card"><header><b>{{$log->description}}</b><small>{{$log->actor?->username ?? 'النظام'}} • {{$log->created_at?->diffForHumans()}}</small></header></article>@empty<p class="muted">لا توجد أحداث مسجلة بعد.</p>@endforelse</div></section>
 <div class="club-actions-row"><a class="btn" href="{{route('clubs')}}">العودة للمجموعات</a>@if($club->members->where('user_id',auth()->id())->count())<form method="post" action="{{route('clubs.leave',$club)}}" data-confirm="هل تريد مغادرة المجموعة؟">@csrf<button class="danger">مغادرة المجموعة</button></form>@else<form method="post" action="{{route('clubs.join',$club)}}">@csrf<button>طلب انضمام</button></form>@endif</div>
</div>
@endsection
