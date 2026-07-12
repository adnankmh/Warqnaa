<?php

namespace Tests\Feature;

use App\Services\WarqnaPro\StoreCatalogService;
use Tests\TestCase;

class V172BrandTableCatalogTest extends TestCase
{
    public function test_legacy_and_reference_tables_are_both_available_without_duplicates(): void
    {
        $tables = app(StoreCatalogService::class)->tableSkins();
        $keys = array_column($tables, 'key');

        $this->assertCount(140, $tables);
        $this->assertCount(140, array_unique($keys));
        $this->assertContains('table_premium_01', $keys);
        $this->assertContains('table_premium_50', $keys);
        $this->assertContains('table_reference_01', $keys);
        $this->assertContains('table_reference_40', $keys);

        $reference = array_values(array_filter($tables, fn (array $item) => str_starts_with($item['key'], 'table_reference_')));
        $this->assertCount(40, $reference);
        foreach ($reference as $item) {
            $this->assertSame('table', $item['category']);
            $this->assertNotEmpty(data_get($item, 'payload.image_asset'));
            $this->assertStringStartsWith('reference_', (string) data_get($item, 'payload.collection'));
        }
    }
}
