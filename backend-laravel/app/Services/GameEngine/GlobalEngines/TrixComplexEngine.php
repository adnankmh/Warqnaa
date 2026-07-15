<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * تركس كمبلكس - TrixComplexEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class TrixComplexEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'trix-complex';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'trix-complex',
            'players' => [4],
            'partnership' => false,
            'deck' => '52',
            'rounds' => 8,
            'opening' => 51,
            'targetScore' => 999999,
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
            'slug'=>'trix-complex',
            'title_ar'=>'تركس كمبلكس',
            'emoji'=>'💎',
            'description'=>'محرك تركس كمبلكس بعقود مجمعة وحساب كامل لعقود الكمبلكس.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>false,
        ];
    }
}
