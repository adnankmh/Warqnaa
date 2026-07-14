<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Warqnaa | منصة ألعاب ورق عربية')</title>
    <meta name="description" content="Warqnaa منصة ألعاب ورق عربية اجتماعية: طرنيب، هاند، بناكل، بلوت، تركس، دومينو وطاولة مع غرف، مجموعات، منافسات ومتجر.">
    <meta name="keywords" content="ألعاب ورق, طرنيب, هاند, بناكل, بلوت, تركس, دومينو, طاولة, Warqna">
    <meta property="og:title" content="Warqnaa">
    <meta property="og:description" content="منصة ألعاب ورق عربية احترافية وآمنة وممتعة.">
    <meta name="theme-color" content="#0B3F1D">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/assets/icons/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"WebApplication","name":"Warqnaa","applicationCategory":"GameApplication","operatingSystem":"Web","inLanguage":"ar","description":"منصة ألعاب ورق عربية اجتماعية احترافية"}
    </script>
    <link rel="stylesheet" href="/assets/css/app.css?v=139-mobile-app-no-studio">
    <link rel="stylesheet" href="/assets/css/mobile-app.css?v=139-mobile-app-no-studio">
    <script>window.WARQNA_V130=true; window.WARQNA_V129=true; window.WARQNA_V128=true; window.WARQNA_V122=true; window.WARQNA_V123=true; window.WARQNA_V124=true; window.CSRF='{{ csrf_token() }}'; window.WARQNA_LOCALE='{{ app()->getLocale() }}'; window.AUTH_ID={{ auth()->check() ? auth()->id() : 'null' }}; window.PREF_URL='{{ auth()->check() ? route('preferences.quick') : '' }}';</script>
    <script defer src="/assets/js/app.js?v=139-mobile-app-no-studio"></script>
    <script defer src="/assets/js/mobile-app.js?v=139-mobile-app-no-studio"></script>
</head>
@php
    $currentUser = auth()->user();
    $currentProfile = $currentUser?->profile;
    $soundEnabled = $currentUser ? (($currentProfile?->sound_enabled !== false) ? '1' : '0') : '1';
    $nameColor = $currentProfile?->name_color ?? '#facc15';
    $textColor = $currentProfile?->chat_color ?? ($currentProfile?->text_color ?? '#ffffff');
    $globalTheme = class_exists('\App\Models\SiteSetting') ? \App\Models\SiteSetting::getValue('default_theme','royal') : 'royal';
    $forceGlobalTheme = class_exists('\App\Models\SiteSetting') ? \App\Models\SiteSetting::getValue('force_global_theme',false) : false;
    $siteTheme = $forceGlobalTheme ? $globalTheme : ($currentProfile?->active_site_theme ?? $globalTheme ?? 'royal');
    $nameFrame = $currentProfile?->active_name_frame ?? 'glow-gold';
    $ownedEmojis = $currentUser ? $currentUser->inventoryItems()->with('storeItem')->whereHas('storeItem', fn($q)=>$q->where('category','emoji_pack'))->get()->flatMap(fn($inv)=>preg_split('//u', (string)($inv->storeItem?->payload['emojis'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))->filter()->values()->all() : [];
    $freeEmojis = ['😂','🤣','😍','👋','👍','😡','😢','😭','😱','🤔','☕','🌹'];
    $emojiList = array_values(array_unique(array_merge($freeEmojis,$ownedEmojis)));
    $navGames = $currentUser ? \App\Models\Game::where('active',true)->orderBy('id')->get() : collect();
    $recentNotifs = $currentUser ? $currentUser->notifications()->latest()->limit(8)->get() : collect();
    $clubNotif = $currentUser ? $currentUser->notifications()->where('read',false)->where('type','like','%club%')->count() : 0;
    $gameNotif = $currentUser ? $currentUser->notifications()->where('read',false)->whereIn('type',['room_invite','game_invite'])->count() : 0;
    $tourNotif = $currentUser ? $currentUser->notifications()->where('read',false)->where('type','like','%tournament%')->count() : 0;
    $msgNotif = $currentUser ? $currentUser->notifications()->where('read',false)->whereIn('type',['private_message','friend_request'])->count() : 0;
    $activeRoom = $currentUser ? \App\Models\Room::with('game')->whereHas('players', fn($q)=>$q->where('user_id',$currentUser->id)->where('is_bot',false))->whereIn('status',['waiting','bidding','playing'])->latest()->first() : null;
@endphp
@php $globalAnnouncement = class_exists('\App\Models\SiteSetting') ? \App\Models\SiteSetting::getValue('global_announcement','') : ''; $customCss = class_exists('\App\Models\SiteSetting') ? \App\Models\SiteSetting::getValue('custom_css','') : ''; @endphp
<body class="warqna-pro-social theme-{{ $siteTheme }} {{ request()->routeIs('store') ? 'is-store-page' : '' }} {{ request()->routeIs('room.show') ? 'is-room-page' : '' }}" data-sound="{{ $soundEnabled }}" data-user="{{ $currentUser?->username ?? '' }}" data-theme="{{ $siteTheme }}" data-country-code="{{ $currentProfile?->country_code ?? 'PS' }}" data-country-name="{{ country_name($currentProfile?->country_code ?? 'PS') }}" style="--my-name-color:{{ $nameColor }};--my-text-color:{{ $textColor }}">
    @if($globalAnnouncement)<div class="global-announcement">{{ $globalAnnouncement }}</div>@endif
    @if($customCss)<style id="adminCustomCss">{!! $customCss !!}</style>@endif
    @php
        $uiGet = fn($key,$default)=> class_exists('\App\Models\SiteSetting') ? \App\Models\SiteSetting::getValue($key,$default) : $default;
        $uiPx = fn($key,$default)=> (int)$uiGet($key,$default).'px';
        $uiPct = fn($key,$default)=> (int)$uiGet($key,$default).'%';
        $uiColor = fn($key,$default)=> preg_match('/^#[0-9a-fA-F]{6}$/',(string)$uiGet($key,$default)) ? $uiGet($key,$default) : $default;
        $buttonStyle=$uiGet('ui_button_style','gradient');
        $cardShadow=$uiGet('ui_card_shadow','medium');
        $tableShape=$uiGet('ui_table_shape','rounded');
        $animationLevel=$uiGet('ui_animation_level','soft');
    @endphp
    <style id="adminNoCodeDesignerCss">
    body{
        --admin-btn-w:{{$uiPx('ui_button_width',126)}};--admin-btn-h:{{$uiPx('ui_button_height',46)}};--admin-btn-radius:{{$uiPx('ui_button_radius',16)}};--admin-btn-font:{{$uiPx('ui_button_font',14)}};--admin-btn-gap:{{$uiPx('ui_button_gap',8)}};
        --admin-btn-bg:{{$uiColor('ui_button_bg','#2e225f')}};--admin-btn-text:{{$uiColor('ui_button_text','#ffffff')}};--admin-primary-1:{{$uiColor('ui_primary_bg','#facc15')}};--admin-primary-2:{{$uiColor('ui_primary_bg2','#ec4899')}};
        --admin-panel-bg:{{$uiColor('ui_panel_bg','#0f172a')}};--admin-card-bg:{{$uiColor('ui_card_bg','#1e293b')}};--admin-site-bg1:{{$uiColor('ui_site_bg1','#07170f')}};--admin-site-bg2:{{$uiColor('ui_site_bg2','#020617')}};
        --admin-card-radius:{{$uiPx('ui_card_radius',24)}};--admin-card-padding:{{$uiPx('ui_card_padding',18)}};--admin-card-gap:{{$uiPx('ui_card_gap',16)}};--admin-card-min-h:{{$uiPx('ui_card_min_height',220)}};
        --admin-page-padding:{{$uiPx('ui_page_padding',18)}};--admin-page-max:{{$uiPx('ui_page_max_width',1500)}};--admin-nav-height:{{$uiPx('ui_nav_height',60)}};--admin-nav-radius:{{$uiPx('ui_nav_radius',16)}};
        --admin-store-card-w:{{$uiPx('ui_store_card_width',220)}};--admin-store-card-h:{{$uiPx('ui_store_card_height',270)}};--admin-store-icon:{{$uiPx('ui_store_icon_size',72)}};--admin-store-price:{{$uiColor('ui_store_price_color','#facc15')}};
        --admin-game-card-w:{{$uiPx('ui_game_card_width',230)}};--admin-game-card-h:{{$uiPx('ui_game_card_height',230)}};--admin-game-icon:{{$uiPx('ui_game_icon_size',64)}};
        --admin-table-radius:{{$uiPx('ui_table_radius',46)}};--admin-table-border:{{$uiPx('ui_table_border',16)}};--admin-table-height:{{$uiPx('ui_table_min_height',610)}};--admin-table-scale:{{(int)$uiGet('ui_table_center_scale',92)}};--admin-table-bg1:{{$uiColor('ui_table_bg1','#16a34a')}};--admin-table-bg2:{{$uiColor('ui_table_bg2','#064e3b')}};--admin-table-border-color:{{$uiColor('ui_table_border_color','#5b3718')}};
        --admin-play-card-w:{{$uiPx('ui_card_play_width',58)}};--admin-play-card-h:{{$uiPx('ui_card_play_height',82)}};--admin-player-avatar:{{$uiPx('ui_player_avatar',56)}};
        --admin-chat-w:{{$uiPx('ui_chat_width',340)}};--admin-chat-h:{{$uiPx('ui_chat_height',560)}};--admin-chat-radius:{{$uiPx('ui_chat_radius',24)}};--admin-chat-font:{{$uiPx('ui_chat_font',14)}};--admin-chat-btn-w:{{$uiPx('ui_chat_button_width',82)}};--admin-chat-btn-h:{{$uiPx('ui_chat_button_height',40)}};--admin-chat-btn-radius:{{$uiPx('ui_chat_button_radius',14)}};--admin-chat-input-h:{{$uiPx('ui_chat_input_height',44)}};--admin-chat-emoji:{{$uiPx('ui_chat_emoji_size',34)}};--admin-chat-gap:{{$uiPx('ui_chat_gap',8)}};--admin-notif-w:{{$uiPx('ui_notif_width',420)}};--admin-profile-w:{{$uiPx('ui_profile_width',560)}};--admin-profile-font:{{$uiPx('ui_profile_font',13)}};--admin-nav-bg:{{$uiColor('ui_nav_bg','#020617')}};--admin-chat-bg:{{$uiColor('ui_chat_bg','#0f172a')}};--admin-chat-head:{{$uiColor('ui_chat_header_bg','#312e81')}};--admin-chat-btn-bg:{{$uiColor('ui_chat_button_bg','#2e225f')}};--admin-chat-btn-text:{{$uiColor('ui_chat_button_text','#ffffff')}};--admin-chat-input-bg:{{$uiColor('ui_chat_input_bg','#020617')}};--admin-chat-message-bg:{{$uiColor('ui_chat_message_bg','#1e293b')}};
        background:radial-gradient(circle at top,var(--admin-site-bg1),var(--admin-site-bg2) 68%)!important;
    }
    .page{max-width:var(--admin-page-max)!important;margin-inline:auto!important;padding:var(--admin-page-padding)!important}.topbar,.userbar{min-height:var(--admin-nav-height)!important;border-radius:0 0 var(--admin-nav-radius) var(--admin-nav-radius)!important;background:color-mix(in srgb,var(--admin-nav-bg),transparent 8%)!important}.btn,button,.topbar a,.userbar button{min-height:var(--admin-btn-h)!important;border-radius:var(--admin-btn-radius)!important;font-size:var(--admin-btn-font)!important;gap:var(--admin-btn-gap)!important;color:var(--admin-btn-text)!important}@if($buttonStyle==='gradient').btn,button,.topbar a,.userbar button{background:linear-gradient(135deg,var(--admin-btn-bg),color-mix(in srgb,var(--admin-primary-2),#000 10%))!important}.primary,button.primary{background:linear-gradient(135deg,var(--admin-primary-1),var(--admin-primary-2))!important}@elseif($buttonStyle==='glass').btn,button,.topbar a,.userbar button{background:color-mix(in srgb,var(--admin-btn-bg),transparent 48%)!important;backdrop-filter:blur(16px)!important;border:1px solid color-mix(in srgb,var(--admin-primary-1),transparent 55%)!important}@elseif($buttonStyle==='outline').btn,button,.topbar a,.userbar button{background:transparent!important;border:1px solid var(--admin-primary-1)!important}.primary,button.primary{background:var(--admin-primary-1)!important;color:#06110d!important}@else.btn,button,.topbar a,.userbar button{background:var(--admin-btn-bg)!important}.primary,button.primary{background:var(--admin-primary-1)!important;color:#06110d!important}@endif
    .game-card,.store-card,.club-card,.tournament-card,.room-card,.pro-card,.mini-card,.admin-card,.builder-card,.store-product-card-v127{border-radius:var(--admin-card-radius)!important;padding:var(--admin-card-padding)!important;background:linear-gradient(145deg,var(--admin-card-bg),color-mix(in srgb,var(--admin-panel-bg),#000 20%))!important;min-height:var(--admin-card-min-h)}@if($cardShadow==='strong').game-card,.store-card,.pro-card,.mini-card{box-shadow:0 28px 90px rgba(0,0,0,.58)!important}@elseif($cardShadow==='medium').game-card,.store-card,.pro-card,.mini-card{box-shadow:0 18px 48px rgba(0,0,0,.38)!important}@elseif($cardShadow==='soft').game-card,.store-card,.pro-card,.mini-card{box-shadow:0 10px 28px rgba(0,0,0,.24)!important}@else.game-card,.store-card,.pro-card,.mini-card{box-shadow:none!important}@endif
    .store-grid,.store-products-grid-v127{grid-template-columns:repeat(auto-fill,minmax(var(--admin-store-card-w),1fr))!important;gap:var(--admin-card-gap)!important}.store-card,.store-product-card-v127{min-height:var(--admin-store-card-h)!important}.shop-icon,.product-generic-v127,.emoji-store-icon{font-size:var(--admin-store-icon)!important}.product-actions-v127 .price,.price,.tokens,.admin-demo-price-v137{color:var(--admin-store-price)!important}.game-grid{grid-template-columns:repeat(auto-fill,minmax(var(--admin-game-card-w),1fr))!important;gap:var(--admin-card-gap)!important}.game-card{min-height:var(--admin-game-card-h)!important}.game-icon,.game-icon-pro-v130{font-size:var(--admin-game-icon)!important}
    .game-table.premium-table,.game-table{min-height:var(--admin-table-height)!important;border-radius:@if($tableShape==='stadium')999px @elseif($tableShape==='square-soft')var(--admin-table-radius) @else var(--admin-table-radius) @endif!important;border-width:var(--admin-table-border)!important;border-color:var(--admin-table-border-color)!important;background:radial-gradient(circle at center,var(--admin-table-bg1),var(--admin-table-bg2) 72%)!important}.center-board{scale:calc(var(--admin-table-scale) / 100)!important}.hand-row .card,.card{width:var(--admin-play-card-w)!important;height:var(--admin-play-card-h)!important}.seat-profile img,.player-ring,.player-ring img{width:var(--admin-player-avatar)!important;height:var(--admin-player-avatar)!important}.chat-dock{width:var(--admin-chat-w)!important;height:var(--admin-chat-h)!important;border-radius:var(--admin-chat-radius)!important;font-size:var(--admin-chat-font)!important;background:linear-gradient(145deg,var(--admin-chat-bg),color-mix(in srgb,var(--admin-chat-bg),#000 28%))!important}.chat-head{background:linear-gradient(135deg,var(--admin-chat-head),color-mix(in srgb,var(--admin-chat-head),#000 34%))!important}.chat-tabs{gap:var(--admin-chat-gap)!important;padding:var(--admin-chat-gap)!important}.chat-tabs button,.chat-head button,.chat-send button{min-width:var(--admin-chat-btn-w)!important;min-height:var(--admin-chat-btn-h)!important;border-radius:var(--admin-chat-btn-radius)!important;background:var(--admin-chat-btn-bg)!important;color:var(--admin-chat-btn-text)!important}.chat-send input,.chat-search{min-height:var(--admin-chat-input-h)!important;background:var(--admin-chat-input-bg)!important}.chat-body .msg,.game-chat-msg{background:var(--admin-chat-message-bg)!important}.emoji-palette button,.quick-reactions-box-v132 button{font-size:var(--admin-chat-emoji)!important;min-width:calc(var(--admin-chat-emoji) + 18px)!important;min-height:calc(var(--admin-chat-emoji) + 18px)!important}.notification-drawer,.notification-drawer-v136{width:min(var(--admin-notif-w),calc(100vw - 24px))!important}.profile-modal,.profile-modal-card{width:min(var(--admin-profile-w),calc(100vw - 22px))!important;font-size:var(--admin-profile-font)!important}@if($animationLevel==='none')*,*:before,*:after{animation:none!important;transition:none!important}@elseif($animationLevel==='premium').btn:hover,button:hover,.game-card:hover,.store-card:hover{transform:translateY(-4px) scale(1.015)!important;filter:brightness(1.08)!important}@endif
    </style>
    <div class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('games') : route('home') }}">ورقنا زون</a>
        @auth
            <button type="button" class="nav-drop-btn games-top-only-v128" onclick="toggleTopPanel('gamesCurtain')" data-i18n="all_games">🎮 الألعاب ▾</button>
            <a href="{{ route('game.rules') }}" data-i18n="rules">قوانين الألعاب</a>
            <a href="{{ route('store') }}" data-i18n="store">المتجر</a><a href="{{ route('rewards') }}">المكافآت</a>
            <a href="{{ route('clubs') }}" data-i18n="groups">المجموعات</a>
            <a href="{{ route('tournaments') }}" data-i18n="competitions">المنافسات</a>
            <a class="nav-drop-btn" href="{{ route('notifications') }}">الإشعارات</a>
            <button type="button" class="nav-drop-btn" onclick="openProfile({{ auth()->id() }})"><span data-i18n="my_profile">بروفايلي</span></button>
            <a href="{{ route('settings') }}" data-i18n="settings">الإعدادات</a>
            <a href="{{ route('about') }}"><span data-i18n="about">حول</span></a>
            <a href="{{ route('contact') }}" data-i18n="contact">اتصل بنا</a>
            @if($currentUser?->is_admin)<a href="{{ route('admin') }}"><span data-i18n="admin">الإدارة</span></a>@endif
        @endauth
    </div>
    @auth
        
        
        <div id="gamesCurtain" class="wz-games-menu-v130 hidden" role="dialog" aria-label="قائمة الألعاب">
            <div class="wz-games-menu-card-v130">
                <div class="wz-games-menu-head-v130">
                    <div>
                        <b>🎮 الألعاب</b>
                        <small>اختر لعبة بسرعة. تختفي القائمة مباشرة بعد اختيار اللعبة أو الضغط خارجها.</small>
                    </div>
                    <div class="wz-games-menu-tools-v130">
                        <input id="navGameSearchV130" type="search" placeholder="ابحث عن لعبة...">
                        <a href="{{ route('games') }}">صفحة الألعاب</a>
                        <button type="button" class="wz-games-close-v130" data-games-close-v130>×</button>
                    </div>
                </div>
                @php
                    $navFamilies=['all'=>'الكل'];
                @endphp
                <div class="wz-games-tabs-v130">
                    @foreach($navFamilies as $fk=>$fl)
                        <button type="button" data-game-family-v130="{{$fk}}" class="{{$fk==='all'?'active':''}}">{{$fl}}</button>
                    @endforeach
                </div>
                <div class="wz-games-grid-v130">
                    @foreach($navGames as $g)
                        @php $family=$g->rules['family'] ?? 'training'; $engine=$g->rules['engine'] ?? ''; @endphp
                        <a class="wz-game-pop-v130 {{ $g->key }}"
                           data-game-link-v130
                           data-family="{{$family}}"
                           data-name="{{ strtolower($g->key.' '.($g->name['ar'] ?? '').' '.($g->name['en'] ?? '').' '.$engine) }}"
                           href="{{ route('rooms.index',$g->key) }}">
                            <span class="game-icon-pro-v130">{{ $g->rules['icon'] ?? game_icon($g->key) }}</span>
                            <b>{{ $g->name['ar'] ?? $g->key }}</b>
                            <small>{{ $g->min_players }}-{{ $g->max_players }} • {{ $g->partnership ? 'شراكة' : 'فردي' }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="themePanel" class="top-panel theme-picker-panel hidden">
            <b>🎨 الثيمات</b>
            <p class="muted">اختر ثيم الموقع، ويتم تفعيله مباشرة على حسابك.</p>
            <div class="theme-grid-v108">
                <button type="button" data-theme-pick="royal" onclick="setSiteTheme('royal');toggleTopPanel('themePanel')">👑 ملكي ذهبي</button>
                <button type="button" data-theme-pick="midnight" onclick="setSiteTheme('midnight');toggleTopPanel('themePanel')">🌙 ليلي أزرق</button>
                <button type="button" data-theme-pick="emerald" onclick="setSiteTheme('emerald');toggleTopPanel('themePanel')">💎 زمردي فاخر</button>
                <button type="button" data-theme-pick="desert" onclick="setSiteTheme('desert');toggleTopPanel('themePanel')">🏜️ صحراوي</button>
                <button type="button" data-theme-pick="galaxy" onclick="setSiteTheme('galaxy');toggleTopPanel('themePanel')">🌌 مجرة نيون</button>
                <button type="button" data-theme-pick="crimson" onclick="setSiteTheme('crimson');toggleTopPanel('themePanel')">❤️ قرمزي</button>
                <button type="button" data-theme-pick="ocean" onclick="setSiteTheme('ocean');toggleTopPanel('themePanel')">🌊 محيطي</button>
            </div>
        </div>

        <div id="languagePanel" class="top-panel language-picker-panel hidden">
            <b data-i18n="site_language">لغة الموقع</b>
            <p class="muted">اختر اللغة وسيتم تطبيق الاتجاه والترجمة مباشرة على الواجهة.</p>
            <div class="language-grid-v138">
                <button type="button" data-lang-pick="ar" onclick="setWarqnaLang('ar');toggleTopPanel('languagePanel')">🇵🇸 عربي</button>
                <button type="button" data-lang-pick="en" onclick="setWarqnaLang('en');toggleTopPanel('languagePanel')">🇬🇧 English</button>
                <button type="button" data-lang-pick="de" onclick="setWarqnaLang('de');toggleTopPanel('languagePanel')">🇩🇪 Deutsch</button>
                <button type="button" data-lang-pick="tr" onclick="setWarqnaLang('tr');toggleTopPanel('languagePanel')">🇹🇷 Türkçe</button>
                <button type="button" data-lang-pick="fr" onclick="setWarqnaLang('fr');toggleTopPanel('languagePanel')">🇫🇷 Français</button>
                <button type="button" data-lang-pick="es" onclick="setWarqnaLang('es');toggleTopPanel('languagePanel')">🇪🇸 Español</button>
            </div>
        </div>

        <div class="userbar">
            <button type="button" class="user-chip player-glow {{ $nameFrame }}" onclick="openProfile({{ auth()->id() }})" style="--player-color:{{ $nameColor }}">
                {!! flag_img($currentProfile?->country_code ?? 'PS','flag-img flag-small') !!}
                <img class="avatar-xs" src="{{ $currentProfile?->avatar ?: '/assets/avatars/default.svg' }}" alt="avatar">
                <span>{{ $currentUser->username }}</span>
            </button>
            <span class="pasha pasha-days-chip-v136"><img class="pasha-mini-icon-v136" src="/assets/store/basha1.png" alt="باشا"><span data-i18n="pasha">باشا</span>: {{ $currentProfile?->pasha_days ?? 0 }} <span data-i18n="days">يوم</span></span>
            <a class="tokens tokens-ledger-link-v136" href="{{ route('tokens') }}" title="سجل التوكنز">🪙 {{ number_format($currentUser->wallet?->tokens ?? 0) }}</a>
            <span id="siteClock" class="site-clock">--:--</span>
            <button type="button" class="theme-switch-btn" onclick="toggleTopPanel('themePanel')" title="الثيمات">🎨</button>
            <button type="button" class="language-switch-btn" onclick="toggleTopPanel('languagePanel')" title="اللغات">🌐</button>
            <label class="sound-range-wrap" title="تحكم بالصوت من 0 إلى 100"><span>🔊</span><input id="soundVolumeRange" type="range" min="0" max="100" step="1" value="80" aria-label="مستوى الصوت"></label>
            <div id="clubPanel" class="top-panel hidden"><b>إشعارات المجموعات</b><p>طلبات الانضمام وتحديثات المجموعة تظهر هنا داخل نفس الصفحة.</p><a href="{{route('clubs')}}">فتح المجموعات</a></div>
            <div id="invitePanel" class="top-panel hidden"><b>دعوات الألعاب</b><p>أي دعوة غرفة من لاعب آخر تظهر هنا بدون مغادرة الصفحة.</p><a href="{{route('notifications')}}">كل الدعوات</a></div>
            <div id="tourPanel" class="top-panel hidden"><b>المنافسات</b><p>متابعة المنافسات المفتوحة والمكتملة.</p><a href="{{route('tournaments')}}">فتح المنافسات</a></div>
            <div id="msgPanel" class="top-panel hidden chat-center-panel"><b>مركز الدردشة</b><div class="mini-chat-tabs"><button type="button" onclick="setChatMode('room');document.getElementById('chatDock')?.classList.remove('hidden')">دردشة اللعبة</button><button type="button" onclick="setChatMode('friends');document.getElementById('chatDock')?.classList.remove('hidden')">الأصدقاء</button><button type="button" onclick="setChatMode('search');document.getElementById('chatDock')?.classList.remove('hidden')">بحث</button></div><input placeholder="ابحث عن لاعب أو صديق" oninput="filterChatList(this.value)"><p>تبويبة دردشة اللعبة مدمجة مع مركز الدردشة على اليسار وتعمل داخل الغرفة نفسها.</p><a href="{{route('friends')}}">الأصدقاء</a></div>
            <div id="notifPanel" class="top-panel hidden notification-drawer notification-drawer-v136">
                <div class="notif-drawer-head-v136"><b>🔔 مركز الإشعارات</b><form method="post" action="{{ route('notifications.readAll') }}" data-ajax-soft>@csrf<button type="submit">قراءة الكل</button></form></div>
                <div class="notif-type-list-v136">
                    @php $notifGroups=['room_invite'=>'دعوات الألعاب','game_invite'=>'دعوات الألعاب','tournament'=>'المسابقات','club'=>'النادي','private_message'=>'الرسائل','friend_request'=>'الأصدقاء']; @endphp
                    @forelse($recentNotifs as $n)
                        @php $label='عام'; foreach($notifGroups as $needle=>$txt){ if(str_contains((string)$n->type,$needle)){ $label=$txt; break; } } @endphp
                        <article class="drawer-row notif-row-v136 {{ $n->read ? 'is-read' : 'is-unread' }}">
                            <span class="notif-kind-v136">{{ $label }}</span>
                            <b>{{ $n->title['ar'] ?? $n->type }}</b>
                            <p>{{ $n->body['ar'] ?? '' }}</p>
                            <div class="notif-actions-v136">
                                @if($n->url)<a href="{{ $n->url }}">فتح</a>@endif
                                <form method="post" action="{{ route('notifications.read',$n) }}" data-ajax-soft>@csrf<button type="submit">قراءة</button></form>
                                <form method="post" action="{{ route('notifications.delete',$n) }}" data-ajax-soft data-confirm="حذف هذا الإشعار؟">@csrf<button type="submit" class="danger">حذف</button></form>
                            </div>
                        </article>
                    @empty<p class="muted">لا توجد إشعارات جديدة.</p>@endforelse
                </div>
            </div>

            <div class="top-icons" aria-label="مركز الإشعارات">
                <button type="button" class="notif-live-btn notif-page-go" data-notif-type="club" onclick="toggleTopPanel('notifPanel')" title="إشعارات المجموعات">🏛️<b class="{{ $clubNotif ? '' : 'hidden' }}">{{ $clubNotif }}</b></button>
                <button type="button" class="notif-live-btn notif-page-go" data-notif-type="game" onclick="toggleTopPanel('notifPanel')" title="دعوات الألعاب">🎮<b class="{{ $gameNotif ? '' : 'hidden' }}">{{ $gameNotif }}</b></button>
                <button type="button" class="notif-live-btn notif-page-go" data-notif-type="competition" onclick="toggleTopPanel('notifPanel')" title="المنافسات">🏆<b class="{{ $tourNotif ? '' : 'hidden' }}">{{ $tourNotif }}</b></button>
                <button type="button" class="notif-live-btn notif-page-go" data-notif-type="message" onclick="setChatMode('friends');reopenChat();" title="الرسائل والأصدقاء">💬<b class="{{ $msgNotif ? '' : 'hidden' }}">{{ $msgNotif }}</b></button>
            </div>
            <button type="button" onclick="changeFont(1);window.WarqnaSound?.ui()">A+</button>
            <button type="button" onclick="changeFont(-1);window.WarqnaSound?.ui()">A-</button>
            <button type="button" id="soundToggle" title="تشغيل/إيقاف الصوت" onclick="window.WarqnaSound?.toggleMute?.()">🔊</button>
            @if($activeRoom)
                <a class="active-room-chip active-room-right" href="{{ route('rooms.show',$activeRoom->code) }}">🎮 داخل لعبة {{ $activeRoom->code }}</a>
                <form method="post" class="global-leave-game active-room-right" action="{{ route('rooms.leave',$activeRoom->code) }}" data-confirm="هل تريد الخروج من اللعبة؟ تنبيه: إذا خرجت 3 مرات من نفس اللعبة لن تستطيع العودة لها مرة أخرى.">@csrf<button type="submit">🚪 خروج من اللعبة</button></form>
            @endif
            <form method="post" action="{{ route('logout') }}" data-confirm="هل تريد تسجيل الخروج؟">@csrf<button type="submit"><span data-i18n="logout">خروج</span></button></form>
        </div>
    @endauth
    <main class="page">
        @if(session('ok'))<script>window.addEventListener('DOMContentLoaded',()=>showNotice(@json(session('ok'))));</script>@endif
        @if($errors->any())<script>window.addEventListener('DOMContentLoaded',()=>showNotice(@json($errors->first())));</script>@endif
        @yield('content')
<button id="installAppBtn" class="install-app-btn hidden" type="button">📲 تثبيت التطبيق</button>
<div id="mobileSafeToast" class="mobile-safe-toast hidden"></div>
    </main>
    @auth
        <aside id="chatDock" class="chat-dock chat-expanded">
            <div class="chat-head"><span data-i18n="chat_center">مركز الدردشة</span> <span><button type="button" onclick="toggleChat()">—</button><button type="button" onclick="minimizeChat()">▾</button><button type="button" onclick="maximizeChat()">□</button><button type="button" onclick="closeChat()">×</button></span></div>
            <div class="chat-tabs">
                <button type="button" data-chat-tab="room" onclick="setChatMode('room')"><span data-i18n="game_chat">دردشة اللعبة</span></button>
                <button type="button" data-chat-tab="friends" onclick="setChatMode('friends')"><span data-i18n="friends">الأصدقاء</span></button>
                <button type="button" data-chat-tab="search" onclick="setChatMode('search')"><span data-i18n="search">بحث</span></button>
            </div>
            <input id="chatSearch" class="chat-search" placeholder="ابحث باسم لاعب أو صديق" oninput="chatSearchChanged(this.value)">
            <div class="emoji-palette" id="emojiPalette"></div>
            <div class="chat-body" id="chatBody"><p class="muted">اختر <span data-i18n="game_chat">دردشة اللعبة</span> أو صديقًا للبدء.</p></div>
            <form class="chat-send" onsubmit="sendChat(event)"><input id="chatInput" placeholder="{{ app()->getLocale()==="ar" ? "اكتب رسالة واضغط Enter" : "Type message" }}"><button type="submit"><span data-i18n="send">إرسال</span></button></form>
        </aside>
        <script>window.WARQNA_EMOJIS=@json($emojiList); window.CHAT_HAS_ROOM=@json(request()->routeIs('room.show')); window.CHAT_ROOM_LABEL=@json($activeRoom?->code ?? null);</script>
        <button id="chatReopen" class="chat-reopen hidden" type="button" onclick="reopenChat()">💬</button>
        <div id="profileModal" class="profile-modal hidden"></div>
    @endauth
<script>
if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').catch(()=>{}));}
</script>
</body>
</html>
