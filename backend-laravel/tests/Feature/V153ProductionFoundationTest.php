<?php
namespace Tests\Feature;

use App\Models\{User,Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V153ProductionFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_config_and_legal_pages_are_available(): void
    {
        $this->getJson('/api/mobile/v1/app-config?platform=web')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['config' => ['version','build','features','legal','voice','limits']]);
        foreach (['privacy','terms','community-guidelines','account-deletion','competition-rules','support'] as $page) {
            $this->get('/legal/'.$page)->assertOk();
        }
    }

    public function test_authenticated_user_can_export_data_and_manage_sessions(): void
    {
        $user = User::create(['username'=>'v153user','email'=>'v153@example.test','password'=>Hash::make('password123')]);
        Wallet::create(['user_id'=>$user->id,'tokens'=>500,'gems'=>0]);
        $token = $user->createToken('mobile')->plainTextToken;
        $this->withToken($token)->getJson('/api/mobile/v1/account/export')
            ->assertOk()->assertJsonPath('export.account.username','v153user');
        $this->withToken($token)->getJson('/api/mobile/v1/account/sessions')
            ->assertOk()->assertJsonCount(1,'sessions');
    }

    public function test_user_can_submit_a_safety_report(): void
    {
        $reporter = User::create(['username'=>'reporter','email'=>'reporter@example.test','password'=>Hash::make('password123')]);
        $target = User::create(['username'=>'target','email'=>'target@example.test','password'=>Hash::make('password123')]);
        Wallet::create(['user_id'=>$reporter->id,'tokens'=>0,'gems'=>0]);
        Wallet::create(['user_id'=>$target->id,'tokens'=>0,'gems'=>0]);
        $token = $reporter->createToken('mobile')->plainTextToken;
        $this->withToken($token)->postJson('/api/mobile/v1/safety/reports', [
            'reported_user_id'=>$target->id,
            'category'=>'harassment',
            'details'=>'test report',
        ])->assertCreated()->assertJsonPath('ok',true);
        $this->assertDatabaseHas('user_reports',['reporter_id'=>$reporter->id,'reported_user_id'=>$target->id,'status'=>'open']);
    }

    public function test_security_headers_and_request_id_are_added(): void
    {
        $response = $this->getJson('/api/mobile/v1/health')->assertOk();
        $response->assertHeader('X-Content-Type-Options','nosniff');
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
}
