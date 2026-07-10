<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlatformFoundationTest extends TestCase
{
    public function test_public_health_endpoint_works(): void
    {
        $this->get('/health')->assertOk()->assertJsonStructure(['ok','version','database','counts','time']);
    }

    public function test_robots_and_sitemap_are_available(): void
    {
        $this->get('/robots.txt')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
    }

    public function test_pwa_manifest_exists(): void
    {
        $this->get('/manifest.webmanifest')->assertOk();
    }
}
