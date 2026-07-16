<?php
require_once __DIR__ . '/GlobalCardEngineCore.php';

/**
 * طرنيب سوري - SyrianTarneebEngine
 * نسخة عالمية مستقلة لمحركات Warqna.
 */
class SyrianTarneebEngine extends GlobalCardEngineCore
{
    protected string $engineName = 'syrian-tarneeb';
    protected function defaultConfig(): array
    {
        return [
            'mode' => 'syrian41',
            'players' => [4],
            'partnership' => true,
            'individualScores' => true,
            'deck' => '52',
            'rounds' => 1,
            'opening' => 51,
            'targetScore' => 41,
            'targetOptions' => [31, 41, 61],
            'minBid' => 2,
            'maxBid' => 13,
            'trump' => false,
            'fixedTrumpFromDeal' => true,
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
            'slug'=>'syrian-tarneeb',
            'title_ar'=>'طرنيب سوري',
            'emoji'=>'♠️',
            'description'=>'محرك طرنيب سوري 41: طلب مستقل 2-13 لكل لاعب، إعادة توزيع إذا كان المجموع أقل من 11، وطرنيب ثابت من الورقة المكشوفة وحساب فردي داخل الفريق.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
