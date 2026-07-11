<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlatformFoundationTest extends TestCase
{
    public function test_public_health_endpoint_works(): void
    {
        $this->get('/health')->assertOk()->assertJsonStructure(['ok','version','database','counts','time']);
    }

    public function test_robots_and_sitemap_are_available_even_without_seeded_data(): void
    {
        $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type','text/plain; charset=UTF-8');
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset',false);
    }

    public function test_pwa_manifest_is_served_by_laravel(): void
    {
        $this->get('/manifest.webmanifest')->assertOk()->assertHeader('Content-Type','application/manifest+json; charset=UTF-8');
    }
}
