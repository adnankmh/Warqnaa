<?php

namespace Tests\Feature;

use App\Models\{Profile,User,Wallet};
use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V021PlayerProfileAdminStoreCardBackTest extends TestCase
{
    use RefreshDatabase;

    public function test_adnan_is_promoted_to_primary_admin_on_mobile_login(): void
    {
        $user = User::factory()->create([
            'username' => 'Adnan',
            'password' => Hash::make('Adnan123'),
            'is_admin' => false,
        ]);

        Profile::create(['user_id' => $user->id, 'display_name' => 'Adnan', 'country_code' => 'PS', 'country_name' => 'Palestine']);
        Wallet::create(['user_id' => $user->id, 'tokens' => 1000]);

        $response = $this->postJson('/api/mobile/v1/login', [
            'login' => 'Adnan',
            'password' => 'Adnan123',
        ]);

        $response->assertOk()->assertJsonPath('user.is_admin', true);
        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_v021_adds_twelve_table_inspired_card_backs_without_changing_legacy_count(): void
    {
        $catalog = app(StoreCatalogService::class);
        $items = $catalog->v021TableCardBacks();

        $this->assertCount(40, $catalog->cardBacks());
        $this->assertCount(12, $items);
        $this->assertCount(12, array_unique(array_column($items, 'key')));
        $this->assertContains('cardback_v021_phoenix', array_column($items, 'key'));
        $this->assertSame('v021_table_cardbacks', $items[0]['payload']['collection']);
    }

    public function test_flutter_patch_contains_global_profile_taps_and_non_scrolling_hands(): void
    {
        $main = file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $patch = file_get_contents(base_path('../flutter_app/lib/v021_patch.dart'));
        $adminStore = file_get_contents(base_path('../flutter_app/lib/premium_v151.dart'));

        $this->assertStringContainsString("part 'v021_patch.dart';", $main);
        $this->assertStringContainsString('openPlayerProfileV021', $patch);
        $this->assertStringContainsString('visibleCardWidthV021', $patch);
        $this->assertStringContainsString('v021_table_cardbacks', $main);
        $this->assertStringContainsString("('visual', 'المظهر واللعب'", $adminStore);
        $this->assertStringContainsString('mainAxisAlignment: MainAxisAlignment.center', $main);
    }
}
