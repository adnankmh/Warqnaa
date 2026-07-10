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
            $this->assertIsArray($state, $key);
            $this->assertArrayHasKey('phase',$state, $key);
            $this->assertArrayHasKey('turn',$state, $key);
        }
    }

    public function test_tarneeb_accepts_multiple_card_formats(): void
    {
        $engine=GameFactory::make('tarneeb');
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
        $state['phase']='playing';
        $state['turn']='user:1';
        $state['trump']='clubs';
        $state['trick']=[];
        $state['hands']['user:1']=['A_clubs','K_hearts'];
        $this->assertTrue($engine->validate($state,'user:1','play_card',['rank'=>'A','suit'=>'♣']));
        $this->assertTrue($engine->validate($state,'user:1','play_card',['card_id'=>'A_clubs']));
        $this->assertTrue($engine->validate($state,'user:1','play_card',['card'=>'A سنك']));
    }
}
