<?php

namespace Tests\Feature;

use App\Services\Leveling\XpService;
use Tests\TestCase;

class V174DirectInviteOrientationXpTest extends TestCase
{
    private function baseXp(int $level): int
    {
        $high = $level - 7;
        return 1000 + ($high * 220) + ($high * $high * 35);
    }

    public function test_v174_xp_multipliers_match_the_new_progression_contract(): void
    {
        $xp = app(XpService::class);
        $cases = [
            39 => 1.00,
            40 => 1.20,
            50 => 1.20,
            51 => 1.30,
            59 => 1.30,
            60 => 1.50,
            69 => 1.50,
            70 => 1.80,
            79 => 1.80,
            80 => 2.20,
            89 => 2.20,
            90 => 6.00,
            100 => 6.00,
            101 => 1.00,
        ];
        foreach ($cases as $level => $multiplier) {
            $this->assertSame((int) round($this->baseXp($level) * $multiplier), $xp->requiredXp($level), 'Unexpected XP at level '.$level);
        }
    }

    public function test_v174_mobile_and_server_contracts_are_present(): void
    {
        $main = file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $api = file_get_contents(base_path('../flutter_app/lib/services/api_client.dart'));
        $game = file_get_contents(app_path('Http/Controllers/MobileGameController.php'));
        $progression = file_get_contents(app_path('Services/Progression/ProgressionService.php'));
        $routes = file_get_contents(base_path('routes/api.php'));

        $this->assertStringContainsString('warqnaNavigatorKey', $main);
        $this->assertStringContainsString('queueNavigationRoute', $main);
        $this->assertStringContainsString('openPendingNavigationRoute', $main);
        $this->assertStringContainsString('_prepareDirectInviteTransfer', $main);
        $this->assertStringContainsString('await api.leaveGame(previousCode)', $main);
        $this->assertStringContainsString('await openGameRoom(navigationContext, controller, game, options: options)', $main);
        $this->assertStringContainsString('consumeServerProgression', $main);
        $this->assertStringContainsString('DeviceOrientation.portraitUp', $main);
        $this->assertStringNotContainsString('await controller.setLandscapeMode(true)', $main);
        $this->assertStringContainsString('gameSessionPreview', $api);
        $this->assertStringContainsString("'/games/session/{room:code}/preview'", $routes);
        $this->assertStringContainsString("if (\$player->is_bot || !\$player->user) continue;", $game);
        $this->assertStringContainsString("\$copy['progression_popup'] = is_array(\$ownPopup)", $game);
        $this->assertStringContainsString("'won'=>(bool)(\$meta['won'] ?? false)", $progression);
    }
}
