@extends('layouts.app')
@section('content')
<div class="settings-page"><h1>⚙️ الإعدادات</h1>
@php($countriesV134=config('countries',[]))
<div class="pro-card settings-avatar-upload-v108">
 <h3>الصورة الشخصية</h3>
 <div class="avatar-edit-block">
  <img class="settings-avatar-preview" src="{{ auth()->user()->profile?->avatar ?: '/assets/avatars/default.svg' }}" alt="avatar">
  <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
   @csrf
   <label class="avatar-file-label">تغيير الصورة الشخصية
    <input type="file" name="avatar" accept="image/*" onchange="this.closest('form').querySelector('.avatar-save-btn').classList.remove('hidden')">
   </label>
   <button class="primary avatar-save-btn hidden" type="submit">حفظ الصورة</button>
  </form>
 </div>
</div>
<form class="pro-card" method="post" action="{{route('settings.save')}}">@csrf
<label>الاسم المعروض</label><input name="display_name" value="{{auth()->user()->profile?->display_name}}">
<label>الدولة</label>
<select name="country_code" class="country-select-v134">
 @foreach($countriesV134 as $code=>$c)
  <option value="{{$code}}" {{(auth()->user()->profile?->country_code ?? 'PS')===$code?'selected':''}}>{{$c['flag']}} {{$c['ar']}} — {{$c['en']}}</option>
 @endforeach
</select>
<label class="check-row"><input type="hidden" name="sound_enabled" value="0"><input type="checkbox" name="sound_enabled" value="1" {{auth()->user()->profile?->sound_enabled!==false?'checked':''}}> تشغيل أصوات اللعبة والإشعارات والرسائل</label>
<label>ثيم الموقع</label>
<select name="active_site_theme" onchange="window.setSiteTheme?.(this.value);fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme:this.value})}).catch(()=>{});WarqnaSound?.ui()">
 @foreach(['royal'=>'ملكي ذهبي','midnight'=>'ليلي أزرق','emerald'=>'زمردي فاخر','desert'=>'صحراوي دافئ','galaxy'=>'مجرة نيون','crimson'=>'قرمزي فاخر','ocean'=>'محيطي هادئ','obsidian'=>'أسود زجاجي','aurora'=>'شفق قطبي'] as $key=>$label)
  <option value="{{$key}}" {{(auth()->user()->profile?->active_site_theme ?? 'royal')===$key?'selected':''}}>{{$label}}</option>
 @endforeach
</select>
<div class="theme-preview-row"><span class="theme-dot royal">ملكي</span><span class="theme-dot midnight">ليلي</span><span class="theme-dot emerald">زمردي</span><span class="theme-dot desert">صحراوي</span><span class="theme-dot galaxy">مجرة</span><span class="theme-dot" style="background:linear-gradient(135deg,#fb7185,#7f1d1d)">قرمزي</span><span class="theme-dot" style="background:linear-gradient(135deg,#22d3ee,#0e7490)">محيط</span><span class="theme-dot" style="background:linear-gradient(135deg,#020617,#64748b)">أسود</span><span class="theme-dot" style="background:linear-gradient(135deg,#34d399,#a78bfa)">شفق</span></div>
<button class="primary">حفظ</button></form>
<div class="pro-card"><h3>تكبير الخط</h3><p>تحكم سريع ومريح بحجم الخط في كل الموقع.</p><button onclick="changeFont(1);WarqnaSound?.ui()">تكبير قوي</button><button onclick="changeFont(-1);WarqnaSound?.ui()">تصغير</button></div>
<div class="pro-card"><h3>الأصوات</h3><p>تحكم كامل بالصوت من 0 إلى 100. استخدم الماوس أو أسهم الكيبورد ↑ ↓.</p><label class="sound-settings-range">مستوى الصوت <input id="settingsSoundRange" type="range" min="0" max="100" step="1" value="80"></label><div class="sound-test-row"><button type="button" onclick="WarqnaSound?.toggleMute()">كتم/تشغيل</button><button type="button" onclick="WarqnaSound?.play('win')">تجربة صوت الفوز</button><button type="button" onclick="WarqnaSound?.play('message')">تجربة صوت رسالة</button></div></div>
</div>
@endsection
