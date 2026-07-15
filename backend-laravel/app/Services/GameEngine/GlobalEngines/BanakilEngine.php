<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * بناكل - BanakilEngine
 * توزيع 18 ورقة لكل لاعب و19 للاعب البادئ، ثم يبدأ البادئ بالرمي.
 */
class BanakilEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'banakil';

    protected function defaultConfig(): array
    {
        return [
            'mode' => 'rummy',
            'players' => [2, 4],
            'partnership' => true,
            'deck' => 'double-joker',
            'rounds' => 7,
            'cardsEach' => 18,
            'starterExtraCard' => true,
            'starterMustDiscard' => true,
            'opening' => 0,
            'banakilScoring' => true,
            'targetScore' => 222,
            'targetOptions' => [150, 222, 300],
            'wildTwos' => true,
            'maxJokersPerMeld' => 1,
            'maxTwosPerMeld' => 1,
            'setRanks' => ['3', 'A'],
            'trump' => false,
            'security' => [
                'serverAuthoritative' => true,
                'stateHash' => true,
                'replay' => true,
            ],
        ];
    }

    public function gameInfo(): array
    {
        return [
            'slug' => 'banakil',
            'title_ar' => 'بناكل',
            'emoji' => '🂮',
            'description' => 'بناكل شراكة بتوزيع 18+19، تنزيل وتركيب قانوني، جوكر واثنان كأوراق بديلة، وسجل حركات موثّق.',
            'version' => 'v0.3',
            'players' => [2, 4],
            'partnership' => true,
        ];
    }
}
