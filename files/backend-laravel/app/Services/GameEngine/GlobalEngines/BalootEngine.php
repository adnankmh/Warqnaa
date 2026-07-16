<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * بلوت - BalootEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class BalootEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'baloot';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'baloot',
            'players' => [4],
            'partnership' => true,
            'deck' => 'baloot32',
            'rounds' => 1,
            'opening' => 51,
            'targetScore' => 152,
            'targetOptions' => [152],
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
            'slug'=>'baloot',
            'title_ar'=>'بلوت',
            'emoji'=>'🪙',
            'description'=>'محرك بلوت 4 لاعبين شراكة: 32 ورقة، صن أو حكم، ترتيب أوراق مختلف لكل عقد، اتباع النوع وحساب نقاط اللمّات وآخر أكلة.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
