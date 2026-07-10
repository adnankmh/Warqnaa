<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * سوليتير تنافسي - CompetitiveSolitaireEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class CompetitiveSolitaireEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'competitive-solitaire';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'solitaire',
            'players' => [2, 3, 4],
            'partnership' => false,
            'deck' => 'multi52',
            'rounds' => 1,
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
            'slug'=>'competitive-solitaire',
            'title_ar'=>'سوليتير تنافسي',
            'emoji'=>'🃁',
            'description'=>'محرك سوليتير تنافسي 2-4 لاعبين مع Stock/Waste/Foundation وحساب سرعة وإنجاز.',
            'version'=>'final-v1',
            'players'=>[2, 3, 4],
            'partnership'=>false,
        ];
    }
}
