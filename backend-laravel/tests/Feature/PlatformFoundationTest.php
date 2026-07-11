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
        $robots = $this->get('/robots.txt')->assertOk();
        $this->assertSame('text/plain; charset=utf-8', strtolower((string) $robots->headers->get('Content-Type')));
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset',false);
    }

    public function test_pwa_manifest_is_served_by_laravel(): void
    {
        $manifest = $this->get('/manifest.webmanifest')->assertOk();
        $this->assertSame('application/manifest+json; charset=utf-8', strtolower((string) $manifest->headers->get('Content-Type')));
    }
}
