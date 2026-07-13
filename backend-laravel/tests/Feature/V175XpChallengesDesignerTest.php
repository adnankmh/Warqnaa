<?php

namespace Tests\Feature;

use App\Models\{ChallengeDefinition,Profile,User};
use App\Services\Leveling\XpService;
use App\Services\WarqnaPro\ChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V175XpChallengesDesignerTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_excel_xp_values_are_exact(): void
    {
        $service = app(XpService::class);
        $this->assertSame(80, $service->requiredXp(1));
        $this->assertSame(98, $service->requiredXp(2));
        $this->assertSame(119, $service->requiredXp(3));
        $this->assertSame(145, $service->requiredXp(4));
        $this->assertSame(177, $service->requiredXp(5));
        $this->assertSame(215, $service->requiredXp(6));
        $this->assertSame(262, $service->requiredXp(7));
        $this->assertSame(319, $service->requiredXp(8));
        $this->assertSame(387, $service->requiredXp(9));
        $this->assertSame(470, $service->requiredXp(10));
        $this->assertSame(570, $service->requiredXp(11));
        $this->assertSame(690, $service->requiredXp(12));
        $this->assertSame(834, $service->requiredXp(13));
        $this->assertSame(1007, $service->requiredXp(14));
        $this->assertSame(1213, $service->requiredXp(15));
        $this->assertSame(1460, $service->requiredXp(16));
        $this->assertSame(1755, $service->requiredXp(17));
        $this->assertSame(2105, $service->requiredXp(18));
        $this->assertSame(2519, $service->requiredXp(19));
        $this->assertSame(3010, $service->requiredXp(20));
        $this->assertSame(3588, $service->requiredXp(21));
        $this->assertSame(4268, $service->requiredXp(22));
        $this->assertSame(5065, $service->requiredXp(23));
        $this->assertSame(5996, $service->requiredXp(24));
        $this->assertSame(7081, $service->requiredXp(25));
        $this->assertSame(8341, $service->requiredXp(26));
        $this->assertSame(9798, $service->requiredXp(27));
        $this->assertSame(11477, $service->requiredXp(28));
        $this->assertSame(13405, $service->requiredXp(29));
        $this->assertSame(15610, $service->requiredXp(30));
        $this->assertSame(18121, $service->requiredXp(31));
        $this->assertSame(20969, $service->requiredXp(32));
        $this->assertSame(24185, $service->requiredXp(33));
        $this->assertSame(27799, $service->requiredXp(34));
        $this->assertSame(31842, $service->requiredXp(35));
        $this->assertSame(36343, $service->requiredXp(36));
        $this->assertSame(41326, $service->requiredXp(37));
        $this->assertSame(46814, $service->requiredXp(38));
        $this->assertSame(52825, $service->requiredXp(39));
        $this->assertSame(59371, $service->requiredXp(40));
        $this->assertSame(66455, $service->requiredXp(41));
        $this->assertSame(74074, $service->requiredXp(42));
        $this->assertSame(82212, $service->requiredXp(43));
        $this->assertSame(90846, $service->requiredXp(44));
        $this->assertSame(99936, $service->requiredXp(45));
        $this->assertSame(109434, $service->requiredXp(46));
        $this->assertSame(119274, $service->requiredXp(47));
        $this->assertSame(129379, $service->requiredXp(48));
        $this->assertSame(139656, $service->requiredXp(49));
        $this->assertSame(150000, $service->requiredXp(50));
        $this->assertSame(160627, $service->requiredXp(51));
        $this->assertSame(171847, $service->requiredXp(52));
        $this->assertSame(183691, $service->requiredXp(53));
        $this->assertSame(196194, $service->requiredXp(54));
        $this->assertSame(209392, $service->requiredXp(55));
        $this->assertSame(223324, $service->requiredXp(56));
        $this->assertSame(238035, $service->requiredXp(57));
        $this->assertSame(253573, $service->requiredXp(58));
        $this->assertSame(269989, $service->requiredXp(59));
        $this->assertSame(287341, $service->requiredXp(60));
        $this->assertSame(305693, $service->requiredXp(61));
        $this->assertSame(325113, $service->requiredXp(62));
        $this->assertSame(345677, $service->requiredXp(63));
        $this->assertSame(367470, $service->requiredXp(64));
        $this->assertSame(390584, $service->requiredXp(65));
        $this->assertSame(415120, $service->requiredXp(66));
        $this->assertSame(441192, $service->requiredXp(67));
        $this->assertSame(468922, $service->requiredXp(68));
        $this->assertSame(498450, $service->requiredXp(69));
        $this->assertSame(529926, $service->requiredXp(70));
        $this->assertSame(563519, $service->requiredXp(71));
        $this->assertSame(599416, $service->requiredXp(72));
        $this->assertSame(637823, $service->requiredXp(73));
        $this->assertSame(678971, $service->requiredXp(74));
        $this->assertSame(723116, $service->requiredXp(75));
        $this->assertSame(770543, $service->requiredXp(76));
        $this->assertSame(821569, $service->requiredXp(77));
        $this->assertSame(876548, $service->requiredXp(78));
        $this->assertSame(935877, $service->requiredXp(79));
        $this->assertSame(1000000, $service->requiredXp(80));
        $this->assertSame(1068951, $service->requiredXp(81));
        $this->assertSame(1142786, $service->requiredXp(82));
        $this->assertSame(1222076, $service->requiredXp(83));
        $this->assertSame(1307479, $service->requiredXp(84));
        $this->assertSame(1399753, $service->requiredXp(85));
        $this->assertSame(1499772, $service->requiredXp(86));
        $this->assertSame(1608544, $service->requiredXp(87));
        $this->assertSame(1727236, $service->requiredXp(88));
        $this->assertSame(1857199, $service->requiredXp(89));
        $this->assertSame(2000000, $service->requiredXp(90));
        $this->assertSame(2175021, $service->requiredXp(91));
        $this->assertSame(2405469, $service->requiredXp(92));
        $this->assertSame(2700731, $service->requiredXp(93));
        $this->assertSame(3072915, $service->requiredXp(94));
        $this->assertSame(3537123, $service->requiredXp(95));
        $this->assertSame(4111710, $service->requiredXp(96));
        $this->assertSame(4818481, $service->requiredXp(97));
        $this->assertSame(5682712, $service->requiredXp(98));
        $this->assertSame(6732888, $service->requiredXp(99));
        $this->assertSame(8000000, $service->requiredXp(100));
    }

    public function test_challenge_can_activate_progress_and_claim_once(): void
    {
        $user=User::create(['username'=>'v175challenge','email'=>'v175@example.test','password'=>Hash::make('password123')]);
        Profile::create(['user_id'=>$user->id,'display_name'=>'V175','country_code'=>'PS','country_name'=>'Palestine']);
        $definition=ChallengeDefinition::firstOrCreate(['key'=>'v175_test'],['name'=>['ar'=>'اختبار'],'description'=>['ar'=>'اختبار'],'cadence'=>'daily','metric'=>'wins','target'=>1,'reward_tokens'=>25,'reward_xp'=>10,'settings'=>['icon'=>'✅'],'active'=>true,'sort_order'=>1]);
        $service=app(ChallengeService::class);
        $activated=$service->activate($user,$definition->key);
        $this->assertTrue($activated['activated']);
        $service->record($user,'wins',1);
        $claimed=$service->claim($user,$definition->key);
        $this->assertTrue($claimed['claimed']);
        $this->expectException(\RuntimeException::class);
        $service->claim($user,$definition->key);
    }

    public function test_v175_ui_contract_hides_pasha_colors_and_keeps_full_pasha(): void
    {
        $main=file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $v170=file_get_contents(base_path('../flutter_app/lib/v170_global.dart'));
        $v173=file_get_contents(base_path('../flutter_app/lib/v173_global.dart'));
        $this->assertStringNotContainsString("('pasha_style', 'ألوان الطربوش')",$main);
        $this->assertStringContainsString("assets/images/pasha.png",$v170);
        $this->assertStringContainsString("assets/images/pasha.png",$v173);
        $this->assertStringContainsString('level_xp',$v173);
        $this->assertStringContainsString('feature_flag',$v173);
    }
}
