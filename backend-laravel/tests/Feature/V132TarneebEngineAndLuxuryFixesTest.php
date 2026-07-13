<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GameEngine\TarneebRules;
use App\Services\WarqnaPro\StoreCatalogService;

class V132TarneebEngineAndLuxuryFixesTest extends TestCase
{
    public function test_tarneeb_uses_standalone_engine_state(): void
    {
        $engine=new TarneebRules();
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'],['target'=>41]);
        $this->assertArrayHasKey('_tarneeb_v2',$state);
        $this->assertSame('standalone_tarneeb_v132',$state['engine_quality']);
        $this->assertSame('bidding',$state['phase']);
        $this->assertCount(13,$state['hands']['user:1']);
    }

    public function test_tarneeb_first_bid_or_pass_is_valid(): void
    {
        $engine=new TarneebRules();
        $state=$engine->initialState(['user:1','user:2','user:3','user:4'],['target'=>41]);
        $turn=$state['turn'];
        $this->assertTrue($engine->validate($state,$turn,'pass',[]) || $engine->validate($state,$turn,'bid',['value'=>7]));
    }

    public function test_store_has_50_tables_and_new_pasha_days(): void
    {
        $store=new StoreCatalogService();
        $this->assertCount(140,$store->tableSkins());
        $pasha=collect($store->pashaItems());
        $this->assertSame(1700,$pasha->firstWhere('duration_days',1)['price']);
        $this->assertSame(5000,$pasha->firstWhere('duration_days',3)['price']);
        $this->assertSame(105000,$pasha->firstWhere('duration_days',90)['price']);
    }
}
