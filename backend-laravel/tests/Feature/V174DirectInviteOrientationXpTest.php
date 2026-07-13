<?php

namespace Tests\Feature;

use Tests\TestCase;

class V174DirectInviteOrientationXpTest extends TestCase
{
    public function test_v174_xp_multipliers_match_the_new_progression_contract(): void
    {
        $xp = app(\App\Services\Leveling\XpService::class);
        $this->assertSame(80, $xp->requiredXp(1));
        $this->assertSame(8000000, $xp->requiredXp(100));
    }

    public function test_v174_mobile_and_server_contracts_are_present(): void
    {
        $main = file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $this->assertStringContainsString('warqnaNavigatorKey', $main);
        $this->assertStringContainsString('queueNavigationRoute', $main);
        $this->assertStringContainsString('openPendingNavigationRoute', $main);
        $this->assertStringContainsString('_prepareDirectInviteTransfer', $main);
        $this->assertStringContainsString('await api.leaveGame(previousCode)', $main);
        $this->assertStringContainsString('await openGameRoom(navigationContext, controller, game, options: options)', $main);
    }
}
