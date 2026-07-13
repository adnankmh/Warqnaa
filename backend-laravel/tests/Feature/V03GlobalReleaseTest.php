<?php

namespace Tests\Feature;

use App\Models\{Club, Profile, User};
use App\Services\Leveling\LevelUpRewardService;
use App\Services\WarqnaPro\ChallengeCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class V03GlobalReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_and_v03_schema_are_available(): void
    {
        $user = User::factory()->create();
        Profile::create(['user_id'=>$user->id, 'display_name'=>'V03 Player', 'country_code'=>'PS', 'country_name'=>'Palestine']);

        $this->assertNotNull($user->id);
        $this->assertTrue(Schema::hasColumn('clubs', 'visibility'));
        $this->assertTrue(Schema::hasColumn('room_players', 'voluntary_leave_count'));
        $this->assertTrue(Schema::hasTable('challenge_runs'));
        $this->assertTrue(Schema::hasTable('level_reward_claims'));
    }

    public function test_challenge_road_and_level_rewards_obey_product_limits(): void
    {
        $road = ChallengeCampaignService::rewardRoad(15);
        $this->assertCount(15, $road);
        $this->assertContains(15, ChallengeCampaignService::STAGE_OPTIONS);
        $this->assertSame(5, ChallengeCampaignService::STARTING_LIVES);
        foreach ($road as $reward) {
            if ($reward['type'] === 'tokens') $this->assertLessThanOrEqual(1000, $reward['amount']);
        }

        $service = app(LevelUpRewardService::class);
        foreach ([2,3,4,5,10,25,100] as $level) {
            $reward = $service->definition($level);
            if ($reward['type'] === 'tokens') $this->assertLessThanOrEqual(1000, $reward['amount']);
        }
    }

    public function test_balanced_deal_is_complete_unique_and_reasonably_even(): void
    {
        require_once app_path('Services/GameEngine/GlobalEngines/BalancedDealV03.php');
        $deck=[];
        foreach (['C','D','S','H'] as $suit) foreach (['2','3','4','5','6','7','8','9','10','J','Q','K','A'] as $rank) $deck[]=$rank.'_'.$suit;
        $deal=\BalancedDealV03::trick($deck,['p1','p2','p3','p4'],13);
        $all=array_merge(...array_values($deal['hands']));
        $this->assertCount(52,$all);
        $this->assertCount(52,array_unique($all));
        foreach ($deal['hands'] as $hand) $this->assertCount(13,$hand);
    }

    public function test_no_code_designer_is_reserved_for_adnan(): void
    {
        $controller=file_get_contents(app_path('Http/Controllers/MobileAdminController.php'));
        $this->assertStringContainsString("strtolower((string)\$user->username) === 'adnan'",$controller);
        $this->assertStringContainsString('public function designerIndex', $controller);
        $this->assertStringContainsString('$this->ownerGuard($request);', $controller);
    }
}
