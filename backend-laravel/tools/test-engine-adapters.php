<?php
/**
 * Standalone adapter smoke test that does not require Composer/Laravel boot.
 * Run from backend-laravel with: php tools/test-engine-adapters.php
 */
$base = dirname(__DIR__) . '/app/Services/GameEngine';
foreach ([
    'GameRuleContract.php',
    'Card.php',
    'DeckFactory.php',
    'AbstractCardRules.php',
    'DominoRules.php',
    'BasraRules.php',
    'BackgammonRules.php',
    'JackarooRules.php',
    'ChessRules.php',
    'TarneebRules.php',
    'GlobalCardEngineRules.php',
    'UniversalSocialGameRules.php',
    'EngineRegistry.php',
    'GameFactory.php',
] as $file) {
    require_once $base . '/' . $file;
}

use App\Services\GameEngine\{EngineRegistry, GameFactory};

$keys = [
    'tarneeb', 'syrian_tarneeb', 'tarneeb_400',
    'trix', 'trix_partner', 'trix_complex',
    'hand', 'hand_partner', 'saudi_hand',
    'banakil', 'pinochle', 'baloot', 'solitaire_multiplayer',
    'domino', 'basra', 'backgammon', 'jackaroo', 'chess',
];

$failures = [];
foreach ($keys as $key) {
    try {
        $meta = EngineRegistry::get($key);
        if (!$meta) {
            throw new RuntimeException('missing registry entry');
        }
        $players = [];
        for ($i = 0; $i < (int) $meta['max']; $i++) {
            $players[] = $i === 0 ? 'user:1' : 'bot:' . $i;
        }
        $state = GameFactory::make($key)->initialState($players, ['target' => 41]);
        if (empty($state['players']) || empty($state['phase'])) {
            throw new RuntimeException('invalid initial state');
        }
        $hand = count($state['hands']['user:1'] ?? []);
        printf("[PASS] %-22s phase=%-16s hand=%-2d quality=%s\n",
            $key,
            (string) $state['phase'],
            $hand,
            (string) ($state['engine_quality'] ?? 'universal')
        );
    } catch (Throwable $exception) {
        $failures[] = $key . ': ' . $exception->getMessage();
        printf("[FAIL] %s — %s\n", $key, $exception->getMessage());
    }
}

if ($failures) {
    fwrite(STDERR, "\nEngine adapter failures:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "\nAll uploaded engine adapters initialized successfully.\n";
