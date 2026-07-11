<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GameEngine\GameFactory;

class GameEnginesSmokeTest extends TestCase
{
    public function test_core_game_engines_create_initial_state(): void
    {
        foreach(['tarneeb','hand','banakil','trix','baloot','basra'] as $key){
            $engine=GameFactory::make($key);
            $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
            $this->assertIsArray($state, $key.' state is not array');
            $this->assertArrayHasKey('phase',$state, $key.' has no phase');
            $this->assertArrayHasKey('turn',$state, $key.' has no turn');
        }
    }

    public function test_tarneeb_bidding_and_trump_flow_uses_authoritative_turn(): void
    {
        $engine=GameFactory::make('tarneeb');
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'], ['target'=>31]);
        $bidder=(string)$state['turn'];

        $this->assertTrue($engine->validate($state,$bidder,'bid',['value'=>7]));
        $state=$engine->apply($state,$bidder,'bid',['value'=>7]);

        for($i=0;$i<3;$i++){
            $passingPlayer=(string)$state['turn'];
            $this->assertNotSame($bidder,$passingPlayer);
            $this->assertTrue($engine->validate($state,$passingPlayer,'pass',[]));
            $state=$engine->apply($state,$passingPlayer,'pass',[]);
        }

        $this->assertSame('choose_trump',$state['phase']);
        $this->assertSame($bidder,$state['turn']);
        $this->assertTrue($engine->validate($state,$bidder,'choose_trump',['suit'=>'clubs']));
    }
}
