<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * طرنيب 400 - Tarneeb400Engine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class Tarneeb400Engine extends GlobalCardEngineCore
{
    protected string $engineName = 'tarneeb-400';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'trick400',
            'players' => [4],
            'partnership' => true,
            'deck' => '52',
            'rounds' => 1,
            'opening' => 51,
            'targetScore' => 400,
            'targetOptions' => [400],
            'minBid' => 2,
            'maxBid' => 13,
            'trump' => true,
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
            'slug'=>'tarneeb-400',
            'title_ar'=>'طرنيب 400',
            'emoji'=>'♥️',
            'description'=>'محرك طرنيب 400/أربعمية بنظام شراكة، عقود، طرنيب، وحساب نقاط مناسب للوصول إلى 400.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
