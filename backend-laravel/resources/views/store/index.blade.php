@extends('layouts.app')
@section('content')
@php
$labels=[
 'pasha'=>'أيام الباشا',
 'table'=>'الطاولات',
 'card_back'=>'ظهر الورق',
 'text_color'=>'ألوان الكتابة',
 'name_color'=>'ألوان الأسماء',
 'emoji_pack'=>'الإيموجي',
 'badge'=>'الشارات',
 'effect'=>'المؤثرات',
 'xp_booster'=>'مسرعات XP',
 'inventory'=>'مشترياتي'
];
$icons=['pasha'=>'👑','table'=>'🟩','card_back'=>'🂠','text_color'=>'✍️','name_color'=>'🌈','emoji_pack'=>'😄','badge'=>'🏅','effect'=>'✨','xp_booster'=>'🚀','inventory'=>'🎒'];
$tierLabels=['all'=>'الكل','beginner'=>'مبتدئ','medium'=>'متوسط','featured'=>'مميز','pro'=>'Pro','legendary'=>'أسطوري','animated'=>'متحرك'];
$emojiTiers=['all'=>'الكل','free'=>'مجاني','laugh'=>'ضحك','happy'=>'فرح','angry'=>'غضب','sad'=>'حزن','vip'=>'VIP','animated'=>'متحرك'];
$firstActive='pasha';
foreach($labels as $k=>$v){ if($k!=='inventory' && (($items[$k] ?? collect())->count())){ $firstActive=$k; break; } }
@endphp

<section class="store-separated-v127" id="storeV127" data-warqna-store-contract="v158">
 <header class="store-separated-hero-v127">
  <div>
   <span class="v127-kicker">Warqna Store</span>
   <h1>متجر ورقنا المنظم</h1>
   <p>كل قسم منفصل لوحده: الباشا وحده، الطاولات وحدها، ظهر الورق وحده، الألوان وحدها، الإيموجي وحده، والمشتريات وحدها.</p>
  </div>
  <div class="store-wallet-v127"><b>🪙 {{ number_format(auth()->user()?->wallet?->tokens ?? 0) }}</b><span>رصيدك</span></div>
 </header>

 <nav class="store-category-tabs-v127">
  @foreach($labels as $key=>$label)
   <button type="button" data-store-tab-v127="{{$key}}" class="{{$key===$firstActive?'active':''}}">
    <span>{{$icons[$key]}}</span><b>{{$label}}</b>
    @if($key!=='inventory')<small>{{ ($items[$key] ?? collect())->count() }}</small>@endif
   </button>
  @endforeach
 </nav>

 <div class="store-search-row-v127">
  <input id="storeSearchV127" placeholder="ابحث داخل القسم الحالي فقط...">
  <div id="storeTierFiltersV127" class="store-tier-filters-v127">
   @foreach($tierLabels as $tk=>$tl)<button type="button" data-store-tier-v127="{{$tk}}" class="{{$tk==='all'?'active':''}}">{{$tl}}</button>@endforeach
  </div>
  <div id="emojiTierFiltersV127" class="store-tier-filters-v127 hidden">
   @foreach($emojiTiers as $ek=>$el)<button type="button" data-emoji-tier-v127="{{$ek}}" class="{{$ek==='all'?'active':''}}">{{$el}}</button>@endforeach
  </div>
 </div>

 <main class="store-section-frame-v127">
  @foreach($labels as $cat=>$label)
   @if($cat==='inventory')
    <section class="store-category-section-v127" data-store-section-v127="inventory" hidden>
     <div class="store-section-head-v127"><h2>🎒 مشترياتي</h2><p>كل ما تملكه يظهر هنا للتفعيل والمعاينة.</p></div>
     <div class="store-products-grid-v127 inventory-grid-v127">
      @forelse(($inventory ?? collect()) as $inv)
       @php $si=$inv->storeItem; @endphp
       <article class="store-product-card-v127 inventory-owned" data-category="inventory" data-name="{{ strtolower(($si?->name['ar'] ?? '').' '.($si?->key ?? '')) }}">
        @php $ownedAsset=$si?->payload['asset_url'] ?? $si?->payload['table_image'] ?? $si?->payload['card_back_image'] ?? null; @endphp
        <div class="product-preview-v127">@if($ownedAsset)<div class="admin-uploaded-cosmetic-preview" style="background-image:url('{{$ownedAsset}}')"></div>@else<div class="shop-icon product-generic-v127">{{ $si?->payload['preview_icon'] ?? '🎁' }}</div>@endif</div>
        <div class="product-info-v127"><h3>{{ $si?->name['ar'] ?? 'عنصر' }}</h3><p>{{ $si?->category ?? '' }}</p></div>
        <div class="product-actions-v127">
         <button type="button" onclick="previewStoreItem(this)">معاينة</button>
         @if($inv && !$inv->active)
          <form method="post" action="{{ route('inventory.activate',$inv) }}">@csrf<button class="primary" type="submit">تفعيل</button></form>
         @else
          <span class="active-owned-v131">مفعّل</span>
         @endif
        </div>
       </article>
      @empty
       <div class="empty-store-v127">لا توجد مشتريات بعد.</div>
      @endforelse
     </div>
    </section>
   @else
    @php $group=$items[$cat] ?? collect(); @endphp
    <section class="store-category-section-v127 {{$cat===$firstActive?'active':''}}" data-store-section-v127="{{$cat}}" {{$cat===$firstActive?'':'hidden'}}>
     <div class="store-section-head-v127">
      <h2>{{$icons[$cat]}} {{$label}}</h2>
      <p>
       @if($cat==='pasha') اشتراكات الباشا وأيام التميز تظهر هنا فقط.
       @elseif($cat==='table') كل الطاولات هنا فقط، مع معاينة شكل الطاولة قبل الشراء.
       @elseif($cat==='card_back') كل ظهور الورق هنا فقط، وتظهر على أوراقك داخل اللعبة.
       @elseif($cat==='text_color') ألوان كتابة الدردشة هنا فقط.
       @elseif($cat==='name_color') ألوان الأسماء والـ Glow هنا فقط.
       @elseif($cat==='emoji_pack') باقات الإيموجي هنا فقط.
       @elseif($cat==='badge') الشارات والهوية هنا فقط.
       @elseif($cat==='effect') مؤثرات الفوز والحركة هنا فقط.
       @elseif($cat==='xp_booster') مسرعات XP هنا فقط.
       @else منتجات هذا القسم هنا فقط.
       @endif
      </p>
     </div>

     <div class="store-products-grid-v127">
      @forelse($group as $item)
       @php
        $payload=$item->payload ?: [];
        $color=$payload['color'] ?? ($payload['color1'] ?? '#d4af37');
        $color2=$payload['color2'] ?? '#064e3b';
        $pattern=$payload['pattern'] ?? 'classic';
        $emblem=$payload['emblem'] ?? 'WZ';
        $tier=$payload['tier'] ?? $payload['tab'] ?? 'pro';
        $emojiTier=$payload['emoji_tier'] ?? (($item->price==0)?'free':'vip');
        $name=$item->name['ar'] ?? $item->key;
        $previewIcon=$payload['preview_icon'] ?? $payload['icon'] ?? $icons[$cat] ?? '🎁';
        $assetUrl=$payload['asset_url'] ?? $payload['table_image'] ?? $payload['card_back_image'] ?? null;
       @endphp
       <form method="post" action="{{ route('store.buy',$item) }}"
        class="store-product-card-v127 store-card"
        data-category="{{$cat}}"
        data-tier="{{$tier}}"
        data-emoji-tier="{{$emojiTier}}"
        data-color="{{$color}}"
        data-item-key="{{$item->key}}"
        data-name="{{ strtolower($name.' '.$item->key.' '.$cat) }}">
        @csrf
        <div class="product-preview-v127 type-{{$cat}}" style="--item-color:{{$color}};--item-color2:{{$color2}};--item-pattern:{{$pattern}}">
         @if($cat==='table')
          <div class="product-table-v127 table-real-preview-v128" @if($assetUrl) style="background-image:linear-gradient(#0003,#0005),url('{{$assetUrl}}');background-size:cover;background-position:center" @endif><i>{{$emblem}}</i></div>
         @elseif($cat==='card_back')
          <div class="product-cardback-v127 cardback-real-preview-v128" @if($assetUrl) style="background-image:url('{{$assetUrl}}');background-size:cover;background-position:center" @endif><i>{{$assetUrl?'':$emblem}}</i></div>
         @elseif($cat==='emoji_pack')
          <div class="emoji-store-icon product-emoji-v127">{{ $payload['emojis'] ?? $previewIcon }}</div>
         @elseif($cat==='xp_booster')
          <div class="product-rocket-v127">🚀</div>
         @elseif($cat==='name_color')
          <div class="name-color-live-preview-v136" style="--sample-color:{{$color}}">
           <img src="{{ auth()->user()?->profile?->avatar ?: '/assets/avatars/default.svg' }}" alt="avatar">
           <b>{{ auth()->user()?->profile?->display_name ?: auth()->user()?->username }}</b>
           <small>معاينة لون الاسم مباشرة</small>
          </div>
         @elseif($cat==='text_color')
          <div class="product-color-v127 text-color-live-preview-v136" style="--sample-color:{{$color}}"><b>رسالة دردشة تجريبية</b></div>
         @else
          <div class="shop-icon product-generic-v127">{{ $previewIcon }}</div>
         @endif
        </div>
        <div class="product-info-v127">
         <h3>{{ $name }}</h3>
         <p>{{ $item->description['ar'] ?? 'مقتنى فاخر داخل Warqnaa' }}</p>
        </div>
        <div class="product-actions-v127">
         <span class="price">@if($cat==='pasha')<img class="pasha-price-icon-v134" src="/assets/store/basha1.png" alt="باشا">@endif 🪙 {{ number_format($item->price) }}</span>
         @unless($cat==='pasha')<button type="button" onclick="previewStoreItem(this)">معاينة</button>@endunless
         <button class="primary" type="submit">شراء</button>
        </div>
       </form>
      @empty
       <div class="empty-store-v127">لا توجد منتجات في هذا القسم حاليًا.</div>
      @endforelse
     </div>
    </section>
   @endif
  @endforeach
 </main>
</section>

<script>
(function(){
 const root=document.getElementById('storeV127');
 const search=document.getElementById('storeSearchV127');
 const tabs=[...document.querySelectorAll('[data-store-tab-v127]')];
 const tierBtns=[...document.querySelectorAll('[data-store-tier-v127]')];
 const emojiBtns=[...document.querySelectorAll('[data-emoji-tier-v127]')];
 const tierBox=document.getElementById('storeTierFiltersV127');
 const emojiBox=document.getElementById('emojiTierFiltersV127');

 function activeCat(){ return document.querySelector('[data-store-tab-v127].active')?.dataset.storeTabV127 || 'pasha'; }
 function activeTier(){ return document.querySelector('[data-store-tier-v127].active')?.dataset.storeTierV127 || 'all'; }
 function activeEmoji(){ return document.querySelector('[data-emoji-tier-v127].active')?.dataset.emojiTierV127 || 'all'; }

 function apply(){
  const cat=activeCat();
  const q=(search?.value||'').trim().toLowerCase();
  const tier=activeTier();
  const emoji=activeEmoji();
  document.querySelectorAll('[data-store-section-v127]').forEach(sec=>{
   const on=sec.dataset.storeSectionV127===cat;
   sec.hidden=!on; sec.classList.toggle('active',on);
  });
  tierBox?.classList.toggle('hidden', !['table','card_back','xp_booster','effect','badge'].includes(cat));
  emojiBox?.classList.toggle('hidden', cat!=='emoji_pack');

  const section=document.querySelector(`[data-store-section-v127="${CSS.escape(cat)}"]`);
  if(!section) return;
  section.querySelectorAll('.store-product-card-v127').forEach(card=>{
   const okText=!q||(card.dataset.name||'').includes(q);
   const okTier=!['table','card_back','xp_booster','effect','badge'].includes(cat) || tier==='all' || (card.dataset.tier||'pro')===tier;
   const okEmoji=cat!=='emoji_pack' || emoji==='all' || (card.dataset.emojiTier||'vip')===emoji;
   card.hidden=!(okText&&okTier&&okEmoji);
  });
 }

 tabs.forEach(btn=>btn.addEventListener('click',()=>{
  tabs.forEach(b=>b.classList.toggle('active',b===btn));
  if(search) search.value='';
  apply();
 }));
 tierBtns.forEach(btn=>btn.addEventListener('click',()=>{tierBtns.forEach(b=>b.classList.toggle('active',b===btn)); apply();}));
 emojiBtns.forEach(btn=>btn.addEventListener('click',()=>{emojiBtns.forEach(b=>b.classList.toggle('active',b===btn)); apply();}));
 search?.addEventListener('input',apply);
 apply();
})();
</script>
@endsection
