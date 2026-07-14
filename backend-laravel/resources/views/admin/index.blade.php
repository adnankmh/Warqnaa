@extends('layouts.app')
@section('content')
@php
$val = fn($key,$default=null)=> optional($siteSettings[$key] ?? null)->value ?? $default;
$bool = fn($key,$default=true)=> filter_var(optional($siteSettings[$key] ?? null)->value ?? ($default?'1':'0'), FILTER_VALIDATE_BOOLEAN);
$tableAdminTiers=['all'=>'الكل','beginner'=>'مبتدئ','medium'=>'متوسط','featured'=>'مميز','pro'=>'احترافي','legendary'=>'أسطوري','animated'=>'متحركة'];
$emojiAdminTiers=['all'=>'الكل','free'=>'مجاني','laugh'=>'ضحك','happy'=>'فرح','angry'=>'عصبية','sad'=>'حزن','vip'=>'VIP','animated'=>'متحرك'];
$rangeDesignerFields=[
 'ui_button_width'=>['label'=>'عرض الأزرار','min'=>70,'max'=>360,'default'=>126,'unit'=>'px'],
 'ui_button_height'=>['label'=>'ارتفاع الأزرار','min'=>32,'max'=>96,'default'=>46,'unit'=>'px'],
 'ui_button_radius'=>['label'=>'استدارة الأزرار','min'=>0,'max'=>44,'default'=>16,'unit'=>'px'],
 'ui_button_font'=>['label'=>'حجم خط الأزرار','min'=>10,'max'=>24,'default'=>14,'unit'=>'px'],
 'ui_button_gap'=>['label'=>'المسافة بين الأزرار','min'=>2,'max'=>28,'default'=>8,'unit'=>'px'],
 'ui_card_radius'=>['label'=>'استدارة البطاقات','min'=>4,'max'=>54,'default'=>24,'unit'=>'px'],
 'ui_card_padding'=>['label'=>'حشو البطاقات','min'=>8,'max'=>44,'default'=>18,'unit'=>'px'],
 'ui_card_gap'=>['label'=>'المسافة بين البطاقات','min'=>6,'max'=>34,'default'=>16,'unit'=>'px'],
 'ui_card_min_height'=>['label'=>'ارتفاع بطاقة المتجر/الألعاب','min'=>120,'max'=>460,'default'=>220,'unit'=>'px'],
 'ui_page_padding'=>['label'=>'هوامش الصفحات','min'=>6,'max'=>36,'default'=>18,'unit'=>'px'],
 'ui_page_max_width'=>['label'=>'أقصى عرض للصفحة','min'=>960,'max'=>1920,'default'=>1500,'unit'=>'px'],
 'ui_nav_height'=>['label'=>'ارتفاع النافبار','min'=>42,'max'=>96,'default'=>60,'unit'=>'px'],
 'ui_nav_radius'=>['label'=>'استدارة النافبار','min'=>0,'max'=>34,'default'=>16,'unit'=>'px'],
 'ui_store_card_width'=>['label'=>'عرض مربع المتجر','min'=>140,'max'=>420,'default'=>220,'unit'=>'px'],
 'ui_store_card_height'=>['label'=>'ارتفاع مربع المتجر','min'=>150,'max'=>520,'default'=>270,'unit'=>'px'],
 'ui_store_icon_size'=>['label'=>'حجم أيقونة المتجر','min'=>32,'max'=>150,'default'=>72,'unit'=>'px'],
 'ui_game_card_width'=>['label'=>'عرض بطاقة اللعبة','min'=>150,'max'=>440,'default'=>230,'unit'=>'px'],
 'ui_game_card_height'=>['label'=>'ارتفاع بطاقة اللعبة','min'=>140,'max'=>420,'default'=>230,'unit'=>'px'],
 'ui_game_icon_size'=>['label'=>'حجم أيقونة اللعبة','min'=>30,'max'=>130,'default'=>64,'unit'=>'px'],
 'ui_table_radius'=>['label'=>'استدارة الطاولة','min'=>16,'max'=>96,'default'=>46,'unit'=>'px'],
 'ui_table_border'=>['label'=>'عرض حافة الطاولة','min'=>4,'max'=>34,'default'=>16,'unit'=>'px'],
 'ui_table_min_height'=>['label'=>'ارتفاع الطاولة داخل الغرفة','min'=>420,'max'=>980,'default'=>610,'unit'=>'px'],
 'ui_table_center_scale'=>['label'=>'حجم مركز الطاولة','min'=>60,'max'=>130,'default'=>92,'unit'=>'%'],
 'ui_card_play_width'=>['label'=>'عرض ورق اللعب','min'=>34,'max'=>100,'default'=>58,'unit'=>'px'],
 'ui_card_play_height'=>['label'=>'ارتفاع ورق اللعب','min'=>48,'max'=>145,'default'=>82,'unit'=>'px'],
 'ui_player_avatar'=>['label'=>'حجم صورة اللاعب','min'=>34,'max'=>96,'default'=>56,'unit'=>'px'],
 'ui_chat_width'=>['label'=>'عرض الدردشة','min'=>240,'max'=>720,'default'=>340,'unit'=>'px'],
 'ui_chat_height'=>['label'=>'ارتفاع الدردشة','min'=>300,'max'=>820,'default'=>560,'unit'=>'px'],
 'ui_chat_radius'=>['label'=>'استدارة نافذة الدردشة','min'=>0,'max'=>42,'default'=>24,'unit'=>'px'],
 'ui_chat_font'=>['label'=>'حجم خط الدردشة','min'=>10,'max'=>22,'default'=>14,'unit'=>'px'],
 'ui_chat_button_width'=>['label'=>'عرض أزرار الدردشة','min'=>34,'max'=>220,'default'=>82,'unit'=>'px'],
 'ui_chat_button_height'=>['label'=>'ارتفاع أزرار الدردشة','min'=>28,'max'=>76,'default'=>40,'unit'=>'px'],
 'ui_chat_button_radius'=>['label'=>'استدارة أزرار الدردشة','min'=>0,'max'=>34,'default'=>14,'unit'=>'px'],
 'ui_chat_input_height'=>['label'=>'ارتفاع خانة كتابة الدردشة','min'=>30,'max'=>72,'default'=>44,'unit'=>'px'],
 'ui_chat_emoji_size'=>['label'=>'حجم إيموجز الدردشة','min'=>20,'max'=>86,'default'=>34,'unit'=>'px'],
 'ui_chat_gap'=>['label'=>'المسافات داخل الدردشة','min'=>2,'max'=>22,'default'=>8,'unit'=>'px'],
 'ui_notif_width'=>['label'=>'عرض قائمة الإشعارات','min'=>260,'max'=>720,'default'=>420,'unit'=>'px'],
 'ui_profile_width'=>['label'=>'عرض نافذة البروفايل','min'=>320,'max'=>840,'default'=>560,'unit'=>'px'],
 'ui_profile_font'=>['label'=>'حجم خط البروفايل','min'=>10,'max'=>20,'default'=>13,'unit'=>'px'],
 'xp_no_pasha_per_round'=>['label'=>'XP لكل جولة بدون باشا','min'=>0,'max'=>1000,'default'=>10,'unit'=>' XP'],
 'xp_pasha_per_round'=>['label'=>'XP لكل جولة مع باشا','min'=>0,'max'=>2000,'default'=>20,'unit'=>' XP'],
 'exit_penalty_xp'=>['label'=>'خصم XP عند الخروج وحده','min'=>0,'max'=>5000,'default'=>200,'unit'=>' XP'],
];
$colorDesignerFields=[
 'ui_button_bg'=>'لون الأزرار العادية','ui_button_text'=>'لون نص الأزرار','ui_primary_bg'=>'لون الزر الأساسي 1','ui_primary_bg2'=>'لون الزر الأساسي 2','ui_panel_bg'=>'لون اللوحات','ui_card_bg'=>'لون البطاقات','ui_site_bg1'=>'خلفية الموقع 1','ui_site_bg2'=>'خلفية الموقع 2','ui_table_bg1'=>'لون الطاولة الداخلي','ui_table_bg2'=>'لون الطاولة الخارجي','ui_table_border_color'=>'لون حافة الطاولة','ui_store_price_color'=>'لون أسعار المتجر','ui_nav_bg'=>'لون النافبار','ui_chat_bg'=>'لون الدردشة','ui_chat_header_bg'=>'لون رأس الدردشة','ui_chat_button_bg'=>'لون أزرار الدردشة','ui_chat_button_text'=>'لون نص أزرار الدردشة','ui_chat_input_bg'=>'لون خانة كتابة الدردشة','ui_chat_message_bg'=>'لون رسائل الدردشة'
];
$colorDesignerDefaults=['ui_button_bg'=>'#2e225f','ui_button_text'=>'#ffffff','ui_primary_bg'=>'#facc15','ui_primary_bg2'=>'#ec4899','ui_panel_bg'=>'#0f172a','ui_card_bg'=>'#1e293b','ui_site_bg1'=>'#07170f','ui_site_bg2'=>'#020617','ui_table_bg1'=>'#16a34a','ui_table_bg2'=>'#064e3b','ui_table_border_color'=>'#5b3718','ui_store_price_color'=>'#facc15','ui_nav_bg'=>'#020617','ui_chat_bg'=>'#0f172a','ui_chat_header_bg'=>'#312e81','ui_chat_button_bg'=>'#2e225f','ui_chat_button_text'=>'#ffffff','ui_chat_input_bg'=>'#020617','ui_chat_message_bg'=>'#1e293b'];
$selectDesignerFields=[
 'ui_button_style'=>['label'=>'شكل الأزرار','options'=>['solid'=>'لون ثابت','gradient'=>'تدرج فاخر','glass'=>'زجاجي','outline'=>'إطار فقط'],'default'=>'gradient'],
 'ui_card_shadow'=>['label'=>'ظل البطاقات','options'=>['soft'=>'ناعم','medium'=>'متوسط','strong'=>'قوي','none'=>'بدون ظل'],'default'=>'medium'],
 'ui_table_shape'=>['label'=>'شكل الطاولة','options'=>['rounded'=>'مستديرة ناعمة','stadium'=>'بيضاوية فخمة','square-soft'=>'مربعة بحواف منحنية'],'default'=>'rounded'],
 'ui_store_layout_mode'=>['label'=>'طريقة عرض المتجر','options'=>['grid'=>'شبكة','compact'=>'مضغوط','showcase'=>'معاينة كبيرة'],'default'=>'grid'],
 'ui_animation_level'=>['label'=>'الحركة والتأثيرات','options'=>['none'=>'بدون','soft'=>'ناعمة','premium'=>'فخمة'],'default'=>'soft'],
];
$designerBooleans=['single_activity_lock_enabled'=>'منع اللاعب من الاشتراك بأكثر من لعبة/مسابقة/نادي بنفس الوقت','room_owner_password_invites'=>'إرسال كلمة سر الغرفة الخاصة مع دعوات الأصدقاء','pasha_kick_dropdown_enabled'=>'إظهار الطرد لصاحب الغرفة مع باشا كقائمة منسدلة','exit_penalty_dropdown_enabled'=>'إظهار خصم XP عند الخروج كقائمة منسدلة','autoplay_timeout_enabled'=>'تشغيل الكمبيوتر تلقائيًا عند انتهاء عداد الدور','admin_live_preview_enabled'=>'تفعيل المعاينة المباشرة في لوحة الإدارة'];
@endphp
<h1>لوحة الإدارة والتحكم بالموقع</h1>
<div class="admin-v133-live-strip"><b>v133 Admin Pro</b><span>الغرف والنوادي والمسابقات واللاعبون والمتجر في تبويبات مباشرة.</span><button type="button" onclick="document.querySelector('[data-admin-tab=players]')?.click()">إدارة اللاعبين</button><button type="button" onclick="document.querySelector('[data-admin-tab=store]')?.click()">إدارة المتجر</button></div>
<div class="stats"><div>الغرف {{$rooms}}</div><div>النوادي {{$clubs}}</div><div>المسابقات {{$tournaments}}</div><div>المقتنيات {{$storeItems->count()}}</div></div>
<div class="admin-tabs jumbo-tabs">
 <button data-admin-tab="control">تحكم الموقع</button><button data-admin-tab="designer">مصمم شامل</button><button data-admin-tab="games-admin">الألعاب والقوانين</button><button data-admin-tab="pro-health">صحة النظام والخطة</button><button data-admin-tab="monitor">مراقبة مباشرة</button><button data-admin-tab="economy">المواسم والاقتصاد</button><button data-admin-tab="v118">منصة V118</button><button data-admin-tab="builder">مصمم الموقع الشامل</button><button data-admin-tab="store">إدارة المتجر</button><button data-admin-tab="players">كل اللاعبين</button><button data-admin-tab="rooms">الغرف المفتوحة</button><button data-admin-tab="clubs">النوادي</button><button data-admin-tab="tournaments">المسابقات</button><button data-admin-tab="security">الحماية</button><button data-admin-tab="support">رسائل الدعم</button>
</div>

<section id="admin-v118" class="admin-section">
 <h2>🔥 مركز منصة V118 الجذرية</h2>
 <div class="v118-admin-actions">
  <a class="btn primary" href="{{ route('admin.pro.v118') }}" target="_blank">فتح JSON المراقبة المتقدمة</a><a class="btn primary" href="{{ route('admin.engine.audit') }}" target="_blank">🛡️ فحص محركات v124</a>
  <a class="btn" href="{{ route('games.library.pro') }}" target="_blank">مكتبة الألعاب Pro</a>
  <a class="btn" href="{{ route('rewards') }}" target="_blank">المكافآت اليومية</a>
 </div>
 <div class="v118-admin-grid">
  <div class="pro-card"><b>الألعاب</b><span>15+</span><small>طرنيب، هاند، تريكس، بلوت، استيميشن، دومينو، لودو، جاكارو والمزيد.</small></div>
  <div class="pro-card"><b>الحماية</b><span>Server</span><small>السيرفر هو الحكم، لا إرسال لأوراق الخصوم، وتسجيل الحركات المشبوهة.</small></div>
  <div class="pro-card"><b>الاقتصاد</b><span>مواسم</span><small>عملات، توكنز، جواهر، عروض، مقتنيات نادرة، مكافآت يومية.</small></div>
  <div class="pro-card"><b>التخصيص</b><span>7 لغات</span><small>ثيمات فخمة، خطوط متعددة، RTL/LTR، PWA وتخطيط للموبايل.</small></div>
 </div>
</section>

<section id="admin-economy" class="admin-section">
 <h2>💎 إدارة المواسم والعروض والمقتنيات النادرة</h2>
 <div class="admin-economy-grid">
  <form class="pro-card" method="post" action="{{ route('admin.economy.season') }}">@csrf
   <h3>إنشاء/تعديل موسم</h3>
   <label>Key<input name="key" value="season_royal_launch"></label>
   <label>اسم الموسم بالعربي<input name="name_ar" value="موسم ملكي جديد"></label>
   <label>البداية<input type="date" name="starts_at"></label>
   <label>النهاية<input type="date" name="ends_at"></label>
   <label class="check-row"><input type="checkbox" name="active" value="1" checked> مفعل</label>
   <button class="primary">حفظ الموسم</button>
  </form>
  <form class="pro-card" method="post" action="{{ route('admin.economy.offer') }}">@csrf
   <h3>إنشاء/تعديل عرض</h3>
   <label>Key<input name="key" value="offer_royal_week"></label>
   <label>عنوان العرض<input name="title_ar" value="عرض الأسبوع الملكي"></label>
   <label>نسبة الخصم<input type="number" name="discount_percent" min="0" max="95" value="25"></label>
   <label class="check-row"><input type="checkbox" name="active" value="1" checked> مفعل</label>
   <button class="primary">حفظ العرض</button>
  </form>
  <form class="pro-card" method="post" action="{{ route('admin.economy.rare') }}">@csrf
   <h3>مقتنى نادر</h3>
   <label>Key<input name="key" value="rare_legend_frame"></label>
   <label>اسم المقتنى<input name="name_ar" value="إطار الأسطورة"></label>
   <label>الندرة<select name="rarity"><option value="rare">Rare</option><option value="epic">Epic</option><option value="legendary">Legendary</option><option value="mythic">Mythic</option></select></label>
   <label>العدد المتاح<input type="number" name="supply" value="500"></label>
   <label class="check-row"><input type="checkbox" name="active" value="1" checked> مفعل</label>
   <button class="primary">حفظ المقتنى</button>
  </form>
 </div>
</section>

<section id="admin-monitor" class="admin-section">
 <h2>📡 مراقبة مباشرة للموقع</h2>
 <div class="monitor-actions"><button type="button" class="primary" onclick="loadAdminSnapshot()">تحديث الآن</button><a class="btn" href="{{ route('warqna.health') }}" target="_blank">Health JSON</a><a class="btn" href="{{ url('/sitemap.xml') }}" target="_blank">Sitemap</a></div>
 <div id="adminSnapshot" class="admin-snapshot-grid">
  <div class="pro-card">اضغط تحديث الآن لعرض حالة النظام.</div>
 </div>
</section>

<section id="admin-pro-health" class="admin-section">
 <h2>🚀 صحة النظام وخطة الاحتراف</h2>
 <div class="pro-health-grid">
  <div class="pro-card"><b>إصدار التطوير</b><span>{{ config('warqna_pro_features.version','v115') }}</span><small>النسخة الحالية مجهزة لمرحلة احترافية.</small></div>
  <div class="pro-card"><b>PWA</b><span>مفعل</span><small>manifest + service worker للتثبيت كتطبيق.</small></div>
  <div class="pro-card"><b>SEO</b><span>مفعل</span><small>robots.txt + sitemap.xml + structured data.</small></div>
  <div class="pro-card"><b>الأداء</b><span>فهارس جاهزة</span><small>Migration للفهرسة على الرسائل والإشعارات والغرف.</small></div>
 </div>
 <div class="pro-card">
  <h3>الخطوات القادمة المعتمدة</h3>
  <ol class="admin-roadmap-list">
   <li>إنشاء اختبارات تلقائية لمحركات الألعاب.</li>
   <li>فصل كل لعبة إلى محرك كامل بقواعدها.</li>
   <li>تفعيل بث لحظي WebSocket للغرف والدردشة.</li>
   <li>إضافة نظام مراقبة أخطاء وتقارير أداء.</li>
   <li>إطلاق نسخة PWA ثم تطبيق موبايل.</li>
  </ol>
  <p class="muted">تمت إضافة ملف تفصيلي داخل المشروع: <b>ROADMAP_PRO_NEXT_STEPS_AR.md</b></p>
 </div>
</section>

<section id="admin-control" class="admin-section active">
 <h2>تحكم عام بدون أكواد</h2><div class="mini-card admin-designer-note">من هنا تستطيع تعديل الثيم، حالة الوحدات، نصوص الواجهة، شكل النافبار، CSS مخصص، وإدارة المتجر بدون تعديل ملفات.</div>
 <form class="admin-control-grid pro-card" method="post" action="{{route('admin.site.save')}}">@csrf
  <label>الثيم الافتراضي للموقع<select name="default_theme" onchange="window.setSiteTheme ? setSiteTheme(this.value) : document.body.classList.add('theme-'+this.value)">@foreach($themeOptions as $key=>$label)<option value="{{$key}}" {{$val('default_theme','royal')===$key?'selected':''}}>{{$label}}</option>@endforeach</select></label>
  <label class="check-row"><input type="checkbox" name="force_global_theme" value="1" {{$bool('force_global_theme',false)?'checked':''}}> فرض ثيم الإدارة على كل اللاعبين</label>
  <label class="check-row"><input type="checkbox" name="store_enabled" value="1" {{$bool('store_enabled',true)?'checked':''}}> تشغيل المتجر</label>
  <label class="check-row"><input type="checkbox" name="clubs_enabled" value="1" {{$bool('clubs_enabled',true)?'checked':''}}> تشغيل النوادي</label>
  <label class="check-row"><input type="checkbox" name="tournaments_enabled" value="1" {{$bool('tournaments_enabled',true)?'checked':''}}> تشغيل المسابقات</label>
  <label class="check-row"><input type="checkbox" name="chat_enabled" value="1" {{$bool('chat_enabled',true)?'checked':''}}> تشغيل الدردشة</label>
  <label class="check-row"><input type="checkbox" name="auto_start_game" value="1" {{$bool('auto_start_game',true)?'checked':''}}> بدء اللعبة مباشرة بدون نافذة تأكيد</label>
  <label class="check-row"><input type="checkbox" name="round_score_popup" value="1" {{$bool('round_score_popup',true)?'checked':''}}> إظهار نقاط الجولة الصغيرة تلقائيًا</label>
  <label class="check-row"><input type="checkbox" name="tarneeb_only_panel" value="1" {{$bool('tarneeb_only_panel',true)?'checked':''}}> في الطرنيب إظهار أزرار الطرنيب فقط وإخفاء أزرار باقي الألعاب</label>
  <label class="check-row"><input type="checkbox" name="large_bot_seats" value="1" {{$bool('large_bot_seats',true)?'checked':''}}> تكبير صور البوتات وإظهارهم خارج حدود الطاولة</label>
  <label>حجم ورق اللعب<select name="card_visual_size"><option value="normal">عادي</option><option value="large">كبير</option><option value="compact">مضغوط</option></select></label>
  <label>شكل إشعارات أعلى الشاشة<select name="notifications_style"><option value="panel">لوحة ثابتة بدون تمرير الصفحة</option><option value="compact">مضغوط</option><option value="wide">عريض</option></select></label>
  <label class="check-row"><input type="checkbox" name="table_uploads_enabled" value="1" {{$bool('table_uploads_enabled',true)?'checked':''}}> السماح برفع صور الطاولات من الإدارة</label>
  <label class="check-row"><input type="checkbox" name="card_back_uploads_enabled" value="1" {{$bool('card_back_uploads_enabled',true)?'checked':''}}> السماح برفع صور ظهر الورق من الإدارة</label>
  <label class="check-row"><input type="checkbox" name="support_enabled" value="1" {{$bool('support_enabled',true)?'checked':''}}> تشغيل الدعم</label>
  <label>عنوان الصفحة الرئيسية<input name="homepage_headline" value="{{$val('homepage_headline','Warqnaa')}}"></label>
  <label>رسالة عامة / صيانة<input name="maintenance_message" value="{{$val('maintenance_message','')}}"></label>
  <label>لغة الموقع الافتراضية<select name="default_locale"><option value="ar" {{$val('default_locale','ar')==='ar'?'selected':''}}>عربي</option><option value="en" {{$val('default_locale','ar')==='en'?'selected':''}}>English</option><option value="fr" {{$val('default_locale','ar')==='fr'?'selected':''}}>Français</option><option value="tr" {{$val('default_locale','ar')==='tr'?'selected':''}}>Türkçe</option><option value="de" {{$val('default_locale','ar')==='de'?'selected':''}}>Deutsch</option><option value="es" {{$val('default_locale','ar')==='es'?'selected':''}}>Español</option></select></label>
  <label>شكل التنقل<select name="nav_style"><option value="bar">شريط علوي</option><option value="glass">زجاجي فاخر</option><option value="side">جانبي مستقبلاً</option></select></label>
  <label>كثافة الواجهة<select name="layout_density"><option value="compact">مضغوط</option><option value="comfortable">مريح</option><option value="wide">واسع</option></select></label>
  <label>شكل البطاقات<select name="card_style"><option value="rounded">دائري ناعم</option><option value="luxury">فاخر</option><option value="flat">مسطح</option></select></label>
  <label>شكل المتجر<select name="store_layout"><option value="tabs">تبويبات</option><option value="cards">بطاقات</option><option value="admin_grid">شبكة إدارية</option></select></label>
  <button class="primary">حفظ إعدادات الموقع والهيكل</button>
 </form>
 <div class="theme-preview-row big-theme-row">@foreach($themeOptions as $key=>$label)<span class="theme-dot {{$key}}" onclick="window.setSiteTheme ? setSiteTheme('{{$key}}') : document.body.classList.add('theme-{{$key}}')">{{$label}}</span>@endforeach</div>
</section>


<section id="admin-builder" class="admin-section">
 <h2>🛠️ مركز التحكم بدون أكواد</h2>
 <div class="admin-builder-grid no-code-hub-v137">
  <div class="builder-card"><b>مصمم كل الواجهة</b><p>غيّر عرض/ارتفاع/لون/حواف الأزرار والبطاقات والطاولات والمتجر والدردشة من منزلقات وألوان.</p><button type="button" onclick="document.querySelector('[data-admin-tab=designer]')?.click()">فتح المصمم الشامل</button></div>
  <div class="builder-card"><b>الألعاب والقوانين</b><p>تفعيل/تعطيل أي لعبة، تعديل اسمها، عدد اللاعبين، المحرك، القوانين بالعربي والإنجليزي و JSON القواعد.</p><button type="button" onclick="document.querySelector('[data-admin-tab=games-admin]')?.click()">إدارة الألعاب</button></div>
  <div class="builder-card"><b>المتجر والأسعار</b><p>إضافة، تعديل، إخفاء، تغيير سعر، مدة، لون، أيقونة، مسرّع، طاولة أو ظهر ورق مع معاينة.</p><button type="button" onclick="document.querySelector('[data-admin-tab=store]')?.click()">إدارة المتجر</button></div>
  <div class="builder-card"><b>اللاعبون والنوادي والمنافسات</b><p>إدارة الحسابات، التوكنز، الحظر، الغرف، النوادي، المنافسات، الرسائل والحماية من واجهات مباشرة.</p><button type="button" onclick="document.querySelector('[data-admin-tab=players]')?.click()">إدارة اللاعبين</button></div>
 </div>
</section>

<section id="admin-designer" class="admin-section">
 <h2>🎛️ المصمم الشامل للموقع بالكامل</h2>
 <p class="muted">كل القيم هنا تُحفظ في قاعدة البيانات وتُطبق على الواجهة، الألعاب، المتجر، الطاولات، الدردشة، الإشعارات والبروفايل بدون كتابة أي كود.</p>
 <div class="pro-card designer-entity-manager-v173">
  <h3>🧠 مدير الكيانات الشامل V173</h3>
  <p class="muted">من هنا يستطيع المدير إضافة أو تعديل أو حذف أي إعداد ديناميكي: طاولة، طربوش، تذكرة، حزمة، تحدٍّ، منافسة، مجموعة، إعلان، نص، ترجمة، ثيم أو إعداد نظام. التعديلات تُحفظ في قاعدة البيانات وتظهر في تطبيق Flutter عبر المزامنة.</p>
  <form method="post" action="{{ route('admin.designer.entity.save') }}" class="admin-control-grid">@csrf
   <label>نوع العنصر<select name="entity_type" required>
    @foreach(['table'=>'طاولة','pasha_style'=>'لون طربوش','competition_ticket'=>'تذكرة منافسة','daily_pack'=>'حزمة يومية','challenge'=>'تحدٍّ','competition'=>'منافسة','group'=>'مجموعة','ads'=>'إعلانات','translation'=>'نص/ترجمة','theme'=>'ثيم','system'=>'إعداد نظام','store'=>'متجر'] as $type=>$label)<option value="{{$type}}">{{$label}}</option>@endforeach
   </select></label>
   <label>المفتاح الفريد<input name="key" required placeholder="مثال: royal_table_51" pattern="[A-Za-z0-9_.:-]+"></label>
   <label>اللغة<input name="locale" value="all" maxlength="10"></label>
   <label>ترتيب الظهور<input name="sort_order" type="number" min="0" value="0"></label>
   <label class="check-row"><input type="checkbox" name="active" value="1" checked> مفعّل ومنشور</label>
   <label style="grid-column:1/-1">بيانات العنصر بصيغة JSON<textarea name="payload_json" rows="7" required>{"name":{"ar":"عنصر جديد","en":"New item"},"enabled":true}</textarea></label>
   <button class="primary">إضافة / تحديث ونشر العنصر</button>
  </form>
  <div class="designer-entity-list-v173" style="display:grid;gap:12px;margin-top:16px">
   @forelse($designerEntities as $entity)
    <div class="mini-card" style="border:1px solid rgba(250,204,21,.22)">
     <form method="post" action="{{ route('admin.designer.entity.save') }}" class="admin-control-grid">@csrf
      <input type="hidden" name="entity_type" value="{{$entity->entity_type}}"><input type="hidden" name="key" value="{{$entity->key}}">
      <label>النوع<input value="{{$entity->entity_type}}" disabled></label>
      <label>المفتاح<input value="{{$entity->key}}" disabled></label>
      <label>اللغة<input name="locale" value="{{$entity->locale}}"></label>
      <label>الترتيب<input name="sort_order" type="number" min="0" value="{{$entity->sort_order}}"></label>
      <label class="check-row"><input type="checkbox" name="active" value="1" {{$entity->active?'checked':''}}> مفعّل</label>
      <label style="grid-column:1/-1">JSON<textarea name="payload_json" rows="6" required>{{ json_encode($entity->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</textarea></label>
      <div style="display:flex;gap:8px;flex-wrap:wrap"><button class="primary">حفظ ونشر — مراجعة {{$entity->revision}}</button></div>
     </form>
     <form method="post" action="{{ route('admin.designer.entity.delete',$entity) }}" onsubmit="return confirm('حذف هذا العنصر نهائيًا؟')">@csrf<button class="danger" style="margin-top:8px">حذف العنصر</button></form>
    </div>
   @empty
    <div class="mini-card">لا توجد كيانات بعد. أضف أول عنصر من النموذج أعلاه.</div>
   @endforelse
  </div>
 </div>
 <form class="designer-shell-v137" method="post" action="{{ route('admin.design.save') }}">@csrf
  <div class="designer-fields-v137">
   <div class="designer-block-v137"><h3>📐 الأحجام والمسافات</h3>
    @foreach($rangeDesignerFields as $key=>$field)
     @php $current=$val($key,$field['default']); @endphp
     <label class="designer-range-v137"><span>{{$field['label']}}</span><input type="range" name="{{$key}}" min="{{$field['min']}}" max="{{$field['max']}}" value="{{$current}}" data-unit="{{$field['unit']}}" oninput="adminDesignerLive(this)"><output>{{$current}}{{$field['unit']}}</output></label>
    @endforeach
   </div>
   <div class="designer-block-v137"><h3>🎨 الألوان</h3>
    <div class="designer-color-grid-v137">
    @foreach($colorDesignerFields as $key=>$label)
     <label><span>{{$label}}</span><input type="color" name="{{$key}}" value="{{$val($key, $colorDesignerDefaults[$key] ?? '#facc15')}}" oninput="adminDesignerLive(this)"></label>
    @endforeach
    </div>
   </div>
   <div class="designer-block-v137"><h3>🧩 الأشكال والقواعد</h3>
    <div class="designer-select-grid-v137">
    @foreach($selectDesignerFields as $key=>$field)
     <label>{{$field['label']}}<select name="{{$key}}">@foreach($field['options'] as $opt=>$txt)<option value="{{$opt}}" {{$val($key,$field['default'])===$opt?'selected':''}}>{{$txt}}</option>@endforeach</select></label>
    @endforeach
    </div>
    <div class="designer-check-grid-v137">
    @foreach($designerBooleans as $key=>$label)
     <label class="check-row"><input type="checkbox" name="{{$key}}" value="1" {{$bool($key,true)?'checked':''}}> {{$label}}</label>
    @endforeach
    </div>
    <button class="primary big-save-v137">حفظ وتطبيق على كل الموقع</button>
   </div>
  </div>
  <aside class="designer-preview-v137">
   <h3>معاينة مباشرة</h3>
   <div class="admin-live-surface-v137">
    <button type="button" class="primary">زر أساسي</button><button type="button">زر عادي</button>
    <div class="store-card deluxe demo-card-v137"><span class="shop-icon">👑</span><b>عنصر متجر</b><p>السعر: <span class="admin-demo-price-v137">2500</span></p></div>
    <div class="demo-table-v137"><span>🂡 🂱 🃁</span><small>طاولة اللعبة</small></div>
    <div class="demo-player-v137"><img src="/assets/avatars/default.svg"><b>اسم اللاعب</b></div>
    <div class="demo-chat-v138">
     <div class="demo-chat-head-v138">💬 مركز الدردشة <span>— ×</span></div>
     <div class="demo-chat-tabs-v138"><button type="button">دردشة اللعبة</button><button type="button">الأصدقاء</button><button type="button">بحث</button></div>
     <div class="demo-chat-body-v138"><p>رسالة لاعب تجريبية</p><p class="me">رسالتي في الدردشة</p></div>
     <div class="demo-chat-send-v138"><input value="اكتب رسالة"><button type="button">إرسال</button></div>
     <div class="demo-chat-emojis-v138"><span>😂</span><span>🔥</span><span>👑</span></div>
    </div>
   </div>
  </aside>
 </form>
</section>

<section id="admin-games-admin" class="admin-section">
 <h2>🎮 إدارة الألعاب والقوانين والمحركات</h2>
 <p class="muted">من هنا تستطيع التحكم في كل لعبة: ظهورها، اسمها، عدد اللاعبين، المحرك، الأيقونة، قوانين القراءة، و JSON القواعد دون تعديل ملفات.</p>
 <form class="pro-card admin-create-game-v137" method="post" action="{{ route('admin.games.create') }}">@csrf
  <h3>إضافة لعبة جديدة</h3>
  <div class="admin-game-form-grid-v137"><input name="key" placeholder="game_key"><input name="name_ar" placeholder="اسم اللعبة بالعربي" required><input name="name_en" placeholder="English name"><input type="number" name="min_players" value="2"><input type="number" name="max_players" value="4"><input name="icon" value="🃏"><input name="family" value="cards"><input name="engine" value="UniversalSocialGameRules"><label class="check-row"><input type="checkbox" name="partnership" value="1"> شراكة</label><label class="check-row"><input type="checkbox" name="active" value="1" checked> مفعلة</label></div>
  <textarea name="rules_ar" placeholder="قوانين اللعبة بالعربي"></textarea><textarea name="rules_en" placeholder="Rules in English"></textarea><button class="primary">إضافة اللعبة</button>
 </form>
 <div class="admin-games-grid-v137">
 @foreach($games as $g)
  @php $rules=$g->rules ?: []; @endphp
  <form class="admin-game-card-v137" method="post" action="{{ route('admin.games.update',$g) }}">@csrf
   <div class="admin-game-head-v137"><span>{{$rules['icon'] ?? game_icon($g->key)}}</span><b>{{$g->name['ar'] ?? $g->key}}</b><small>{{$g->key}}</small></div>
   <div class="admin-game-form-grid-v137"><label>الاسم العربي<input name="name_ar" value="{{$g->name['ar'] ?? $g->key}}"></label><label>English<input name="name_en" value="{{$g->name['en'] ?? ''}}"></label><label>أقل لاعبين<input type="number" name="min_players" value="{{$g->min_players}}"></label><label>أكثر لاعبين<input type="number" name="max_players" value="{{$g->max_players}}"></label><label>أيقونة<input name="icon" value="{{$rules['icon'] ?? game_icon($g->key)}}"></label><label>العائلة<input name="family" value="{{$rules['family'] ?? 'cards'}}"></label><label>المحرك<input name="engine" value="{{$rules['engine'] ?? ''}}"></label><label>XP بدون باشا<input type="number" name="xp_no_pasha" value="{{$rules['xp_no_pasha'] ?? $val('xp_no_pasha_per_round',10)}}"></label><label>XP مع باشا<input type="number" name="xp_pasha" value="{{$rules['xp_pasha'] ?? $val('xp_pasha_per_round',20)}}"></label><label>خصم الخروج<input type="number" name="exit_penalty" value="{{$rules['exit_penalty'] ?? $val('exit_penalty_xp',200)}}"></label><label class="check-row"><input type="checkbox" name="partnership" value="1" {{$g->partnership?'checked':''}}> شراكة</label><label class="check-row"><input type="checkbox" name="active" value="1" {{$g->active?'checked':''}}> مفعلة</label></div>
   <label>قوانين اللعبة بالعربي<textarea name="rules_ar">{{$rules['rules_ar'] ?? ($rules['description_ar'] ?? '')}}</textarea></label>
   <label>Rules in English<textarea name="rules_en">{{$rules['rules_en'] ?? ($rules['description_en'] ?? '')}}</textarea></label>
   <label>JSON متقدم للقوانين<textarea name="rules_json" class="code-textarea-v137">{{ json_encode($rules, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</textarea></label>
   <button class="primary">حفظ اللعبة والقوانين</button>
  </form>
 @endforeach
 </div>
</section>

<section id="admin-store" class="admin-section">
 <h2>إدارة المتجر والمقتنيات</h2>
 <p class="muted">يمكنك تعديل الاسم، السعر، المدة، التبويب، المستوى، الأيقونة، والكلاس البصري لأي مقتنى بدون فتح الملفات.</p>
 <div class="store-admin-create pro-card"><h3>إضافة مقتنى جديد</h3><div class="admin-live-preview"><div class="preview-surface"><div class="preview-card-back">🂠</div></div><div><b>معاينة مباشرة</b><p>ارفع صورة للطاولة أو ظهر الورق، أو اكتب كلاس بصري/أيقونة، وستظهر للمستخدمين بعد التفعيل.</p></div></div><form method="post" enctype="multipart/form-data" action="{{route('admin.store.create')}}">@csrf <div class="admin-store-form compact"><input name="key" placeholder="مفتاح اختياري مثل table_gold_new"><select name="category">@foreach($categoryLabels as $k=>$label)<option value="{{$k}}">{{$label}}</option>@endforeach</select><input name="name_ar" required placeholder="الاسم العربي"><input name="name_en" placeholder="English name"><input name="price" type="number" value="0"><input name="duration_days" type="number" placeholder="المدة بالأيام أو اتركها فارغة"><input name="tab" placeholder="تبويب: مبتدئ/أسطوري/متحرك"><input name="tier" placeholder="مستوى: beginner/pro/legendary"><input name="preview_icon" placeholder="أيقونة مثل 👑"><input name="css_class" placeholder="كلاس بصري مثل table-ultra-eagle"><input name="color" placeholder="#facc15"><input name="multiplier" placeholder="XP x مثل 2"><input name="emojis" placeholder="😄😂🔥"><input type="file" name="asset" accept="image/*"><label class="check-row"><input type="checkbox" name="active" value="1" checked> ظاهر في المتجر</label><button class="primary">إضافة</button></div></form></div>
 <div class="admin-designer-note mini-card">💡 يمكنك إدارة الموقع والمتجر من هنا: إظهار/إخفاء، سعر، مدة، تبويب، لون، أيقونة، كلاس بصري ومعاينة مباشرة بدون فتح الأكواد.</div><div class="admin-store-tabs">@foreach($categoryLabels as $cat=>$label)<button data-store-admin-tab="{{$cat}}">{{$label}}</button>@endforeach</div>
 @foreach($categoryLabels as $cat=>$label)
  <div id="admin-store-{{$cat}}" class="admin-store-section"><h3>{{$label}}</h3>
  @if($cat==='table')<div class="sub-tabs admin-table-tier-tabs">@foreach($tableAdminTiers as $tk=>$tl)<button type="button" data-admin-tier-filter="{{$tk}}" onclick="filterAdminStoreTier(this, '{{$tk}}')">{{$tl}}</button>@endforeach</div>@endif
  @if($cat==='emoji_pack')<div class="sub-tabs admin-emoji-tier-tabs">@foreach($emojiAdminTiers as $ek=>$el)<button type="button" data-admin-emoji-filter="{{$ek}}" onclick="filterAdminEmojiTier(this, '{{$ek}}')">{{$el}}</button>@endforeach</div>@endif
  @if(in_array($cat,['text_color','name_color']))<div class="sub-tabs"><button type="button" class="active">الكل</button><button type="button">ألوان عادية</button><button type="button">Glow</button><button type="button">VIP</button></div>@endif
  @forelse(($storeGroups[$cat] ?? collect()) as $item)
   @php $payload=$item->payload ?: []; $rowTier=$payload['tier'] ?? $payload['tab'] ?? 'none'; if($cat==='table' && $rowTier==='none'){ $css=$payload['table'] ?? $item->key; $rowTier=str_contains($css,'beginner')?'beginner':(str_contains($css,'medium')?'medium':(str_contains($css,'advanced')?'advanced':((str_contains($css,'legend')||str_contains($css,'mythic'))?'legendary':(str_contains($css,'animated')?'animated':'pro')))); } $rowEmoji=$payload['emoji_tier'] ?? (($item->price==0)?'free':'vip'); if($cat==='emoji_pack' && (($payload['animated']??false) || str_contains($item->key,'animated'))) $rowEmoji='animated'; @endphp
   <form class="store-admin-row" data-admin-category="{{$cat}}" data-admin-tier="{{$rowTier}}" data-admin-emoji-tier="{{$rowEmoji}}" method="post" enctype="multipart/form-data" action="{{route('admin.store.update',$item)}}">@csrf
    <div class="admin-item-preview admin-preview-{{$cat}}">
     @if($cat==='table')
      @if(!empty($payload['table_image']))<span class="table-preview custom-image" style="background-image:url('{{ $payload['table_image'] }}')"></span>@else<span class="table-preview {{$payload['table'] ?? ''}}"></span>@endif
      <em>معاينة طاولة فقط</em>
     @elseif($cat==='card_back')
      @if(!empty($payload['card_back_image']))<span class="card-back-preview custom-image" style="background-image:url('{{ $payload['card_back_image'] }}')">🂠</span>@else<span class="card-back-preview {{$payload['card_back'] ?? ''}}">🂠</span>@endif
      <em>معاينة ظهر الورق فقط</em>
     @elseif($cat==='text_color')
      <span class="text-color-preview admin-color-sample" style="--text-preview:{{$payload['color'] ?? '#fff'}};color:{{$payload['color'] ?? '#fff'}}"><b style="color:{{$payload['color'] ?? '#fff'}}">كلمة تجريبية</b></span>
      <em>لون الدردشة</em>
     @else
      <span class="generic-admin-icon">{{$payload['preview_icon'] ?? $payload['icon'] ?? '🎁'}}</span>
     @endif
     <small>{{$item->key}}</small><button type="button" onclick="adminPreviewStoreItem(this)">معاينة</button></div>
    <input name="name_ar" value="{{$item->name['ar'] ?? $item->key}}"><input name="name_en" value="{{$item->name['en'] ?? ''}}">
    <select name="category">@foreach($categoryLabels as $k=>$catLabel)<option value="{{$k}}" {{$item->category===$k?'selected':''}}>{{$catLabel}}</option>@endforeach</select>
    <input name="price" type="number" value="{{$item->price}}"><input name="duration_days" type="number" value="{{$item->duration_days}}" placeholder="دائم">
    <input name="tab" value="{{$payload['tab'] ?? ''}}" placeholder="تبويب"><input name="tier" value="{{$payload['tier'] ?? ''}}" placeholder="مستوى"><input name="preview_icon" value="{{$payload['preview_icon'] ?? $payload['icon'] ?? ''}}" placeholder="أيقونة">
    <input name="css_class" value="{{$payload['table'] ?? $payload['card_back'] ?? $payload['effect'] ?? $payload['badge'] ?? $payload['frame'] ?? $payload['css'] ?? ''}}" placeholder="كلاس بصري">
    <input name="color" value="{{$payload['color'] ?? ''}}" placeholder="لون"><input name="multiplier" value="{{$payload['multiplier'] ?? ''}}" placeholder="x"><input name="emojis" value="{{$payload['emojis'] ?? ''}}" placeholder="إيموجي"><input type="file" name="asset" accept="image/*">
    <label class="check-row"><input type="checkbox" name="active" value="1" {{$item->active?'checked':''}}> ظاهر</label>
    <button class="primary">حفظ</button>
   </form>
   <form class="inline-delete" method="post" action="{{route('admin.store.delete',$item)}}" data-confirm="سيتم إخفاء هذا المقتنى من المتجر. هل أنت متأكد؟">@csrf<button class="danger">إخفاء</button></form>
  @empty <p class="muted">لا توجد عناصر في هذا القسم.</p>@endforelse
  </div>
 @endforeach
</section>
<section id="admin-players" class="admin-section admin-players-v133">
 <h2>👥 كل اللاعبين</h2>
 <p class="muted">اضغط على صورة أو اسم اللاعب لفتح البروفايل، ويمكنك تعديل بياناته أو حذف الحساب أو إرسال توكنز مخفية أو إضافة صداقة.</p>
 <div class="admin-player-search-v133"><input oninput="filterAdminPlayersV133(this.value)" placeholder="ابحث عن لاعب..."></div>
 <div class="admin-players-grid-v133">
 @foreach($users as $u)
  <article class="admin-player-card-v133" data-admin-player-name="{{ strtolower($u->username.' '.($u->profile?->display_name ?? '')) }}">
   <button type="button" class="admin-player-head-v133" onclick="openProfile({{$u->id}})" style="--player-color:{{$u->profile?->name_color ?? '#facc15'}}">
    <img class="avatar-lg" src="{{$u->profile?->avatar ?: '/assets/avatars/default.svg'}}">
    <span><b>{{$u->profile?->display_name ?: $u->username}}</b><small>{!! flag_img($u->profile?->country_code) !!} {{$u->email}}</small></span>
   </button>
   <div class="admin-player-stats-v133">
    <span>Level {{$u->profile?->level ?? 1}}</span>
    <span>XP {{number_format($u->profile?->xp ?? 0)}}</span>
    <span>🪙 {{number_format($u->wallet?->tokens??0)}}</span>
    <span>{{$u->is_banned?'محظور':'نشط'}}</span>
   </div>
   <form class="admin-player-edit-v133" method="post" action="{{route('admin.users.action',$u)}}">
    @csrf
    <input type="hidden" name="action" value="update_profile">
    <input name="display_name" value="{{$u->profile?->display_name}}" placeholder="الاسم الظاهر">
    <input type="number" name="level" value="{{$u->profile?->level ?? 1}}" placeholder="Level">
    <input type="number" name="xp" value="{{$u->profile?->xp ?? 0}}" placeholder="XP">
    <input name="name_color" value="{{$u->profile?->name_color ?? '#facc15'}}" placeholder="لون الاسم">
    <button class="primary">حفظ تعديل اللاعب</button>
   </form>
   <div class="admin-player-actions-v133">
    <form method="post" action="{{route('admin.users.action',$u)}}" data-confirm="هل تريد تغيير حالة الحظر؟">@csrf<input type="hidden" name="action" value="{{$u->is_banned?'unban':'ban'}}"><button>{{$u->is_banned?'فك الحظر':'حظر'}}</button></form>
    <form method="post" action="{{route('admin.users.action',$u)}}" data-confirm="سيتم إرسال توكنز مخفية من الإدارة بدون ظهور اسم المرسل.">@csrf<input type="hidden" name="action" value="credit"><input name="amount" value="1000"><button>توكنز مخفية</button></form>
    <form method="post" action="{{route('admin.users.action',$u)}}">@csrf<input type="hidden" name="action" value="friend_request"><button>إضافة صداقة</button></form>
    @unless($u->is_admin)<form method="post" action="{{route('admin.users.action',$u)}}" data-confirm="سيتم حذف اللاعب نهائيًا.">@csrf<input type="hidden" name="action" value="delete"><button class="danger">حذف نهائي</button></form>@endunless
   </div>
  </article>
 @endforeach
 </div>
 {{$users->links()}}
</section>

<section id="admin-rooms" class="admin-section admin-live-rooms-v133"><h2>🎮 إدارة الغرف المفتوحة والشغالة</h2><p class="muted">تعديل حالة الغرفة، كلمة السر، عدد اللاعبين، الرسوم والمستوى مباشرة.</p><div class="admin-crud-grid-v137">@forelse($allRooms as $r)<div class="mini-card admin-crud-card-v137"><form method="post" action="{{ route('admin.rooms.update',$r) }}">@csrf<h3>{{$r->game?->name['ar'] ?? 'لعبة'}} — {{$r->code}}</h3><small>المالك: {{$r->owner?->username ?? 'بدون'}} • لاعبين {{$r->players->count()}}/{{$r->max_players}}</small><label>الحالة<select name="status">@foreach(['waiting'=>'انتظار','bidding'=>'طلب','playing'=>'لعب','finished'=>'منتهية','closed'=>'مغلقة'] as $st=>$txt)<option value="{{$st}}" {{$r->status===$st?'selected':''}}>{{$txt}}</option>@endforeach</select></label><label>الظهور<select name="visibility"><option value="public" {{$r->visibility==='public'?'selected':''}}>عام</option><option value="friends" {{$r->visibility==='friends'?'selected':''}}>أصدقاء</option><option value="private" {{$r->visibility==='private'?'selected':''}}>خاص</option></select></label><label>كلمة السر<input name="password" value="{{$r->password}}"></label><label>عدد اللاعبين<input type="number" name="max_players" value="{{$r->max_players}}"></label><label>رسوم الدخول<input type="number" name="entry_fee" value="{{$r->entry_fee ?? 0}}"></label><label>أقل مستوى<input type="number" name="min_level" value="{{$r->min_level ?? 1}}"></label><label>هدف النقاط<input name="target_score" value="{{$r->target_score}}"></label><button class="primary">حفظ الغرفة</button><a class="btn" href="{{ route('rooms.show',$r->code) }}">دخول/معاينة</a></form><form method="post" action="{{ route('admin.rooms.close',$r) }}" data-confirm="إغلاق الغرفة؟">@csrf<button class="danger">إغلاق الغرفة</button></form></div>@empty<p class="muted">لا توجد غرف.</p>@endforelse</div></section>
<section id="admin-clubs" class="admin-section admin-live-clubs-v133"><h2>🏛️ إدارة النوادي</h2><div class="admin-crud-grid-v137">@forelse($allClubs as $c)<div class="mini-card admin-crud-card-v137"><form method="post" action="{{ route('admin.clubs.update',$c) }}">@csrf<h3>{{$c->name}}</h3><small>المالك: {{$c->owner?->username}} • أعضاء {{$c->members->count()}}</small><label>اسم النادي<input name="name" value="{{$c->name}}"></label><label>المستوى<input type="number" name="level" value="{{$c->level}}"></label><label>نقاط الأسبوع<input type="number" name="weekly_points" value="{{$c->weekly_points}}"></label><label>الخزينة<input type="number" name="treasury" value="{{$c->treasury}}"></label><button class="primary">حفظ النادي</button></form><form method="post" action="{{ route('admin.clubs.delete',$c) }}" data-confirm="حذف النادي نهائيًا؟">@csrf<button class="danger">حذف النادي</button></form></div>@empty<p class="muted">لا توجد نوادٍ.</p>@endforelse</div></section>
<section id="admin-tournaments" class="admin-section admin-live-tournaments-v133"><h2>🏆 إدارة المنافسات</h2><form class="pro-card admin-create-game-v137" method="post" action="{{ route('admin.tournaments.create') }}">@csrf<h3>إنشاء منافسة جديدة</h3><div class="admin-game-form-grid-v137"><select name="game_id">@foreach($games as $g)<option value="{{$g->id}}">{{$g->name['ar'] ?? $g->key}}</option>@endforeach</select><input type="number" name="stages" value="1" placeholder="المراحل"><input type="number" name="seats_per_match" value="4" placeholder="المقاعد"><input type="number" name="entry_fee" value="0" placeholder="رسوم الدخول"><input type="number" name="prize_pool" value="0" placeholder="الجائزة"><select name="status"><option value="open">مفتوحة</option><option value="running">شغالة</option><option value="finished">منتهية</option><option value="cancelled">ملغية</option></select><button class="primary">إنشاء المنافسة</button></div></form><div class="admin-crud-grid-v137">@forelse($allTournaments as $t)<div class="mini-card admin-crud-card-v137"><form method="post" action="{{ route('admin.tournaments.update',$t) }}">@csrf<h3>{{$t->game?->name['ar'] ?? 'لعبة'}} #{{$t->id}}</h3><small>المسجلون {{$t->entries->count()}} • المنشئ {{$t->creator?->username}}</small><label>الحالة<select name="status">@foreach(['open'=>'مفتوحة','running'=>'شغالة','finished'=>'منتهية','cancelled'=>'ملغية'] as $st=>$txt)<option value="{{$st}}" {{$t->status===$st?'selected':''}}>{{$txt}}</option>@endforeach</select></label><label>المراحل<input type="number" name="stages" value="{{$t->stages}}"></label><label>مقاعد المباراة<input type="number" name="seats_per_match" value="{{$t->seats_per_match}}"></label><label>رسوم الدخول<input type="number" name="entry_fee" value="{{$t->entry_fee}}"></label><label>الجائزة<input type="number" name="prize_pool" value="{{$t->prize_pool}}"></label><button class="primary">حفظ المنافسة</button></form><form method="post" action="{{ route('admin.tournaments.delete',$t) }}" data-confirm="إلغاء المنافسة؟">@csrf<button class="danger">إلغاء</button></form></div>@empty<p class="muted">لا توجد منافسات.</p>@endforelse</div></section>
<section id="admin-security" class="admin-section"><h2>Anti-Cheat آخر التنبيهات</h2>@foreach($antiCheat as $e)<div class="mini-card">{{$e->event}} • severity {{$e->severity}} • user {{$e->user_id}} • room {{$e->room_id}}</div>@endforeach</section>
<section id="admin-support" class="admin-section"><h2>رسائل الدعم والاقتراحات</h2>@forelse($supportMessages as $m)<div class="mini-card"><b>{{$m->title['ar'] ?? 'رسالة دعم'}}</b><p>{{$m->body['ar'] ?? ''}}</p><small>{{$m->created_at}}</small></div>@empty<p class="muted">لا توجد رسائل دعم حاليًا.</p>@endforelse</section>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('[data-admin-tab]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-admin-tab]').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.querySelectorAll('.admin-section').forEach(s=>s.classList.toggle('active',s.id==='admin-'+b.dataset.adminTab));});
 document.querySelector('[data-admin-tab]')?.click();
 document.querySelectorAll('[data-store-admin-tab]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-store-admin-tab]').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.querySelectorAll('.admin-store-section').forEach(s=>s.classList.toggle('active',s.id==='admin-store-'+b.dataset.storeAdminTab));});
 document.querySelector('[data-store-admin-tab]')?.click();
 document.querySelectorAll('[data-admin-tier-filter]').forEach(b=>b.onclick=()=>{const v=b.dataset.adminTierFilter;b.closest('.admin-store-section')?.querySelectorAll('[data-admin-tier-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');b.closest('.admin-store-section')?.querySelectorAll('.store-admin-row').forEach(r=>r.style.display=(v==='all'||r.dataset.adminTier===v)?'grid':'none');});
 document.querySelectorAll('[data-admin-emoji-filter]').forEach(b=>b.onclick=()=>{const v=b.dataset.adminEmojiFilter;b.closest('.admin-store-section')?.querySelectorAll('[data-admin-emoji-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');b.closest('.admin-store-section')?.querySelectorAll('.store-admin-row').forEach(r=>r.style.display=(v==='all'||r.dataset.adminEmojiTier===v)?'grid':'none');});
 document.querySelectorAll('.designer-range-v137 input[type="range"]').forEach(adminDesignerLive);
});
function adminDesignerLive(input){
 if(!input) return; const out=input.closest('label')?.querySelector('output'); if(out) out.textContent=input.value+(input.dataset.unit||'');
 const root=document.querySelector('.admin-live-surface-v137'); if(!root) return;
 const form=input.closest('form'); if(!form) return;
 const get=n=>form.querySelector(`[name="${n}"]`)?.value;
 root.style.setProperty('--demo-btn-w',(get('ui_button_width')||126)+'px');
 root.style.setProperty('--demo-btn-h',(get('ui_button_height')||46)+'px');
 root.style.setProperty('--demo-radius',(get('ui_button_radius')||16)+'px');
 root.style.setProperty('--demo-btn-bg',get('ui_button_bg')||'#2e225f');
 root.style.setProperty('--demo-primary',get('ui_primary_bg')||'#facc15');
 root.style.setProperty('--demo-primary2',get('ui_primary_bg2')||'#ec4899');
 root.style.setProperty('--demo-card-radius',(get('ui_card_radius')||24)+'px');
 root.style.setProperty('--demo-card-bg',get('ui_card_bg')||'#1e293b');
 root.style.setProperty('--demo-table1',get('ui_table_bg1')||'#16a34a');
 root.style.setProperty('--demo-table2',get('ui_table_bg2')||'#064e3b');
 root.style.setProperty('--demo-table-border',get('ui_table_border_color')||'#5b3718');
 root.style.setProperty('--demo-price',get('ui_store_price_color')||'#facc15');
 root.style.setProperty('--demo-chat-w',(get('ui_chat_width')||340)+'px');
 root.style.setProperty('--demo-chat-h',(get('ui_chat_height')||560)+'px');
 root.style.setProperty('--demo-chat-radius',(get('ui_chat_radius')||24)+'px');
 root.style.setProperty('--demo-chat-font',(get('ui_chat_font')||14)+'px');
 root.style.setProperty('--demo-chat-btn-w',(get('ui_chat_button_width')||82)+'px');
 root.style.setProperty('--demo-chat-btn-h',(get('ui_chat_button_height')||40)+'px');
 root.style.setProperty('--demo-chat-btn-radius',(get('ui_chat_button_radius')||14)+'px');
 root.style.setProperty('--demo-chat-input-h',(get('ui_chat_input_height')||44)+'px');
 root.style.setProperty('--demo-chat-emoji',(get('ui_chat_emoji_size')||34)+'px');
 root.style.setProperty('--demo-chat-gap',(get('ui_chat_gap')||8)+'px');
 root.style.setProperty('--demo-chat-bg',get('ui_chat_bg')||'#0f172a');
 root.style.setProperty('--demo-chat-head',get('ui_chat_header_bg')||'#312e81');
 root.style.setProperty('--demo-chat-btn-bg',get('ui_chat_button_bg')||'#2e225f');
 root.style.setProperty('--demo-chat-btn-text',get('ui_chat_button_text')||'#ffffff');
 root.style.setProperty('--demo-chat-input',get('ui_chat_input_bg')||'#020617');
 root.style.setProperty('--demo-chat-message',get('ui_chat_message_bg')||'#1e293b');
}
function adminPreviewStoreItem(btn){const row=btn.closest('.store-admin-row'); const name=row?.querySelector('input[name="name_ar"]')?.value||'عنصر'; const price=row?.querySelector('input[name="price"]')?.value||'0'; const icon=row?.querySelector('.admin-item-preview span')?.textContent||'🎁'; const cat=row?.dataset.category||row?.closest('.admin-store-section')?.id?.replace('admin-store-','')||''; let visual=`<div class="shop-icon big">${escapeHtml(icon)}</div>`; if(cat==='table') visual='<div class="admin-full-table-preview"><div class="mock-table"><span></span><span></span><span></span><span></span><b>🂡 🂱 🃁</b></div></div>'; if(cat==='card_back') visual='<div class="card-back-showcase big"><span class="card-back-preview">🂠</span><span class="card-back-preview">🂠</span><span class="card-back-preview">🂠</span></div>'; if(cat==='text_color'||cat==='name_color'||cat==='name_frame') visual='<div class="profile-preview-card mini"><img src="/assets/avatars/default.svg"><b style="color:var(--my-name-color)">معاينة على البروفايل</b><p style="color:var(--my-text-color)">كلمة تجريبية</p></div>'; showRichNotice(`<div class="store-preview-pop admin-preview-pop"><div class="profile-preview-card">${visual}<h3>${escapeHtml(name)}</h3><p>السعر: 🪙 ${escapeHtml(price)}</p><small>معاينة إدارية واضحة قبل الحفظ أو الإظهار للمتجر.</small></div></div>`)}
</script>
@endsection
