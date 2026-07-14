<?php

namespace Tests\Feature;

use Tests\TestCase;

class V05GlobalPlatformContractTest extends TestCase
{
    public function test_v05_global_source_contracts_are_present(): void
    {
        $root = base_path('..');
        $main = file_get_contents($root.'/flutter_app/lib/main.dart');
        $v05 = file_get_contents($root.'/flutter_app/lib/v05_release.dart');
        $api = file_get_contents(app_path('Http/Controllers/MobileApiController.php'));
        $road = file_get_contents(app_path('Services/WarqnaPro/ChallengeRoadService.php'));
        $deck = file_get_contents(app_path('Services/GameEngine/DeckFactory.php'));
        $routes = file_get_contents(base_path('routes/api.php'));

        $this->assertStringContainsString("part 'v05_release.dart';", $main);
        $this->assertStringContainsString('favoriteGameIdsV05', $main);
        $this->assertStringContainsString('class PashaFezV05', $v05);
        $this->assertStringContainsString('class V05GlobalControlsOverlay', $v05);
        $this->assertStringContainsString('public function purchase', $api);
        $this->assertStringContainsString('MAX_LIVES = 5', $road);
        $this->assertStringContainsString('ALLOWED_STAGES = [10, 12, 15]', $road);
        $this->assertStringContainsString('random_int', $deck);
        $this->assertStringContainsString('/v05/challenge-road', $routes);
        $this->assertStringContainsString('/v05/clubs', $routes);
    }

    public function test_v05_release_metadata_is_consistent(): void
    {
        $meta = json_decode(file_get_contents(base_path('../RELEASE_VERSION.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('0.5.0', $meta['version']);
        $this->assertSame(500, $meta['build']);
        $this->assertSame('0.5.0+500', $meta['full']);
    }
}
