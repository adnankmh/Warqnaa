<?php

namespace Tests\Feature;

use App\Models\{LuckyWheelSpin, PrizeBox, Profile, User, Wallet};
use App\Services\WarqnaPro\{LuckyWheelService, PrizeBoxService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class V182LuckyWheelRewardsTest extends TestCase
{
    use RefreshDatabase;

    private function player(string $username, int $tokens = 0, bool $admin = false): User
    {
        $user = User::create([
            'username'=>$username,
            'email'=>strtolower($username).'@v182.test',
            'password'=>Hash::make('password123'),
            'is_admin'=>$admin,
        ]);
        Profile::create([
            'user_id'=>$user->id,
            'display_name'=>$username,
            'country_code'=>'PS',
            'country_name'=>'Palestine',
            'level'=>$admin ? 99 : 10,
            'pasha_style'=>'red',
        ]);
        Wallet::create(['user_id'=>$user->id,'tokens'=>$tokens,'gems'=>0]);
        return $user->fresh(['profile','wallet']);
    }

    public function test_wheel_has_ten_server_authoritative_segments_and_one_free_spin_daily(): void
    {
        $this->player('Adnan', 0, true);
        $user = $this->player('WheelPlayer', 1000);
        $service = app(LuckyWheelService::class);

        $center = $service->center($user);
        $this->assertCount(10, $center['segments']);
        $this->assertTrue($center['free_available']);
        $this->assertSame(100, $center['token_cost']);

        $result = $service->spin($user, 'free');
        $this->assertContains($result['segment_index'], range(0, 9));
        $this->assertNotEmpty($result['reward']['type']);
        $this->assertFalse($result['center']['free_available']);
        $this->assertSame(1, LuckyWheelSpin::where('user_id',$user->id)->count());

        $this->expectException(RuntimeException::class);
        $service->spin($user, 'free');
    }

    public function test_paid_wheel_spin_moves_cost_to_primary_admin_and_applies_reward(): void
    {
        $admin = $this->player('Adnan', 20, true);
        $user = $this->player('PaidSpinner', 1000);
        $service = app(LuckyWheelService::class);

        $result = $service->spin($user, 'tokens');

        $this->assertSame(100, (int)$result['center']['token_cost']);
        $this->assertSame(1, $result['center']['token_spins_today']);
        $reward = $result['reward'];
        $rewardTokens = is_array($reward) && (($reward['type'] ?? '') === 'tokens') ? (int) ($reward['value'] ?? 0) : 0;
        $this->assertSame(1000 - 100 + $rewardTokens, (int) $user->wallet()->first()->tokens);
        $this->assertSame(120, (int)$admin->wallet()->first()->tokens);
        $this->assertDatabaseHas('lucky_wheel_spins', [
            'user_id'=>$user->id,
            'source'=>'tokens',
            'token_cost'=>100,
        ]);
    }

    public function test_completed_game_box_tier_depends_on_mode_and_result(): void
    {
        $user = $this->player('OutcomeBoxes');
        $service = app(PrizeBoxService::class);

        $simple = $service->awardForCompletedGame($user,'normal-loss','hand','normal',false);
        $strong = $service->awardForCompletedGame($user,'normal-win','tarneeb','normal',true);
        $epic = $service->awardForCompletedGame($user,'competition-loss','trix','tournament',false);
        $legendary = $service->awardForCompletedGame($user,'competition-win','banakil','tournament',true);

        $this->assertContains($simple->box_key, ['crimson_lion','obsidian']);
        $this->assertContains($strong->box_key, ['emerald_eagle','bronze_dragon']);
        $this->assertSame('royal_amethyst', $epic->box_key);
        $this->assertSame('diamond_phoenix', $legendary->box_key);
        $this->assertSame('simple', $simple->payload['tier']);
        $this->assertSame('strong', $strong->payload['tier']);
        $this->assertSame('epic', $epic->payload['tier']);
        $this->assertSame('legendary', $legendary->payload['tier']);
        $this->assertSame(4, PrizeBox::where('user_id',$user->id)->count());
    }
}
