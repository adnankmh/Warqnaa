<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WarqnaPro\EngineCoverageService;
use App\Services\Games\GameCatalog;

class V127StoreAndEngineCoverageTest extends TestCase
{
    public function test_engine_coverage_covers_catalog(): void
    {
        $coverage=(new EngineCoverageService())->summary();
        $this->assertSame(count(GameCatalog::all()),$coverage['total']);
        $this->assertGreaterThanOrEqual(90,$coverage['percent']);
    }

    public function test_store_view_uses_stable_separated_sections(): void
    {
        $html=file_get_contents(resource_path('views/store/index.blade.php'));
        $this->assertStringContainsString('store-category-section-v127',$html);
        $this->assertStringContainsString('data-store-section-v127="{{$cat}}"',$html);
        $this->assertStringContainsString("'table'=>'الطاولات'",$html);
        $this->assertStringContainsString("'pasha'=>'أيام الباشا'",$html);
        $this->assertStringContainsString('data-warqna-store-contract="v158"',$html);
    }
}
