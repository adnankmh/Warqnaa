<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V123FinalQualityTest extends TestCase
{
    public function test_every_catalog_game_has_safe_initial_state(): void
    {
        $players=['user:1','user:2','user:3','user:4'];
        foreach(GameCatalog::all() as $key=>$meta){
            $engine=GameFactory::make($key);
            $state=$engine->initialState(array_slice($players,0,max(2,min(4,(int)($meta['max'] ?? 4)))), ['target'=>31]);
            $this->assertIsArray($state,$key);
            $this->assertArrayHasKey('phase',$state,$key);
            $this->assertArrayHasKey('turn',$state,$key);
        }
    }

    public function test_tarneeb_accepts_multiple_card_formats_in_real_playing_state(): void
    {
        $engine=GameFactory::make('tarneeb');
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
        $bidder=(string)$state['turn'];
        $state=$engine->apply($state,$bidder,'bid',['value'=>7]);
        for($i=0;$i<3;$i++) $state=$engine->apply($state,(string)$state['turn'],'pass',[]);
        $state=$engine->apply($state,$bidder,'choose_trump',['suit'=>'clubs']);

        $card=(string)$state['hands'][$bidder][0];
        [$rank,$suit]=explode('_',$card,2);
        $symbol=['clubs'=>'♣','diamonds'=>'♦','spades'=>'♠','hearts'=>'♥'][$suit];
        $arabic=['clubs'=>'سنك','diamonds'=>'ديناري','spades'=>'بستوني','hearts'=>'كبة'][$suit];

        $this->assertTrue($engine->validate($state,$bidder,'play_card',['rank'=>$rank,'suit'=>$symbol]));
        $this->assertTrue($engine->validate($state,$bidder,'play_card',['card_id'=>$card]));
        $this->assertTrue($engine->validate($state,$bidder,'play_card',['card'=>$rank.' '.$arabic]));
    }
}
