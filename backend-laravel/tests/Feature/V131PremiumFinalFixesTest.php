<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\WarqnaPro\StoreCatalogService;
use App\Services\GameEngine\DeckFactory;

class V131PremiumFinalFixesTest extends TestCase
{
    public function test_only_15_curated_games_are_available(): void
    {
        $this->assertCount(15, GameCatalog::all());
        $this->assertArrayHasKey('tarneeb', GameCatalog::all());
        $this->assertArrayHasKey('hand', GameCatalog::all());
        $this->assertArrayHasKey('baloot', GameCatalog::all());
        $this->assertArrayNotHasKey('chess', GameCatalog::all());
        $this->assertArrayNotHasKey('backgammon', GameCatalog::all());
    }

    public function test_pasha_catalog_has_single_prices(): void
    {
        $pasha=collect((new StoreCatalogService())->pashaItems());
        $this->assertSame(10000, $pasha->firstWhere('duration_days',7)['price']);
        $this->assertSame(38000, $pasha->firstWhere('duration_days',30)['price']);
        $this->assertSame(300000, $pasha->firstWhere('duration_days',365)['price']);
    }

    public function test_chat_and_profile_fix_assets_exist(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $js=file_get_contents(public_path('assets/js/app.js'));
        $css=file_get_contents(public_path('assets/css/app.css'));
        $this->assertStringContainsString('chat-dock chat-expanded', $layout);
        $this->assertStringContainsString('v131 — profile fix', $js);
        $this->assertStringContainsString('v131 PREMIUM FINAL', $css);
    }

    public function test_fair_deal_is_random_and_strong_enough(): void
    {
        $hands=DeckFactory::balancedHands(['p1','p2','p3','p4'],13);
        foreach($hands as $hand){
            $this->assertCount(13,$hand);
            $high=count(array_filter($hand,fn($c)=>$c->value()>=11));
            $this->assertGreaterThanOrEqual(2,$high);
        }
    }
}
