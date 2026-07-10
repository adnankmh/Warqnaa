<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WarqnaPro\StoreCatalogService;
use App\Services\GameEngine\DeckFactory;

class V128StoreGameplayNavTest extends TestCase
{
    public function test_store_catalog_has_40_tables_and_40_card_backs(): void
    {
        $service=new StoreCatalogService();
        $this->assertCount(40, $service->tableSkins());
        $this->assertCount(40, $service->cardBacks());
        $pasha7=collect($service->pashaItems())->firstWhere('duration_days',7);
        $this->assertSame(10000, $pasha7['price']);
    }

    public function test_balanced_tarneeb_deal_gives_each_player_high_cards(): void
    {
        $hands=DeckFactory::balancedHands(['p1','p2','p3','p4'],13);
        foreach($hands as $hand){
            $high=0;
            foreach($hand as $card){
                if($card->value() >= 11) $high++;
            }
            $this->assertGreaterThanOrEqual(2, $high);
        }
    }

    public function test_nav_games_dropdown_and_store_sections_exist(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $store=file_get_contents(resource_path('views/store/index.blade.php'));
        $this->assertStringContainsString('games-top-only-v128', $layout);
        $this->assertStringContainsString('curtain-grid-v127', $layout);
        $this->assertStringContainsString('store-category-section-v127', $store);
    }
}
