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
        $this->assertSame(count(GameCatalog::all()), $coverage['total']);
        $this->assertGreaterThanOrEqual(90, $coverage['percent']);
    }

    public function test_store_view_uses_separated_sections(): void
    {
        $path=resource_path('views/store/index.blade.php');
        $html=file_get_contents($path);
        $this->assertStringContainsString('store-category-section-v127', $html);
        $this->assertStringContainsString('data-store-section-v127="table"', $html);
        $this->assertStringContainsString('data-store-section-v127="pasha"', $html);
        $this->assertStringNotContainsString("'all'=>'الكل'", $html);
    }
}
