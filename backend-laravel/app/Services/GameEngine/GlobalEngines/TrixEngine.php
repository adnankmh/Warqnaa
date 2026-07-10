<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * تركس - TrixEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class TrixEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'trix';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'trix',
            'players' => [4],
            'partnership' => false,
            'deck' => '52',
            'rounds' => 4,
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
            'slug'=>'trix',
            'title_ar'=>'تركس',
            'emoji'=>'👑',
            'description'=>'محرك تركس فردي: ممالك، عقود، لطوش، بنات، ديناري، شيخ الكبة، تركس، وحساب جزاءات.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>false,
        ];
    }
}
