@extends('layouts.app')
@section('content')
<section class="rewards-hero-v118 pro-card">
 <h1>🎁 المكافآت اليومية</h1>
 <p>ادخل يوميًا للحصول على توكنز ومكافآت خاصة، وكلما زادت السلسلة زادت قيمة المكافأة.</p>
 @if($today)
  <div class="reward-claimed-v118">✅ استلمت مكافأة اليوم: {{ number_format($today->coins) }} توكنز</div>
 @else
  <form method="post" action="{{ route('rewards.claim') }}">@csrf<button class="primary big-btn">استلام مكافأة اليوم</button></form>
 @endif
</section>

<section class="pro-card">
 <h2>آخر المكافآت</h2>
 <div class="reward-grid-v118">
 @forelse($claims as $claim)
  <div class="reward-card-v118">
   <b>اليوم {{ $claim->streak }}</b>
   <span>🪙 {{ number_format($claim->coins) }}</span>
   <small>{{ $claim->claim_date?->format('Y-m-d') }}</small>
  </div>
 @empty
  <p class="muted">لا توجد مكافآت بعد.</p>
 @endforelse
 </div>
</section>
@endsection
