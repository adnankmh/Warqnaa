@extends('layouts.app')
@section('content')
<div class="tournament-hero premium-module-hero">
    <h1>🏆 المنافسات</h1>
    <p>أنشئ منافسة واضحة بمراحل محددة. المقعدين = نهائي/نصف/ربع/ثمن، والأربع مقاعد بنفس المنطق مع 4 لاعبين في كل مباراة.</p>
</div>
<div class="tournament-system-panel pro-card v105-system-panel">
 <h2>⚔️ نظام المنافسات المعتمد</h2>
 <div class="system-grid">
  <span>1 مرحلة = نهائي فقط</span><span>2 مراحل = نصف نهائي ثم نهائي</span><span>3 مراحل = ربع نهائي ثم نصف ثم نهائي</span><span>4 مراحل = ثمن نهائي ثم ربع ثم نصف ثم نهائي</span>
  <span>ألعاب المقعدين: 2 / 4 / 8 / 16 لاعب</span><span>ألعاب 4 مقاعد: 4 / 8 / 16 / 32 لاعب</span><span>رسوم دخول + صندوق جوائز</span><span>XP ونقاط صدارة حسب المرحلة</span>
 </div>
</div>

<form class="inline-card tournament-create-grid mega-tournament-form" method="post" action="{{ route('tournaments.store') }}">
    @csrf
    <label>
        اللعبة
        <select name="game_id" id="tourGameSelect" class="tour-select-glow">
            @foreach($games as $g)
                <option value="{{ $g->id }}" data-min="{{ $g->min_players }}" data-max="{{ $g->max_players }}" data-partner="{{ $g->partnership ? 1 : 0 }}">
                    {{ game_icon($g->key) }} {{ $g->name['ar'] }}
                </option>
            @endforeach
        </select>
        <small>اختر اللعبة التي ستُفتح لها غرف المنافسة تلقائيًا.</small>
    </label>

    <label>
        عدد المراحل
        <select name="stages" id="tourStages" class="tour-select-glow">
            <option value="1">1 — نهائي فقط</option>
            <option value="2">2 — نصف نهائي ثم نهائي</option>
            <option value="3">3 — ربع نهائي ثم نصف ثم نهائي</option>
            <option value="4">4 — ثمن نهائي ثم ربع ثم نصف ثم نهائي</option>
        </select>
        <small>أقصى شيء 4 مراحل.</small>
    </label>

    <label>
        مقاعد كل مباراة
        <select name="seats_per_match" id="tourSeats" class="tour-select-glow">
            <option value="2">2 لاعبين</option>
            <option value="3">3 لاعبين</option>
            <option value="4" selected>4 لاعبين</option>
            <option value="6">6 لاعبين</option>
        </select>
        <small>يتم ضبط الخيارات حسب اللعبة.</small>
    </label>

    <label>
        رسوم الدخول
        <input name="entry_fee" value="100">
        <small>إذا لم يملك اللاعب توكنز كافية تظهر له رسالة واضحة.</small>
    </label>

    <label>
        الجائزة الأساسية
        <input name="prize_pool" value="1000">
        <small>في ألعاب الشراكة تُقسم الجائزة على الفريق الفائز.</small>
    </label>

    <div id="tourCostBox" class="tour-cost-box">سيظهر هنا كم سيتم خصمه من صاحب المنافسة وكم يمكن أن تكون الجائزة قبل الإنشاء.</div>
    <small class="pasha-create-note">👑 إنشاء المنافسات متاح للباشا عند توفر التوكنز.</small><button class="primary tournament-main-btn">🏆 إنشاء منافسة</button>
</form>

<div class="module-grid tournament-grid-horizontal">
    @forelse($tournaments as $t)
        @php
            $needed = max(1, $t->seats_per_match * (2 ** max(0, ($t->stages ?? 1) - 1)));
            $count = $t->entries->count();
            $pct = min(100, round(($count / $needed) * 100));
            $match = $t->bracket['matches'][0] ?? null;
        @endphp
        <div class="pro-card tournament-card tournament-card-wide">
            <h3>{{ game_icon($t->game->key) }} {{ $t->game->name['ar'] }} #{{ $t->id }}</h3>
            <span class="pill">{{ $t->status }}</span>
            <span class="pill">{{ $t->stages }} مراحل</span>
            <span class="pill">{{ $t->seats_per_match }} مقاعد لكل مباراة</span>
            <p>المنشئ: {{ $t->creator->username }}</p>
            <p>رسوم الدخول: 🪙 {{ number_format($t->entry_fee) }} — الجائزة: 🪙 {{ number_format($t->prize_pool) }}</p>
            @php $dist=$t->prize_distribution ?: ($t->bracket['distribution'] ?? []); $lb=$t->leaderboard_points ?: ($t->bracket['leaderboard_points'] ?? []); @endphp
            <div class="tournament-prize-line"><span>نظام الجائزة: {{ $dist['note'] ?? ($t->seats_per_match>=4?'تقسيم للفريق/المراكز':'الفائز يأخذ الجائزة كاملة') }}</span><span>🏆 الأول: {{ $lb['first'] ?? 1000 }} نقطة صدارة</span><span>🥈 الثاني: {{ $lb['second'] ?? 600 }}</span></div>
            <div class="tournament-progress"><span style="width:{{ $pct }}%"></span></div>
            <p>المسجلون: {{ $count }} / {{ $needed }}</p><div class="tournament-bracket-record big-record"><b>📼 تسجيل المنافسة وسجل المراحل</b><p class="muted">بعد اكتمال المنافسة سيظهر هنا سجل واضح لكل المراحل والنتائج. وعند تفعيل التسجيل داخل غرفة المنافسة يتم حفظ لقطات الورق والحركات كسجل إعادة مشاهدة، مع إظهار أوراق جميع اللاعبين بعد انتهاء المنافسة.</p><div>@foreach(($t->bracket['messages'] ?? []) as $rec)<p>{{ $rec }}</p>@endforeach @if(empty($t->bracket['messages'] ?? []))<p class="muted">سيظهر هنا سجل مراحل المنافسة والنتائج بعد التشغيل.</p>@endif</div></div>

            @if($match && !empty($match['room_code']))
                <a class="btn success" href="{{ route('rooms.show', $match['room_code']) }}">دخول غرفة المنافسة {{ $match['room_code'] }}</a>
                <a class="btn replay-btn" href="{{ route('tournaments.replay', $t) }}">📼 مشاهدة/توليد تسجيل الفيديو</a>
            @else
                <form method="post" action="{{ route('tournaments.join', $t) }}">
                    @csrf
                    <button>دخول المنافسة</button>
                </form>

                @if($t->creator_id === auth()->id() || auth()->user()->is_admin)
                    <form method="post" action="{{ route('tournaments.launch', $t) }}">
                        @csrf
                        <button class="primary">تشغيل عند اكتمال العدد</button>
                    </form>
                @endif
            @endif

            <h4>اللاعبون</h4>
            @forelse($t->entries as $e)
                <span class="pill">{{ $e->user->username }}</span>
            @empty
                <p class="muted">لا يوجد مسجلون بعد.</p>
            @endforelse
        </div>
    @empty
        <div class="mini-card">لا توجد مسابقات بعد.</div>
    @endforelse
</div>

<script>
function updateTourSeats(){
    const option = document.querySelector('#tourGameSelect')?.selectedOptions?.[0];
    const max = Number(option?.dataset.max || 4);
    const min = Number(option?.dataset.min || 2);
    const select = document.querySelector('#tourSeats');
    if(!select) return;
    [...select.options].forEach(opt => {
        const value = Number(opt.value);
        opt.disabled = value < min || value > max;
    });
    const preferred = [...select.options].find(opt => !opt.disabled && Number(opt.value) === max) || [...select.options].find(opt => !opt.disabled);
    if(preferred) select.value = preferred.value;
}
document.getElementById('tourGameSelect')?.addEventListener('change', updateTourSeats);
updateTourSeats();
function updateTourHint(){const st=Number(document.getElementById('tourStages')?.value||1), seats=Number(document.getElementById('tourSeats')?.value||2), entry=Number(document.querySelector('[name="entry_fee"]')?.value||0), prize=Number(document.querySelector('[name="prize_pool"]')?.value||0); const total=seats*Math.pow(2,st-1), creationFee=1000, expectedPrize=prize+Math.floor(total*entry*.90); let box=document.getElementById('tourHint'); if(!box){box=document.createElement('div'); box.id='tourHint'; box.className='tour-hint'; document.querySelector('.mega-tournament-form')?.appendChild(box);} box.innerHTML='سيتم إنشاء منافسة من <b>'+total+'</b> مقعد/لاعب — '+(st===1?'نهائي فقط':st===2?'نصف نهائي ثم نهائي':st===3?'ربع نهائي ثم نصف نهائي ثم نهائي':'ثمن نهائي ثم ربع نهائي ثم نصف نهائي ثم نهائي'); const cost=document.getElementById('tourCostBox'); if(cost){ const partner=seats>=4; const first=partner?Math.floor(expectedPrize*.55):expectedPrize; const second=partner?Math.floor(expectedPrize*.30):0; const semi=partner?Math.floor(expectedPrize*.10):0; cost.innerHTML='<span>سيخصم من صاحب المنافسة: <b>1000</b> رسوم إنشاء + <b>'+prize.toLocaleString()+ '</b> جائزة أساسية = <b>'+(creationFee+prize).toLocaleString()+'</b> توكنز</span><span>عدد المقاعد المطلوب: <b>'+total+'</b></span><span>الجائزة المتوقعة بعد امتلاء التسجيل: <b>'+expectedPrize.toLocaleString()+'</b></span><span>توزيع تقديري: الأول <b>'+first.toLocaleString()+'</b>'+(second?'، الثاني <b>'+second.toLocaleString()+'</b>، المربع <b>'+semi.toLocaleString()+'</b>':'، الفائز يأخذها كاملة')+'</span><span>XP/صدارة: الأول 1000، الثاني 600، نصف النهائي 350، ربع النهائي 150</span>'; }}
document.getElementById('tourStages')?.addEventListener('change',updateTourHint);document.getElementById('tourSeats')?.addEventListener('change',updateTourHint);document.querySelector('[name="entry_fee"]')?.addEventListener('input',updateTourHint);document.querySelector('[name="prize_pool"]')?.addEventListener('input',updateTourHint);updateTourHint();
</script>
@endsection
