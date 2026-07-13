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
            'players' => [4],
            'partnership' => true,
            'deck' => 'double-joker',
            'rounds' => 7,
            'cardsEach' => 18,
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
            'slug'=>'banakil',
            'title_ar'=>'بناكل',
            'emoji'=>'🂮',
            'description'=>'محرك بناكل شراكة لأربعة لاعبين: 18 ورقة، أول لاعب بورقة إضافية، افتتاح 51 عبر عدة مجموعات، تركيب، جوكر واثنان بري، وحساب فرق كامل.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
