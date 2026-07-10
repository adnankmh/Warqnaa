<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V122CatalogAndEnginesTest extends TestCase
{
    public function test_v122_catalog_contains_many_social_card_games(): void
    {
        $games = GameCatalog::all();
        $this->assertGreaterThanOrEqual(40, count($games));
        foreach(['tarneeb','hand','trix','baloot','domino','ludo','jackaroo','spades','hearts','rummy','concan','kout4','basra','oono','chess'] as $key){
            $this->assertArrayHasKey($key, $games);
        }
    }

    public function test_v122_game_factory_can_create_engines_for_catalog(): void
    {
        foreach(array_keys(GameCatalog::all()) as $key){
            $engine = GameFactory::make($key);
            $this->assertIsObject($engine, $key.' has no engine object');
            $this->assertTrue(method_exists($engine,'initialState'), $key.' engine has no initialState');
        }
    }
}
