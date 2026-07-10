@extends('layouts.app')
@section('content')
@php
$typeLabels=[
 'store_buy'=>'شراء من المتجر','store_sale_income'=>'دخل بيع متجر','transfer_sent'=>'تحويل صادر','transfer_received'=>'تحويل وارد','transfer_fee'=>'عمولة تحويل','admin_silent_gift'=>'هدية إدارية','voice_room_entry'=>'غرفة صوتية','tournament_create'=>'إنشاء مسابقة','tournament_entry'=>'اشتراك مسابقة','club_create'=>'إنشاء نادي','daily_reward'=>'مكافأة يومية','reward_claim'=>'مكافأة'
];
$purchaseTypes=['store_buy','voice_room_entry','tournament_entry','tournament_create','club_create'];
$transferTypes=['transfer_sent','transfer_received','transfer_fee','admin_silent_gift'];
$rewardTypes=['daily_reward','reward_claim','store_sale_income'];
$summary=[
 'in'=>$transactions->where('amount','>',0)->sum('amount'),
 'out'=>abs($transactions->where('amount','<',0)->sum('amount')),
 'count'=>$transactions->count(),
];
@endphp
<section class="token-ledger-v136">
 <header class="token-ledger-hero-v136 pro-card">
  <div><span class="v127-kicker">Token Ledger</span><h1>سجل التوكنز</h1><p>سجل الشراء، التحويل، الإنفاق، المكافآت والعمولات في مكان واحد واضح وسهل القراءة.</p></div>
  <div class="token-balance-pill-v136"><small>رصيدك الحالي</small><b>🪙 {{number_format($user->wallet?->tokens ?? 0)}}</b></div>
 </header>
 <div class="token-summary-grid-v136">
  <div class="pro-card"><b>{{number_format($summary['in'])}}</b><span>إجمالي الداخل</span></div>
  <div class="pro-card"><b>{{number_format($summary['out'])}}</b><span>إجمالي المصروف</span></div>
  <div class="pro-card"><b>{{number_format($summary['count'])}}</b><span>عدد الحركات</span></div>
 </div>
 <div class="tokens-page token-ledger-layout-v136">
  <section class="pro-card token-transfer token-send-card-v136">
   <h2>إرسال توكنز للاعب</h2>
   <form method="post" action="{{route('wallet.transfer')}}" data-confirm="هل أنت متأكد من إرسال التوكنز؟ سيتم خصم عمولة 10% من حسابك.">@csrf
    <label>اسم اللاعب أو الإيميل</label><input name="receiver" placeholder="Username أو Email" required>
    <label>المبلغ</label><input type="number" name="amount" min="1" required>
    <button class="primary big-save">إرسال التوكنز</button>
   </form>
  </section>
  <section class="pro-card token-history token-ledger-card-v136">
   <div class="token-ledger-head-v136"><h2>كل الحركات الأخيرة</h2><div class="token-ledger-tabs-v136"><button type="button" class="active" data-token-filter="all">الكل</button><button type="button" data-token-filter="purchase">شراء/إنفاق</button><button type="button" data-token-filter="transfer">تحويل</button><button type="button" data-token-filter="reward">مكافآت/دخل</button></div></div>
   <div class="token-ledger-list-v136">
   @forelse($transactions as $t)
    @php
     $kind=in_array($t->type,$purchaseTypes,true)?'purchase':(in_array($t->type,$transferTypes,true)?'transfer':(in_array($t->type,$rewardTypes,true)?'reward':'other'));
     $meta=$t->meta ?: [];
     $details=collect($meta)->map(fn($v,$k)=>$k.': '.(is_array($v)?json_encode($v,JSON_UNESCAPED_UNICODE):$v))->implode(' · ');
    @endphp
    <article class="transaction-row token-ledger-row-v136 {{$t->amount>=0?'income':'expense'}}" data-token-kind="{{$kind}}">
     <span class="token-kind-icon-v136">{{$kind==='purchase'?'🛒':($kind==='transfer'?'🔁':($kind==='reward'?'🎁':'🧾'))}}</span>
     <div><b>{{$typeLabels[$t->type] ?? $t->type}}</b>@if($details)<small>{{$details}}</small>@endif<small>{{$t->created_at?->format('Y-m-d H:i')}}</small></div>
     <strong class="{{$t->amount>=0?'plus':'minus'}}">{{$t->amount>=0?'+':'-'}}{{number_format(abs($t->amount))}}</strong>
    </article>
   @empty
    <p class="muted">لا توجد عمليات بعد.</p>
   @endforelse
   </div>
  </section>
 </div>
</section>
<script>
document.addEventListener('click',function(e){const b=e.target.closest('[data-token-filter]');if(!b)return;document.querySelectorAll('[data-token-filter]').forEach(x=>x.classList.toggle('active',x===b));const v=b.dataset.tokenFilter;document.querySelectorAll('[data-token-kind]').forEach(r=>r.hidden=(v!=='all'&&r.dataset.tokenKind!==v));});
</script>
@endsection
