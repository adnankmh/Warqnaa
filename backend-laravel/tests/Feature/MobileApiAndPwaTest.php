<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileApiAndPwaTest extends TestCase
{
    public function test_versioned_mobile_public_endpoints_are_available(): void
    {
        $this->get('/api/mobile/v1/health')->assertOk()->assertJsonStructure(['ok','service','version','build','time']);
        $this->get('/api/mobile/v1/app-config?platform=web')->assertOk()->assertJsonStructure(['ok','config']);
        $this->get('/api/mobile/v1/games/catalog')->assertOk()->assertJsonStructure(['ok','games']);
    }

    public function test_legacy_public_aliases_remain_compatible(): void
    {
        $this->get('/api/mobile/health')->assertOk()->assertJsonStructure(['ok','version','pwa','icons','offline','time']);
        $this->get('/api/mobile/bootstrap')->assertOk()->assertJsonStructure(['ok','app','version','apk_ready','mobile']);
        $this->get('/api/mobile/games')->assertOk()->assertJsonStructure(['ok','games']);
    }

    public function test_pwa_assets_exist(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('assets/icons/icon-512.png'));
    }
}
