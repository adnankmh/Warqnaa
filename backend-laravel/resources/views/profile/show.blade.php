@extends('layouts.app')
@section('content')
@php $profile=$user->profile; $mine=$user->id===auth()->id(); $countries=config('countries'); $games=\App\Models\Game::where('active',true)->orderBy('id')->get(); $code=safe_country_code($profile?->country_code ?? 'PS'); @endphp
<section class="profile-edit-page luxury-form-page">
 <div class="profile-edit-card">
  <h1>تعديل البروفايل</h1>
  <div class="profile-edit-preview">
   <img class="avatar-lg" src="{{$profile?->avatar ?: '/assets/avatars/default.svg'}}" alt="avatar">
   <div><h2>{{$profile?->display_name ?: $user->username}}</h2><div id="countryPreviewBig">{!! flag_img($code,'flag-img flag-small') !!} <b>{{country_name($code)}}</b></div></div>
  </div>

  <div class="profile-theme-box-v131">
   <h2>🎨 الثيمات السريعة</h2>
   <p>اختر الثيم ويتفعل مباشرة على الموقع.</p>
   <div class="profile-theme-grid-v131">
    <button type="button" data-theme-pick="royal">👑 ملكي</button>
    <button type="button" data-theme-pick="midnight">🌙 ليلي</button>
    <button type="button" data-theme-pick="emerald">💎 زمردي</button>
    <button type="button" data-theme-pick="desert">🏜️ صحراوي</button>
    <button type="button" data-theme-pick="galaxy">🌌 مجرة</button>
    <button type="button" data-theme-pick="crimson">❤️ قرمزي</button>
    <button type="button" data-theme-pick="ocean">🌊 محيطي</button>
   </div>
  </div>

  @if($mine)
  <form method="post" action="{{route('profile.update')}}" enctype="multipart/form-data" class="profile-edit-form">@csrf
   <label>الاسم الظاهر</label><input name="display_name" value="{{$profile?->display_name}}" placeholder="اسمك داخل الموقع">
   <label>الدولة والعلم</label><select name="country_code" onchange="updateCountryPreview(this)">@foreach($countries as $cc=>$name)<option value="{{$cc}}" {{$cc===$code?'selected':''}} data-flag="{{flag_url($cc)}}" data-name="{{$name}}">{{$name}}</option>@endforeach</select><div class="country-preview" data-country-preview>{!! flag_img($code,'flag-img flag-small') !!} <b>{{country_name($code)}}</b></div>
   <label>اللعبة المفضلة</label><select name="favorite_game_key"><option value="">اختر لعبة</option>@foreach($games as $g)<option value="{{$g->key}}" {{$profile?->favorite_game_key===$g->key?'selected':''}}>{{$g->name['ar'] ?? $g->key}}</option>@endforeach</select>
   <label>الإيميل</label><input name="email" value="{{$user->email}}">
   <label>الصورة الشخصية</label><input type="file" name="avatar" accept="image/*">
   <div class="two-cols"><div><label>كلمة سر جديدة</label><input type="password" name="password"></div><div><label>تأكيد كلمة السر</label><input type="password" name="password_confirmation"></div></div>
   <button class="btn primary big-save" type="submit">حفظ التعديلات</button>
  </form>
  @endif
 </div>
</section>
@endsection
