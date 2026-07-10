<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GameEngine\GameFactory;

class GameEnginesSmokeTest extends TestCase
{
    public function test_core_game_engines_create_initial_state(): void
    {
        foreach(['tarneeb','hand','banakil','domino','backgammon','trix','baloot'] as $key){
            $engine=GameFactory::make($key);
            $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
            $this->assertIsArray($state, $key.' state is not array');
            $this->assertArrayHasKey('phase',$state, $key.' has no phase');
        }
    }

    public function test_tarneeb_bidding_and_trump_flow(): void
    {
        $engine=GameFactory::make('tarneeb');
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
        $this->assertTrue($engine->validate($state,'user:1','bid',['value'=>7]));
        $state=$engine->apply($state,'user:1','bid',['value'=>7]);
        $state=$engine->apply($state,'user:2','pass',[]);
        $state=$engine->apply($state,'user:3','pass',[]);
        $state=$engine->apply($state,'user:4','pass',[]);
        $this->assertEquals('choose_trump',$state['phase']);
        $this->assertTrue($engine->validate($state,'user:1','choose_trump',['suit'=>'clubs']));
    }
}
