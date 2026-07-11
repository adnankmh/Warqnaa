<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V122CatalogAndEnginesTest extends TestCase
{
    public function test_current_curated_catalog_matches_product_contract(): void
    {
        $games=GameCatalog::all();
        $expected=[
            'tarneeb','syrian_tarneeb','tarneeb_400',
            'trix','trix_partner','trix_complex',
            'hand','hand_partner','saudi_hand','banakil','baloot','basra',
        ];
        $this->assertSame($expected,array_keys($games));
        foreach(['domino','ludo','jackaroo','chess','backgammon','solitaire_multiplayer'] as $removed){
            $this->assertArrayNotHasKey($removed,$games);
        }
    }

    public function test_every_curated_game_has_a_real_engine_object(): void
    {
        foreach(array_keys(GameCatalog::all()) as $key){
            $engine=GameFactory::make($key);
            $this->assertTrue(method_exists($engine,'initialState'),$key.' engine has no initialState');
            $this->assertTrue(method_exists($engine,'validate'),$key.' engine has no validate');
            $this->assertTrue(method_exists($engine,'apply'),$key.' engine has no apply');
        }
    }
}
