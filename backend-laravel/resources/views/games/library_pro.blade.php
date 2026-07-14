@extends('layouts.app')
@section('content')
<section class="pro-hero-v118">
 <div>
  <h1>🎮 مكتبة ألعاب Warqnaa الاحترافية</h1>
  <p>منصة ألعاب ورق ورقعة اجتماعية، مبنية بمحركات عائلات: اللمّات، التجميع، العقود، الرقعة والنرد.</p>
 </div>
 <a class="btn primary" href="{{ route('games') }}">فتح كل الألعاب</a>
</section>

@foreach($matrix['families'] as $familyKey=>$family)
<section class="pro-card family-section-v118">
 <h2>{{ $family['ar'] }}</h2>
 <div class="game-family-grid-v118">
  @foreach($family['games'] as $gk)
   @php $g=$matrix['supported'][$gk] ?? null; @endphp
   @if($g)
    <article class="game-pro-card-v118 difficulty-{{ $g['difficulty'] }}">
     <div class="game-pro-icon-v118">🃏</div>
     <h3>{{ $g['ar'] }}</h3>
     <p>المحرك: <b>{{ $g['engine'] }}</b></p>
     <p>عدد اللاعبين: {{ $g['players'] }}</p>
     <span class="difficulty-pill">{{ $g['difficulty'] }}</span>
     <a class="btn" href="{{ route('rooms.index',$gk) }}">دخول اللعبة</a>
    </article>
   @endif
  @endforeach
 </div>
</section>
@endforeach

<section class="pro-card">
 <h2>🛡️ مبادئ الحماية</h2>
 <ul class="core-rule-list">
  <li>السيرفر هو الحكم الوحيد للحركات والنتائج.</li>
  <li>لا يتم إرسال أوراق الخصوم إلى اللاعب.</li>
  <li>يتم رفض الحركة غير القانونية وتسجيلها في مراقبة الغش.</li>
  <li>دعم لعب النظام مؤقتًا عند انقطاع اللاعب حتى 3 لفات.</li>
 </ul>
</section>
@endsection
