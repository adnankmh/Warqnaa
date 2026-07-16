<?php
/**
 * Repeated engine initialization and first-legal-move stress audit.
 * Standalone: no Composer/Laravel boot required.
 */
$base = dirname(__DIR__) . '/app/Services/GameEngine';
require_once dirname(__DIR__) . '/app/Services/WarqnaPro/PlayActionNormalizer.php';
foreach ([
    'GameRuleContract.php','Card.php','DeckFactory.php','AbstractCardRules.php',
    'DominoRules.php','BasraRules.php','BackgammonRules.php','JackarooRules.php',
    'ChessRules.php','TarneebRules.php','GlobalCardEngineRules.php',
    'UniversalSocialGameRules.php','EngineRegistry.php','GameFactory.php',
] as $file) require_once $base . '/' . $file;

use App\Services\GameEngine\{EngineRegistry, GameFactory};

$keys = [
    'tarneeb','syrian_tarneeb','tarneeb_400','trix','trix_partner','trix_complex',
    'hand','hand_partner','saudi_hand','banakil','pinochle','baloot',
    'solitaire_multiplayer','domino','basra','backgammon','jackaroo','chess',
];
$iterations = 20;
$checks = 0;
$errors = [];
foreach ($keys as $key) {
    for ($run = 1; $run <= $iterations; $run++) {
        try {
            $meta = EngineRegistry::get($key);
            if (!$meta) throw new RuntimeException('missing registry metadata');
            $count = (int)($meta['max'] ?? 4);
            $players = [];
            for ($i=0; $i<$count; $i++) $players[] = $i === 0 ? 'user:1' : 'bot:'.$i;
            $rules = GameFactory::make($key);
            $state = $rules->initialState($players, ['target'=>41, 'player_count'=>$count]);
            if (empty($state['players']) || empty($state['phase']) || empty($state['turn'])) {
                throw new RuntimeException('incomplete initial state');
            }
            if (count(array_unique($state['players'])) !== count($state['players'])) {
                throw new RuntimeException('duplicate player identifiers');
            }
            foreach (($state['hands'] ?? []) as $pid=>$hand) {
                if (!is_array($hand) || in_array(null, $hand, true) || in_array('', $hand, true)) {
                    throw new RuntimeException('invalid card in hand for '.$pid);
                }
            }
            $turn = (string)$state['turn'];
            if (method_exists($rules, 'availableActions')) {
                $actions = $rules->availableActions($state, $turn);
                if (!$actions && $key === 'jackaroo' && !empty($state['hands'][$turn])) {
                    if (!$rules->validate($state, $turn, 'pass', [])) {
                        throw new RuntimeException('Jackaroo has no move but does not permit the required pass');
                    }
                    $next = $rules->apply($state, $turn, 'pass', []);
                    if (!empty($next['last_error_message'])) {
                        throw new RuntimeException('Jackaroo legal pass failed: '.$next['last_error_message']);
                    }
                } elseif (!$actions && ($state['phase'] ?? '') !== 'finished') {
                    throw new RuntimeException('no legal actions for current player in phase '.($state['phase'] ?? 'unknown'));
                } elseif ($actions) {
                    $action = $actions[0];
                    $type = (string)($action['type'] ?? '');
                    if ($type === '' || $type === 'wait') throw new RuntimeException('invalid first legal action');
                    $payload = $action;
                    unset($payload['type'], $payload['reason']);
                    if (!$rules->validate($state, $turn, $type, $payload)) {
                        throw new RuntimeException('advertised action failed validation: '.$type);
                    }
                    // Chess move timestamps depend on Laravel's now() helper; its
                    // complete move logic is covered by test-v142-rule-cores.php.
                    if ($key !== 'chess') {
                        $next = $rules->apply($state, $turn, $type, $payload);
                        if (!empty($next['last_error']) || !empty($next['last_error_message'])) {
                            throw new RuntimeException('legal action failed: '.($next['last_error_message'] ?? $next['last_error']));
                        }
                    }
                }
            }
            $checks++;
        } catch (Throwable $e) {
            $errors[] = $key.' run '.$run.': '.$e->getMessage();
            break;
        }
    }
    if (!array_filter($errors, fn($e)=>str_starts_with($e, $key.' '))) {
        echo "[PASS] {$key}: {$iterations} randomized starts and first legal moves\n";
    }
}
if ($errors) {
    fwrite(STDERR, "\nEngine stress failures:\n- ".implode("\n- ",$errors)."\n");
    exit(1);
}
echo "\n[PASS] {$checks} engine stress scenarios completed without invalid state or legal-move failure.\n";
