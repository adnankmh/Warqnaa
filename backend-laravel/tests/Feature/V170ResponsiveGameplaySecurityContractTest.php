<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use App\Services\Leveling\XpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V170ResponsiveGameplaySecurityContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_progressive_xp_curve_matches_the_v170_contract(): void
    {
        $xp = app(XpService::class);

        $this->assertSame(80, $xp->requiredXp(1));
        $this->assertSame(98, $xp->requiredXp(2));
        $this->assertSame(119, $xp->requiredXp(3));
        $this->assertSame(145, $xp->requiredXp(4));
        $this->assertSame(177, $xp->requiredXp(5));
        $this->assertSame(215, $xp->requiredXp(6));
        $this->assertSame(262, $xp->requiredXp(7));
        $this->assertGreaterThan($xp->requiredXp(20), $xp->requiredXp(40));
    }

    public function test_public_profile_contains_progress_and_country_but_never_private_tokens(): void
    {
        $user = User::create([
            'username' => 'v170profile',
            'email' => 'v170profile@example.test',
            'password' => Hash::make('password123'),
        ]);
        Profile::create([
            'user_id' => $user->id,
            'display_name' => 'V170 Player',
            'country_code' => 'PS',
            'country_name' => 'فلسطين',
            'level' => 7,
            'xp' => 500,
            'round_points' => 120,
            'tournament_points' => 45,
            'club_points' => 30,
            'pasha_days' => 3,
        ]);

        $profile = $user->fresh('profile')->publicProfile();

        $this->assertSame('V170 Player', $profile['display_name']);
        $this->assertSame('PS', $profile['country_code']);
        $this->assertSame(7, $profile['level']);
        $this->assertSame(262, $profile['xp_next']);
        $this->assertArrayHasKey('flag', $profile);
        $this->assertArrayHasKey('round_points', $profile);
        $this->assertArrayNotHasKey('tokens', $profile);
        $this->assertArrayNotHasKey('wallet', $profile);
        $this->assertArrayNotHasKey('coins', $profile);
    }

    public function test_v170_authoritative_game_and_security_contracts_are_present(): void
    {
        $game = file_get_contents(app_path('Http/Controllers/MobileGameController.php'));
        $social = file_get_contents(app_path('Http/Controllers/MobileSocialController.php'));
        $tournament = file_get_contents(app_path('Http/Controllers/TournamentController.php'));
        $api = file_get_contents(base_path('../flutter_app/lib/services/api_client.dart'));
        $voice = file_get_contents(base_path('../flutter_app/lib/services/voice_room_service.dart'));
        $ui = file_get_contents(base_path('../flutter_app/lib/v170_global.dart'));

        $this->assertStringContainsString("'state_revision' => 'nullable|integer|min:0'", $game);
        $this->assertStringContainsString("'client_action_id' => 'nullable|string|min:8|max:120'", $game);
        $this->assertStringContainsString('Cache::add', $game);
        $this->assertStringContainsString('kicked_user_ids', $game);
        $this->assertStringContainsString('blockedWithParticipant', $game);
        $this->assertStringContainsString('unset($copy[\'hands\']', $game);
        $this->assertStringContainsString("'transfer_fee'", $social);
        $this->assertStringContainsString('inviteAllToRoom', $social);
        $this->assertStringContainsString('event_log_with_final_hands', $tournament);
        $this->assertStringContainsString('state_revision', $api);
        $this->assertStringContainsString('client_action_id', $api);
        $this->assertStringContainsString("const {'localhost', '127.0.0.1', '10.0.2.2'}", $voice);
        $this->assertStringContainsString('showPublicPlayerProfileV170', $ui);
        $this->assertStringContainsString('class OpenRoomCardV170', $ui);
        $this->assertStringContainsString('class GroupInnovationHubV170', $ui);
    }
}
