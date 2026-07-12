<?php

namespace Tests\Feature;

use App\Models\{AdminDesignerEntity,CompetitionTicket,DailyPackClaim,Game,Profile,TournamentEntry,User,Wallet};
use App\Services\WarqnaPro\{CompetitionService,DailyPackService,StoreCatalogService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class V173OnlineEngagementTest extends TestCase
{
    use RefreshDatabase;

    private function player(string $username = 'v173player'): User
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
        Wallet::create(['user_id' => $user->id, 'tokens' => 100000, 'gems' => 0]);
        return $user->fresh(['profile','wallet']);
    }

    public function test_v173_catalog_preserves_legacy_tables_and_adds_fifty_new_tables(): void
    {
        $catalog = app(StoreCatalogService::class);
        $tables = $catalog->tableSkins();
        $keys = array_column($tables, 'key');

        $this->assertCount(140, $tables);
        $this->assertCount(140, array_unique($keys));
        $this->assertContains('table_premium_01', $keys);
        $this->assertContains('table_reference_40', $keys);
        $this->assertContains('table_v173_royal_30', $keys);
        $this->assertContains('table_v173_showcase_20', $keys);

        $items = $catalog->v173Items();
        $this->assertCount(78, $items);
        $this->assertCount(14, array_filter($items, fn(array $item) => $item['category'] === 'pasha_style'));
        $this->assertCount(50, array_filter($items, fn(array $item) => $item['category'] === 'table'));
        $this->assertCount(14, array_filter($items, fn(array $item) => $item['category'] === 'competition_ticket'));
        foreach (array_filter($items, fn(array $item) => $item['category'] === 'competition_ticket') as $ticket) {
            $this->assertSame((int) round(((int) data_get($ticket, 'payload.denomination')) * .9), (int) $ticket['price']);
        }
    }

    public function test_daily_pack_is_server_authoritative_and_available_once_per_day(): void
    {
        $user = $this->player('v173pack');
        app(StoreCatalogService::class)->sync();
        $service = app(DailyPackService::class);

        $reward = $service->open($user);
        $this->assertNotEmpty($reward['type']);
        $this->assertDatabaseHas('daily_pack_claims', [
            'user_id' => $user->id,
            'claim_date' => now()->toDateString(),
        ]);
        $this->assertSame(1, DailyPackClaim::where('user_id', $user->id)->count());

        $this->expectException(RuntimeException::class);
        $service->open($user);
    }

    public function test_competition_uses_the_lowest_sufficient_ticket_before_tokens(): void
    {
        $user = $this->player('v173ticket');
        Game::firstOrCreate([
            'key' => 'basra',
        ], [
            'name' => ['ar' => 'بصرة', 'en' => 'Basra'],
            'min_players' => 2,
            'max_players' => 4,
            'partnership' => false,
            'rules' => [],
            'active' => true,
        ]);
        CompetitionTicket::create(['user_id' => $user->id, 'denomination' => 500, 'quantity' => 1, 'total_used' => 0]);
        CompetitionTicket::create(['user_id' => $user->id, 'denomination' => 2000, 'quantity' => 1, 'total_used' => 0]);

        $result = app(CompetitionService::class)->join($user, 'quick', 500);

        $this->assertSame('ticket', $result['entry_mode']);
        $this->assertSame(500, $result['ticket_denomination']);
        $this->assertDatabaseHas('competition_tickets', ['user_id' => $user->id, 'denomination' => 500, 'quantity' => 0, 'total_used' => 1]);
        $this->assertDatabaseHas('competition_tickets', ['user_id' => $user->id, 'denomination' => 2000, 'quantity' => 1]);
        $this->assertSame(1, TournamentEntry::where('user_id', $user->id)->count());
        $this->assertSame('100000', (string) $user->wallet()->first()->tokens);
    }

    public function test_universal_designer_entity_is_revisioned_and_cast_to_array(): void
    {
        $admin = $this->player('v173admin');
        $admin->update(['is_admin' => true]);
        $entity = AdminDesignerEntity::create([
            'entity_type' => 'table',
            'key' => 'custom_table_01',
            'locale' => 'all',
            'payload' => ['name' => ['ar' => 'طاولة خاصة'], 'enabled' => true],
            'sort_order' => 1,
            'active' => true,
            'revision' => 1,
            'updated_by' => $admin->id,
        ]);

        $this->assertIsArray($entity->fresh()->payload);
        $this->assertTrue($entity->fresh()->active);
        $this->assertSame('طاولة خاصة', data_get($entity->fresh()->payload, 'name.ar'));
    }
}
