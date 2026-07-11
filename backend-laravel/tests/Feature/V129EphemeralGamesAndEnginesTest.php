<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V129EphemeralGamesAndEnginesTest extends TestCase
{
    public function test_games_menu_closes_on_selection_outside_click_and_escape(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $js=file_get_contents(public_path('assets/js/app.js'));
        $this->assertStringContainsString('data-game-link-v130',$layout);
        $this->assertStringContainsString("addEventListener('click',()=>{closeGames()",$js);
        $this->assertStringContainsString("e.key==='Escape'",$js);
        $this->assertStringContainsString('hideGamesMenu',$js);
    }

    public function test_all_catalog_engines_start_with_valid_state(): void
    {
        foreach(GameCatalog::all() as $key=>$meta){
            $players=['user:1','user:2','user:3','user:4'];
            $max=max(2,min(4,(int)($meta['max'] ?? 4)));
            $state=GameFactory::make($key)->initialState(array_slice($players,0,$max),['target'=>31]);
            $this->assertIsArray($state,$key);
            $this->assertArrayHasKey('phase',$state,$key);
            $this->assertArrayHasKey('turn',$state,$key);
            $this->assertArrayHasKey('players',$state,$key);
        }
    }

    public function test_representative_engines_accept_phase_appropriate_first_moves(): void
    {
        $tarneeb=GameFactory::make('tarneeb');
        $state=$tarneeb->initialState(['user:1','user:2','user:3','user:4'],['target'=>31]);
        $this->assertTrue($tarneeb->validate($state,(string)$state['turn'],'bid',['value'=>7]));

        $basra=GameFactory::make('basra');
        $state=$basra->initialState(['user:1','user:2'],['target'=>31]);
        $turn=(string)$state['turn'];
        $this->assertTrue($basra->validate($state,$turn,'play_card',['card'=>$state['hands'][$turn][0]]));
    }
}
