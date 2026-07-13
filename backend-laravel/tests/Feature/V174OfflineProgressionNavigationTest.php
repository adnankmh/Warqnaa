<?php

namespace Tests\Feature;

use App\Models\{DailyPackClaim,Profile,User,Wallet};
use App\Services\Leveling\XpService;
use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V174OfflineProgressionNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function baseXp(int $level): int
    {
        if ($level <= 7) return [1=>100,2=>220,3=>360,4=>500,5=>650,6=>800,7=>1000][$level];
        $high = $level - 7;
        return 1000 + ($high * 220) + ($high * $high * 35);
    }

    public function test_v174_progressive_xp_curve_matches_all_requested_ranges(): void
    {
        $xp = app(\App\Services\Leveling\XpService::class);
        $this->assertSame(80, $xp->requiredXp(1));
        $this->assertSame(59371, $xp->requiredXp(40));
        $this->assertSame(150000, $xp->requiredXp(50));
        $this->assertSame(1000000, $xp->requiredXp(80));
        $this->assertSame(8000000, $xp->requiredXp(100));
    }

    public function test_v174_catalog_remains_additive_and_unique(): void
    {
        $tables = app(StoreCatalogService::class)->tableSkins();
        $this->assertCount(140, $tables);
        $this->assertCount(140, array_unique(array_column($tables, 'key')));
    }

    public function test_daily_pack_claim_date_is_stored_as_plain_date(): void
    {
        $user = User::create(['username'=>'v174date','email'=>'v174date@example.test','password'=>Hash::make('password123')]);
        Profile::create(['user_id'=>$user->id,'display_name'=>'V174 Date','country_code'=>'PS','country_name'=>'Palestine']);
        Wallet::create(['user_id'=>$user->id,'tokens'=>0,'gems'=>0]);
        $claim = DailyPackClaim::create([
            'user_id'=>$user->id,'claim_date'=>'2026-07-13','reward_type'=>'tokens','reward_key'=>'250','duration_hours'=>0,'payload'=>[],
        ]);
        $this->assertDatabaseHas('daily_pack_claims', ['id'=>$claim->id,'claim_date'=>'2026-07-13']);
        $this->assertSame('2026-07-13', $claim->fresh()->claim_date->toDateString());
    }
}
