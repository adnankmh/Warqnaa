<?php

namespace Tests\Feature;

use App\Models\{DailyPackClaim,InventoryItem,Profile,StoreItem,User,Wallet};
use App\Services\WarqnaPro\DailyPackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V176DailyPackInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function player(string $username): User
    {
        $user = User::create([
            'username'=>$username,
            'email'=>$username.'@example.test',
            'password'=>Hash::make('password123'),
        ]);
        Profile::create([
            'user_id'=>$user->id,
            'display_name'=>$username,
            'country_code'=>'PS',
            'country_name'=>'Palestine',
        ]);
        Wallet::create(['user_id'=>$user->id,'tokens'=>1500,'gems'=>0]);
        return $user->fresh(['profile','wallet']);
    }

    public function test_timed_pack_reward_is_added_to_store_inventory_with_expiry(): void
    {
        $user = $this->player('v176pack');
        $reward = app(DailyPackService::class)->open($user, DailyPackService::catalog()[0]);

        $this->assertSame('daily_pack_name_gold_24h_v176', $reward['store_item_key']);
        $this->assertNotNull($reward['expires_at']);
        $this->assertNotNull($reward['inventory_item']);
        $this->assertDatabaseHas('store_items', [
            'key'=>'daily_pack_name_gold_24h_v176',
            'category'=>'name_color',
            'price'=>0,
        ]);
        $item = StoreItem::where('key', 'daily_pack_name_gold_24h_v176')->firstOrFail();
        $inventory = InventoryItem::where('user_id', $user->id)->where('store_item_id', $item->id)->firstOrFail();
        $this->assertTrue($inventory->active);
        $this->assertNotNull($inventory->expires_at);
        $this->assertTrue($inventory->expires_at->isFuture());
        $this->assertSame('#facc15', $user->profile()->firstOrFail()->name_color);
        $this->assertSame(1, DailyPackClaim::where('user_id', $user->id)->count());
    }

    public function test_permanent_balance_reward_does_not_create_fake_inventory_item(): void
    {
        $user = $this->player('v176tokens');
        $tokenReward = collect(DailyPackService::catalog())->firstWhere('type', 'tokens');
        $reward = app(DailyPackService::class)->open($user, $tokenReward);

        $this->assertSame('tokens', $reward['type']);
        $this->assertNull($reward['expires_at']);
        $this->assertNull($reward['inventory_item']);
        $this->assertSame(0, InventoryItem::where('user_id', $user->id)->count());
        $this->assertGreaterThan(1500, (int)$user->wallet()->firstOrFail()->tokens);
    }

    public function test_flutter_v176_contract_contains_animation_inventory_and_analyzer_fixes(): void
    {
        $main = file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $release = file_get_contents(base_path('../flutter_app/lib/v176_release.dart'));
        $v175 = file_get_contents(base_path('../flutter_app/lib/v175_release.dart'));

        $this->assertStringNotContainsString('_openingRoomRouteV174', $main);
        $this->assertStringContainsString("part 'v176_release.dart';", $main);
        $this->assertStringContainsString('final navigationContext = warqnaNavigatorKey.currentContext;', $main);
        $this->assertStringContainsString('DailyPackOpeningDialogV176', $release);
        $this->assertStringContainsString('packInventoryExpiriesV176', $main);
        $this->assertStringContainsString("('inventory', 'مقتنياتي')", $main);
        $this->assertStringNotContainsString("'\${activated?'متابعة':'تفعيل'}'", $v175);
    }
}
