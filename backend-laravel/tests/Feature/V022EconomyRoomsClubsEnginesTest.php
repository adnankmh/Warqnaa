<?php

namespace Tests\Feature;

use App\Models\{AdminDelegation,Club,ClubMember,Profile,StoreItem,User,Wallet};
use App\Services\GameEngine\{EngineRegistry,GameFactory};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class V022EconomyRoomsClubsEnginesTest extends TestCase
{
    use RefreshDatabase;

    private function player(string $username, int $tokens = 100000): User
    {
        $user = User::create([
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
        ]);
        Profile::create([
            'user_id' => $user->id,
            'display_name' => ucfirst($username),
            'country_code' => 'PS',
            'country_name' => 'Palestine',
            'pasha_style' => 'red',
        ]);
        Wallet::create(['user_id' => $user->id, 'tokens' => $tokens, 'gems' => 0]);
        return $user->fresh(['profile','wallet']);
    }

    public function test_purchase_uses_the_authoritative_wallet_and_returns_the_updated_balance(): void
    {
        $user = $this->player('v022buyer', 50000);
        $item = StoreItem::create([
            'key' => 'v022_purchase_contract',
            'name' => ['ar'=>'عنصر اختبار','en'=>'Test Item'],
            'category' => 'badge',
            'price' => 12000,
            'payload' => ['badge'=>'V022'],
            'active' => true,
        ]);

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/mobile/v1/store/purchase', [
            'key' => $item->key,
            'confirmed' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('wallet.tokens', '38000');
        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $user->id,
            'store_item_id' => $item->id,
            'active' => 1,
        ]);
    }

    public function test_adnan_and_delegated_admin_permissions_are_server_authoritative(): void
    {
        $adnan = $this->player('Adnan');
        $adnan->update(['is_admin'=>true]);
        $delegate = $this->player('v022delegate');
        AdminDelegation::create([
            'user_id'=>$delegate->id,
            'granted_by'=>$adnan->id,
            'permissions'=>['store.manage','designer.manage'],
            'active'=>true,
        ]);

        $this->assertSame(['*'], $adnan->fresh(['profile','adminDelegation'])->publicProfile()['admin_permissions']);
        $this->assertContains('store.manage', $delegate->fresh(['profile','adminDelegation'])->publicProfile()['admin_permissions']);
    }

    public function test_profile_exposes_club_identity_and_multiple_member_permissions(): void
    {
        $owner = $this->player('v022owner');
        $member = $this->player('v022member');
        $club = Club::create([
            'owner_id'=>$owner->id,
            'name'=>'Warqna Elite',
            'logo'=>'🛡️',
            'level'=>7,
            'weekly_points'=>0,
            'total_points'=>0,
            'treasury'=>0,
            'capacity'=>50,
            'league_tier'=>'gold',
            'visibility'=>'public',
        ]);
        ClubMember::create(['club_id'=>$club->id,'user_id'=>$owner->id,'role'=>'owner','permissions'=>['all'=>true],'weekly_points'=>0]);
        ClubMember::create([
            'club_id'=>$club->id,
            'user_id'=>$member->id,
            'role'=>'moderator',
            'permissions'=>['manage_chat'=>true,'create_tournaments'=>true,'accept_members'=>true],
            'weekly_points'=>0,
        ]);

        $profile = $member->fresh(['profile','clubMembership.club'])->publicProfile();
        $this->assertSame('Warqna Elite', $profile['club']['name']);
        $this->assertSame('🛡️', $profile['club']['logo']);
        $this->assertSame('moderator', $profile['club']['role']);
        $this->assertCount(3, $member->clubMembership()->firstOrFail()->permissions);
    }

    public function test_public_room_and_admin_contracts_are_present(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/MobileGameController.php'));
        $admin = file_get_contents(app_path('Http/Controllers/MobileAdminController.php'));
        $routes = file_get_contents(base_path('routes/api.php'));

        $this->assertStringContainsString("where('visibility', '!=', 'private')", $controller);
        $this->assertStringContainsString("'avatars'", $controller);
        $this->assertStringContainsString("'empty_seats'", $controller);
        $this->assertStringContainsString('updateDelegation', $admin);
        $this->assertStringContainsString("/clubs/{club}/members/{member}", $routes);
        $this->assertStringContainsString("/admin/delegations/{user}", $routes);
    }

    public function test_registry_and_rummy_adapter_support_real_51_opening_and_lay_offs(): void
    {
        $handMeta = EngineRegistry::get('hand');
        $banakilMeta = EngineRegistry::get('banakil');
        $this->assertSame(5, $handMeta['max']);
        $this->assertContains('meld_batch', $handMeta['actions']);
        $this->assertContains('lay_off', $handMeta['actions']);
        $this->assertSame(18, $banakilMeta['hand']);

        $engine = GameFactory::make('hand');
        $state = $engine->initialState(['user:1','user:2'], ['target'=>101]);
        $this->assertCount(2, $state['players']);

        $player = (string)$state['turn'];
        $shortHand = ['10_H','10_D','10_S','9_C','10_C','J_C','3_D'];
        $longHand = ['10_hearts','10_diamonds','10_spades','9_clubs','10_clubs','J_clubs','3_diamonds'];
        $state['_global_engine']['phase'] = 'discard';
        $state['_global_engine']['currentIndex'] = array_search($player, array_column($state['_global_engine']['players'], 'id'), true);
        $state['_global_engine']['hands'][$player] = $shortHand;
        $state['_global_engine']['opened'][$player] = false;
        $state['engine_phase'] = 'discard';
        $state['phase'] = 'playing';
        $state['hands'][$player] = $longHand;

        $state = $engine->apply($state, $player, 'meld_batch', [
            'groups'=>[
                ['10_hearts','10_diamonds','10_spades'],
                ['9_clubs','10_clubs','J_clubs'],
            ],
        ]);

        $this->assertArrayNotHasKey('last_error', $state, $state['last_error_message'] ?? 'meld batch failed');
        $this->assertTrue((bool)$state['_global_engine']['opened'][$player]);
        $this->assertSame(['3_D'], array_values($state['_global_engine']['hands'][$player]));
        $this->assertCount(2, $state['_global_engine']['melds'][$player]);
    }

    public function test_all_curated_game_engines_still_create_valid_initial_state(): void
    {
        foreach (array_keys(EngineRegistry::all()) as $key) {
            $engine = GameFactory::make($key);
            $players = ['user:1','user:2','user:3','user:4','user:5'];
            $state = $engine->initialState($players, ['target'=>101]);
            $this->assertIsArray($state, $key);
            $this->assertArrayHasKey('phase', $state, $key);
            $this->assertArrayHasKey('turn', $state, $key);
            $this->assertNotEmpty($state['players'], $key);
        }
    }
}
