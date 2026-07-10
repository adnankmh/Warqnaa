@extends('layouts.app')
@section('content')
<div class="notifications-page"><h1>🔔 الإشعارات</h1><form method="post" action="{{route('notifications.readAll')}}">@csrf<button class="primary">تعليم الكل كمقروء</button></form>
@forelse($notifications as $n)
 <a class="notification-card {{$n->read?'read':'unread'}}" href="{{$n->url ?: '#'}}"><b>{{$n->title['ar'] ?? $n->type}}</b><p>{{$n->body['ar'] ?? ''}}</p><small>{{$n->created_at->diffForHumans()}}</small></a>
@empty <div class="mini-card">لا توجد إشعارات بعد.</div>@endforelse
{{$notifications->links()}}</div>
@endsection
