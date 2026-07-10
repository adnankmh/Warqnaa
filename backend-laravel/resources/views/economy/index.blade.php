@extends('layouts.app')
@section('content')
<div class="economy-hero pro-card">
 <h1>🚀 المواسم والعروض النادرة</h1>
 <p>مركز اقتصاد Warqna: مواسم، فعاليات، عروض، مقتنيات نادرة، ومسرّعات.</p>
 <a class="btn primary" href="{{ route('store') }}">فتح المتجر</a>
</div>
<div class="economy-grid">
 <section class="pro-card">
  <h2>🏆 الموسم الحالي</h2>
  @if($season)
   <h3>{{ $season->name['ar'] ?? $season->key }}</h3>
   <p>من {{ optional($season->starts_at)->format('Y-m-d') ?: 'مفتوح' }} إلى {{ optional($season->ends_at)->format('Y-m-d') ?: 'مفتوح' }}</p>
   <pre class="json-mini">{{ json_encode($season->rewards, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>
  @else
   <p class="muted">لا يوجد موسم مفعل حاليًا. يمكن للإدارة إضافة موسم من قاعدة البيانات أو لوحة الإدارة القادمة.</p>
  @endif
 </section>
 <section class="pro-card">
  <h2>🔥 العروض النشطة</h2>
  @forelse($offers as $offer)
   <div class="offer-card"><b>{{ $offer->title['ar'] ?? $offer->key }}</b><span>{{ $offer->discount_percent }}%</span><p>{{ $offer->description['ar'] ?? '' }}</p></div>
  @empty <p class="muted">لا توجد عروض نشطة الآن.</p> @endforelse
 </section>
 <section class="pro-card">
  <h2>💎 مقتنيات نادرة</h2>
  @forelse($rares as $rare)
   <div class="rare-card rarity-{{ $rare->rarity }}"><b>{{ $rare->name['ar'] ?? $rare->key }}</b><span>{{ $rare->rarity }}</span><small>{{ $rare->claimed }}/{{ $rare->supply ?? '∞' }}</small></div>
  @empty <p class="muted">لا توجد مقتنيات نادرة مفعلة الآن.</p> @endforelse
 </section>
</div>
<section class="pro-card">
 <h2>🎁 عناصر مميزة</h2>
 <div class="store-grid refined">
  @foreach($featured as $item)
   <article class="store-card deluxe" data-category="{{ $item->category }}" data-item-key="{{ $item->key }}">
    <div class="shop-icon">{{ $item->payload['preview_icon'] ?? '🎁' }}</div>
    <h3>{{ $item->name['ar'] ?? $item->key }}</h3>
    <p class="price">🪙 {{ number_format($item->price) }}</p>
    <button type="button" onclick="previewStoreItem(this)">معاينة</button>
   </article>
  @endforeach
 </div>
</section>
@endsection
