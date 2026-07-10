<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * هاند سعودي - SaudiHandEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class SaudiHandEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'saudi-hand';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'rummy',
            'players' => [2, 3, 4],
            'partnership' => false,
            'deck' => 'double-joker',
            'rounds' => 5,
            'cardsEach' => 14,
            'opening' => 51,
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
            'slug'=>'saudi-hand',
            'title_ar'=>'هاند سعودي',
            'emoji'=>'🂱',
            'description'=>'محرك هاند سعودي فردي 2-4 لاعبين بقوانين السحب والرمي والمجموعات والسلاسل والهاند الكامل.',
            'version'=>'final-v1',
            'players'=>[2, 3, 4],
            'partnership'=>false,
        ];
    }
}
