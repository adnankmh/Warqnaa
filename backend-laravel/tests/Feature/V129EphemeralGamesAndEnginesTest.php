<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V129EphemeralGamesAndEnginesTest extends TestCase
{
    public function test_games_menu_is_ephemeral(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $js=file_get_contents(public_path('assets/js/app.js'));
        $this->assertStringContainsString('data-close-games-on-click', $layout);
        $this->assertStringContainsString('games-ephemeral-v129', $js);
        $this->assertStringContainsString('hideGamesMenu', $js);
    }

    public function test_all_catalog_engines_start_with_valid_state(): void
    {
        foreach(GameCatalog::all() as $key=>$meta){
            $players=['user:1','user:2','user:3','user:4','user:5','user:6'];
            $max=max(2,min(6,(int)($meta['max'] ?? 4)));
            $engine=GameFactory::make($key);
            $state=$engine->initialState(array_slice($players,0,$max), ['target'=>31]);
            $this->assertIsArray($state, $key);
            $this->assertArrayHasKey('phase',$state,$key);
            $this->assertArrayHasKey('turn',$state,$key);
            $this->assertArrayHasKey('players',$state,$key);
        }
    }

    public function test_representative_engines_accept_real_first_moves(): void
    {
        $checks=['tarneeb'=>'play_card','spades'=>'play_card','domino'=>'play_tile','ludo'=>'roll_dice','oono'=>'draw'];
        foreach($checks as $key=>$action){
            $engine=GameFactory::make($key);
            $players=['user:1','user:2','user:3','user:4'];
            $state=$engine->initialState($players, ['target'=>31]);
            $turn=$state['turn'];
            if($action==='play_card'){
                $card=$state['hands'][$turn][0] ?? null;
                $this->assertTrue($engine->validate($state,$turn,'play_card',['card'=>$card]), $key);
            } elseif($action==='play_tile'){
                $tile=$state['hands'][$turn][0] ?? null;
                $this->assertTrue($engine->validate($state,$turn,'play_tile',['tile'=>$tile,'side'=>'right']), $key);
            } else {
                $this->assertTrue($engine->validate($state,$turn,$action,[]), $key);
            }
        }
    }
}
