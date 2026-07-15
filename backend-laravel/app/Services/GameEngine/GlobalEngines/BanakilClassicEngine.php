<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * بناكل كلاسيك - إعداد كلاسيكي قابل للتخصيص مع قواعد 18+19.
 */
class BanakilClassicEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'banakil-classic';

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
            'targetScore' => 150,
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
            'slug' => 'banakil-classic',
            'title_ar' => 'بناكل كلاسيك',
            'emoji' => '🂲',
            'description' => 'نسخة كلاسيكية من البناكل بتوزيع 18+19، لعب شراكة، وتركيب على مجموعات الفريق.',
            'version' => 'v0.3',
            'players' => [2, 4],
            'partnership' => true,
        ];
    }
}
