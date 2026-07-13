<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * بناكل - BanakilEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class BanakilEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'banakil';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'rummy',
            'players' => [2, 3, 4],
            'partnership' => false,
            'deck' => 'double-joker',
            'rounds' => 5,
            'cardsEach' => 14,
            'opening' => 40,
            'targetScore' => 41,
            'targetOptions' => [],
            'minBid' => 7,
            'maxBid' => 13,
            'trump' => false,
            'security' => [
                'serverAuthoritative' => true,
                'stateHash' => true,
                'replay' => true
            ]
        ];
    }

    public function gameInfo(): array
    {
        return [
            'slug'=>'banakil',
            'title_ar'=>'بناكل',
            'emoji'=>'🂮',
            'description'=>'محرك بناكل فردي 2-4 لاعبين مع تنزيل مجموعات وسلاسل وجوكر وحساب نقاط.',
            'version'=>'final-v1',
            'players'=>[2, 3, 4],
            'partnership'=>false,
        ];
    }
}
