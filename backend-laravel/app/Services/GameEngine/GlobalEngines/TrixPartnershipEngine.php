<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * تركس شراكة - TrixPartnershipEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class TrixPartnershipEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'trix-partnership';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'trix',
            'players' => [4],
            'partnership' => true,
            'deck' => '52',
            'rounds' => 20,
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
            'slug'=>'trix-partnership',
            'title_ar'=>'تركس شراكة',
            'emoji'=>'🤝',
            'description'=>'محرك تركس شراكة مع نفس العقود وحساب النقاط للفريقين.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
