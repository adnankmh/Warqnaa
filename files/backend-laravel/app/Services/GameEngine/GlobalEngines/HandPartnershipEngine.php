<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * هاند شراكة - HandPartnershipEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class HandPartnershipEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'hand-partnership';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'rummy',
            'players' => [4],
            'partnership' => true,
            'deck' => 'double-joker',
            'rounds' => 5,
            'cardsEach' => 14,
            'starterExtraCard' => true,
            'starterMustDiscard' => true,
            'opening' => 51,
            'teamOpening' => true,
            'openingEscalates' => true,
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
            'slug'=>'hand-partnership',
            'title_ar'=>'هاند شراكة',
            'emoji'=>'🤝',
            'description'=>'محرك هاند شراكة 4 لاعبين: سحب، رمي، تنزيل مجموعات وسلاسل، افتتاح 51، حساب جولات وشراكة.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
