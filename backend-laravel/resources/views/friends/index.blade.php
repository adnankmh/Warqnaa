@extends('layouts.app')
@section('content')
<h1>الأصدقاء وطلبات الصداقة</h1>
<p class="muted">إدارة طلبات الصداقة والرسائل الخاصة من نفس الصفحة. لا يمكن إرسال طلب أكثر من مرة لنفس اللاعب.</p>
<div class="friends-dashboard">
 <section class="pro-card friend-section"><h2>طلبات واردة</h2>
  @forelse($incoming as $f) @php $u=$f->requester; @endphp
   <div class="friend-row"><button type="button" class="user-chip" onclick="openProfile({{$u->id}})"><img class="avatar-xs" src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}"> {!! flag_img($u->profile?->country_code) !!} {{$u->username}}</button><div class="friend-actions"><form method="post" action="{{route('friends.respond',$f)}}" data-ajax-soft="1">@csrf<input type="hidden" name="status" value="accepted"><button class="primary">قبول</button></form><form method="post" action="{{route('friends.respond',$f)}}" data-ajax-soft="1">@csrf<input type="hidden" name="status" value="rejected"><button>رفض</button></form><form method="post" action="{{route('friends.block',$u)}}" data-confirm="هل تريد حظر هذا اللاعب؟">@csrf<button class="danger">حظر</button></form></div></div>
  @empty <div class="mini-card">لا توجد طلبات واردة.</div> @endforelse
 </section>
 <section class="pro-card friend-section"><h2>طلبات مرسلة</h2>
  @forelse($outgoing as $f) @php $u=$f->addressee; @endphp
   <div class="friend-row"><button type="button" class="user-chip" onclick="openProfile({{$u->id}})"><img class="avatar-xs" src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}"> {!! flag_img($u->profile?->country_code) !!} {{$u->username}}</button><span class="pill">تم إرسال الطلب</span><form method="post" action="{{route('friends.cancel',$f)}}" data-ajax-soft="1">@csrf<button class="danger">إلغاء الطلب</button></form></div>
  @empty <div class="mini-card">لا توجد طلبات مرسلة.</div> @endforelse
 </section>
 <section class="pro-card friend-section"><h2>الأصدقاء</h2>
  @forelse($accepted as $f) @php $u=$f->requester_id===auth()->id()?$f->addressee:$f->requester; @endphp
   <div class="friend-row"><button type="button" class="user-chip" onclick="openProfile({{$u->id}})"><img class="avatar-xs" src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}"> {!! flag_img($u->profile?->country_code) !!} {{$u->username}}</button><div class="friend-actions"><button onclick="setChatMode('friends');openFriendThread({{$u->id}});document.getElementById('chatDock')?.classList.remove('hidden','chat-minimized')">رسالة</button><form method="post" action="{{route('friends.block',$u)}}" data-confirm="هل تريد حظر هذا اللاعب؟">@csrf<button class="danger">حظر</button></form></div></div>
  @empty <div class="mini-card">لا يوجد أصدقاء بعد.</div> @endforelse
 </section>
 <section class="pro-card friend-section"><h2>المحظورون</h2>
  @forelse($blocked as $f) @php $u=$f->requester_id===auth()->id()?$f->addressee:$f->requester; @endphp
   <div class="friend-row"><button type="button" class="user-chip" onclick="openProfile({{$u->id}})"><img class="avatar-xs" src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}"> {{$u->username}}</button><span class="pill danger">محظور</span><form method="post" action="{{route('friends.unblock',$u)}}" data-ajax-soft="1">@csrf<button>إلغاء الحظر</button></form></div>
  @empty <div class="mini-card">لا يوجد لاعبون محظورون.</div> @endforelse
 </section>
</div>
@endsection
