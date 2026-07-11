<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Progression\ProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V161VoiceSocialProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_countries_and_flags_are_exposed(): void
    {
        $countries = config('countries');
        $this->assertGreaterThanOrEqual(240, count($countries));
        $this->assertSame('🇵🇸', $countries['PS']['flag']);
        $this->getJson('/api/mobile/v1/countries')->assertOk()->assertJsonPath('ok', true);
    }

    public function test_round_progress_is_idempotent_and_pasha_doubles_once(): void
    {
        $user = User::create(['username'=>'progression','email'=>'progression@example.test','password'=>Hash::make('password')]);
        $user->profile()->create(['display_name'=>'Progression','country_code'=>'PS','country_name'=>'فلسطين','level'=>1,'xp'=>0,'pasha_days'=>3650,'xp_boost_multiplier'=>1]);
        $service = app(ProgressionService::class);

        $first = $service->award($user, 'test:round:1', ['event_type'=>'round_complete','mode'=>'normal','won'=>true]);
        $again = $service->award($user, 'test:round:1', ['event_type'=>'round_complete','mode'=>'normal','won'=>true]);

        $this->assertFalse($first['duplicate']);
        $this->assertTrue($again['duplicate']);
        $this->assertSame(2.0, $first['multiplier']);
        $this->assertSame(120, $first['xp']);
        $this->assertSame(60, $first['round_points']);
        $this->assertSame(120, (int)$user->profile()->firstOrFail()->xp);
    }

    public function test_social_voice_club_and_designer_contracts_exist(): void
    {
        $this->assertStringContainsString("auth/social/*/callback", file_get_contents(base_path('bootstrap/app.php')));
        $this->assertStringContainsString("create_announcements", file_get_contents(app_path('Http/Controllers/ClubController.php')));
        $this->assertStringContainsString("table_image", file_get_contents(app_path('Http/Controllers/AdminController.php')));
        $this->assertStringContainsString("card_back_image", file_get_contents(app_path('Http/Controllers/AdminController.php')));
        $this->assertStringContainsString("turn_configured", file_get_contents(app_path('Http/Controllers/MobileVoiceController.php')));
    }
}
