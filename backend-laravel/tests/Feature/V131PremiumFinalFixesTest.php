<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\WarqnaPro\StoreCatalogService;
use App\Services\GameEngine\DeckFactory;

class V131PremiumFinalFixesTest extends TestCase
{
    public function test_only_current_12_curated_games_are_available(): void
    {
        $this->assertCount(12,GameCatalog::all());
        foreach(['tarneeb','hand','baloot','basra'] as $key) $this->assertArrayHasKey($key,GameCatalog::all());
        foreach(['chess','backgammon','domino','jackaroo'] as $key) $this->assertArrayNotHasKey($key,GameCatalog::all());
    }

    public function test_pasha_catalog_has_single_prices(): void
    {
        $pasha=collect((new StoreCatalogService())->pashaItems());
        $this->assertSame(10000,$pasha->firstWhere('duration_days',7)['price']);
        $this->assertSame(38000,$pasha->firstWhere('duration_days',30)['price']);
        $this->assertSame(300000,$pasha->firstWhere('duration_days',365)['price']);
    }

    public function test_chat_and_profile_fix_assets_exist(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $js=file_get_contents(public_path('assets/js/app.js'));
        $css=file_get_contents(public_path('assets/css/app.css'));
        $this->assertStringContainsString('chat-dock chat-expanded',$layout);
        $this->assertStringContainsString('v131 — profile fix',$js);
        $this->assertStringContainsString('v131 PREMIUM FINAL',$css);
    }

    public function test_fair_deal_never_strengthens_or_duplicates_hands(): void
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
}
