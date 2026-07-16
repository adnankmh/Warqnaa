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
            'mode' => 'trick',
            'players' => [4],
            'partnership' => true,
            'deck' => '52',
            'rounds' => 1,
            'opening' => 51,
            'targetScore' => 41,
            'targetOptions' => [31, 41, 61],
            'minBid' => 7,
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
            'slug'=>'syrian-tarneeb',
            'title_ar'=>'طرنيب سوري',
            'emoji'=>'♠️',
            'description'=>'محرك طرنيب سوري كامل بنظام شراكة 4 لاعبين، طلب 7-13، طرنيب، اتباع النوع، حساب الأكلات والنقاط.',
            'version'=>'final-v1',
            'players'=>[4],
            'partnership'=>true,
        ];
    }
}
