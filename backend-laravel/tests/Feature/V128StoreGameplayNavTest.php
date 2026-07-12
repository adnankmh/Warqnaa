<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WarqnaPro\StoreCatalogService;
use App\Services\GameEngine\DeckFactory;

class V128StoreGameplayNavTest extends TestCase
{
    public function test_store_catalog_keeps_expanded_tables_and_card_backs(): void
    {
        $service=new StoreCatalogService();
        $this->assertCount(140,$service->tableSkins());
        $this->assertCount(40,$service->cardBacks());
        $pasha7=collect($service->pashaItems())->firstWhere('duration_days',7);
        $this->assertSame(10000,$pasha7['price']);
    }

    public function test_tarneeb_deal_is_complete_equal_and_duplicate_free(): void
    {
        $hands=DeckFactory::balancedHands(['p1','p2','p3','p4'],13);
        $cards=[];
        foreach($hands as $hand){
            $this->assertCount(13,$hand);
            foreach($hand as $card) $cards[]=$card->id();
        }
        $this->assertCount(52,$cards);
        $this->assertCount(52,array_unique($cards));
    }

    public function test_navigation_and_store_contracts_exist(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $store=file_get_contents(resource_path('views/store/index.blade.php'));
        $this->assertStringContainsString('games-top-only-v128',$layout);
        $this->assertStringContainsString('wz-games-menu-v130',$layout);
        $this->assertStringContainsString('data-game-link-v130',$layout);
        $this->assertStringContainsString('store-category-section-v127',$store);
    }
}
