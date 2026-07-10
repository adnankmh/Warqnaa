@extends('layouts.app')
@section('content')<div class="contact-page"><h1>اتصل بنا / الدعم</h1><form class="pro-card" method="post" action="{{route('contact.send')}}">@csrf<input name="subject" placeholder="عنوان المشكلة" required><textarea name="message" placeholder="اكتب رسالتك للدعم" required></textarea><button class="primary">إرسال للدعم</button></form></div>@endsection
