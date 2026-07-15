<?php
require_once __DIR__.'/../app/Services/GameEngine/GlobalEngines/BanakilEngine.php';

function ok(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "[FAIL] {$message}\n");
        exit(1);
    }
    echo "[PASS] {$message}\n";
}

$engine = new BanakilEngine();
$players = [];
for ($i=1; $i<=4; $i++) $players[] = ['id'=>'p'.$i, 'name'=>'P'.$i, 'bot'=>false];
$state = $engine->newGame($players, ['seed'=>3032026]);

ok($state['phase'] === 'discard', 'اللاعب البادئ يبدأ بالرمي');
ok(count($state['hands']['p1']) === 19, 'اللاعب البادئ يحمل 19 ورقة');
ok(count($state['hands']['p2']) === 18 && count($state['hands']['p3']) === 18 && count($state['hands']['p4']) === 18, 'باقي اللاعبين يحملون 18 ورقة');
ok(($state['config']['opening'] ?? -1) === 0, 'لا يوجد حد افتتاح مصطنع للبناكل');
ok(($state['config']['partnership'] ?? false) === true, 'نمط الشراكة مفعّل');

// Deterministic duplicate-card regression: remove only one physical copy.
$state['hands']['p1'] = ['A_C','A_C','A_D','A_H','5_C'];
$state['melds'] = [];
$state['phase'] = 'discard';
$state['currentIndex'] = 0;
$state['starterDiscardPending'] = false; // the duplicate-card fixture starts after the mandatory opening discard
$state = $engine->applyAction($state, 'p1', ['type'=>'meld','cards'=>['A_C','A_D','A_H']]);
ok(count(array_filter($state['hands']['p1'], fn($c)=>$c==='A_C')) === 1, 'التنزيل يحذف نسخة واحدة فقط من الورقة المكررة');
ok(count($state['melds']['p1'][0]['cards'] ?? []) === 3, 'تم تسجيل المجموعة القانونية');

$state['hands']['p1'][] = 'A_S';
$state = $engine->applyAction($state, 'p1', ['type'=>'layoff','target_player'=>'p1','meld_index'=>0,'cards'=>['A_S']]);
ok(count($state['melds']['p1'][0]['cards'] ?? []) === 4, 'التركيب على المجموعة يعمل');
ok(!in_array('A_S', $state['hands']['p1'], true), 'ورقة التركيب حُذفت من اليد');

// Recycle discard pile when stock runs out.
$state['phase'] = 'draw';
$state['deck'] = [];
$state['discard'] = ['3_C','4_D','5_H'];
$before = count($state['hands']['p1']);
$state = $engine->applyAction($state, 'p1', ['type'=>'draw_deck']);
ok(count($state['hands']['p1']) === $before + 1, 'إعادة تدوير الرزمة تسمح بالسحب');
ok(count($state['discard']) === 1, 'تبقى الورقة المكشوفة أعلى كومة الرمي');

ok(!empty($state['antiCheat']['lastHash']), 'تجزئة الحالة المضادة للتلاعب موجودة');
$view = $engine->playerView($state, 'p1');
ok(!array_key_exists('deck', $view) && !array_key_exists('seed', $view), 'عرض اللاعب لا يكشف رزمة السحب أو بذرة التوزيع');
ok(isset($view['deck_count']) && is_int($view['deck_count']), 'عرض اللاعب يكشف عدد الأوراق فقط');
ok(!empty($view['antiCheat']['dealCommitment']), 'التزام التوزيع المشفّر ظاهر للتحقق اللاحق');
echo "\nBanakil V0.3 regression tests passed.\n";
