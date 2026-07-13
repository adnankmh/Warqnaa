<?php

namespace Tests\Feature;

use App\Models\{CompetitionTicket, InventoryItem, PrizeBox, Profile, User, Wallet};
use App\Services\WarqnaPro\PrizeBoxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class V02DailyPrizeBoxesTest extends TestCase
{
    use RefreshDatabase;

    private function player(string $username = 'v02player'): User
    {
        $user = User::create([
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
        ]);
        Profile::create([
            'user_id' => $user->id,
            'display_name' => $username,
            'country_code' => 'PS',
            'country_name' => 'Palestine',
            'pasha_style' => 'red',
        ]);
        Wallet::create(['user_id' => $user->id, 'tokens' => 0, 'gems' => 0]);
        return $user->fresh(['profile', 'wallet']);
    }

    public function test_one_varied_box_is_awarded_per_win_up_to_four_per_day(): void
    {
        $user = $this->player('v02limit');
        $service = app(PrizeBoxService::class);
        $awarded = [];

        for ($i = 1; $i <= 4; $i++) {
            $box = $service->awardForWin($user, 'match-win-'.$i, $i % 2 ? 'tarneeb' : 'basra');
            $this->assertNotNull($box);
            $awarded[] = $box->box_key;
        }

        $this->assertNull($service->awardForWin($user, 'match-win-5', 'tarneeb'));
        $this->assertSame(4, PrizeBox::where('user_id', $user->id)->count());
        $this->assertGreaterThan(1, count(array_unique($awarded)));

        $duplicate = $service->awardForWin($user, 'match-win-1', 'tarneeb');
        $this->assertNotNull($duplicate);
        $this->assertSame(4, PrizeBox::where('user_id', $user->id)->count());
    }

    public function test_opening_boxes_applies_requested_rewards_and_expiry(): void
    {
        $user = $this->player('v02rewards');
        $service = app(PrizeBoxService::class);

        $tokenBox = $service->awardForWin($user, 'reward-token', 'tarneeb');
        $tokenResult = $service->open($user, $tokenBox, [
            'type' => 'tokens', 'value' => '350', 'duration_hours' => 0,
            'rarity' => 'common', 'icon' => '🪙', 'label_ar' => '350 توكن مجاني',
        ]);
        $this->assertSame('350', (string) $tokenResult['wallet']['tokens']);
        $this->assertSame('tokens', $tokenResult['reward']['type']);

        $ticketBox = $service->awardForWin($user, 'reward-ticket', 'basra');
        $ticketResult = $service->open($user, $ticketBox, [
            'type' => 'ticket', 'value' => '200', 'duration_hours' => 0,
            'rarity' => 'epic', 'icon' => '🎟️', 'label_ar' => 'تذكرة مسابقة 200',
        ]);
        $this->assertSame(1, $ticketResult['tickets'][200]);
        $this->assertDatabaseHas('competition_tickets', [
            'user_id' => $user->id, 'denomination' => 200, 'quantity' => 1,
        ]);

        $pashaBox = $service->awardForWin($user, 'reward-pasha', 'tarneeb');
        $service->open($user, $pashaBox, [
            'type' => 'pasha_day', 'value' => '1', 'duration_hours' => 24,
            'rarity' => 'legendary', 'icon' => '👑', 'label_ar' => 'يوم باشا',
            'store_item_key' => 'daily_prize_pasha_day_v02',
        ]);
        $this->assertSame('red', $user->profile()->first()->pasha_style);
        $this->assertSame(1, (int) $user->profile()->first()->pasha_days);
        $this->assertDatabaseHas('inventory_items', ['user_id' => $user->id, 'active' => 1]);

        $coverBox = $service->awardForWin($user, 'reward-cover', 'basra');
        $coverResult = $service->open($user, $coverBox, [
            'type' => 'profile_cover', 'value' => 'cover_v02_royal', 'duration_hours' => 72,
            'rarity' => 'epic', 'icon' => '🖼️', 'label_ar' => 'غلاف شخصي لمدة 3 أيام',
            'store_item_key' => 'daily_prize_cover_v02',
        ]);
        $this->assertSame('cover_v02_royal', $user->profile()->first()->active_profile_cover);
        $this->assertNotNull($coverResult['reward']['expires_at']);
        $this->assertSame(2, InventoryItem::where('user_id', $user->id)->where('active', true)->count());

        $this->expectException(RuntimeException::class);
        $service->open($user, $coverBox);
    }
}
