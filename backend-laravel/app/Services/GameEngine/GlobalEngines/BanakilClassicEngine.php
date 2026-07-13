<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * بناكل كلاسيك - BanakilClassicEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class BanakilClassicEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'banakil-classic';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'rummy',
            'players' => [2, 3, 4],
            'partnership' => false,
            'deck' => 'double-joker',
            'rounds' => 5,
            'cardsEach' => 14,
            'firstExtra' => 1,
            'opening' => 51,
            'wildTwos' => true,
            'wildValue' => 20,
            'targetScore' => 222,
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
            'slug'=>'banakil-classic',
            'title_ar'=>'بناكل كلاسيك',
            'emoji'=>'🂲',
            'description'=>'بناكل كلاسيك فردي مع افتتاح 51 متعدد المجموعات، تركيب، جوكر واثنان بري، وسحب ورمي مضبوطين من الخادم.',
            'version'=>'final-v1',
            'players'=>[2, 3, 4],
            'partnership'=>false,
        ];
    }
}
