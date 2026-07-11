<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Notifications\FirebasePushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V166GlobalPolishContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_register_and_remove_a_push_device(): void
    {
        $user = User::create([
            'username' => 'v166push',
            'email' => 'v166push@example.test',
            'password' => Hash::make('password123'),
        ]);
        $token = $user->createToken('mobile')->plainTextToken;
        $deviceToken = str_repeat('v166-device-token-', 8);

        $this->withToken($token)->postJson('/api/mobile/v1/push/devices', [
            'token' => $deviceToken,
            'platform' => 'android',
            'app_version' => '1.66.0',
            'app_build' => 166,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('device.platform', 'android');

        $this->assertDatabaseHas('push_devices', [
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $deviceToken),
            'platform' => 'android',
            'app_build' => 166,
        ]);

        $this->withToken($token)->deleteJson('/api/mobile/v1/push/devices', [
            'token' => $deviceToken,
        ])->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseMissing('push_devices', [
            'token_hash' => hash('sha256', $deviceToken),
        ]);
    }


    public function test_push_dispatcher_is_fail_safe_without_server_credentials(): void
    {
        config([
            'push.enabled' => false,
            'push.project_id' => null,
            'push.service_account_b64' => null,
            'push.service_account_json' => null,
            'push.service_account_path' => null,
        ]);

        $user = User::create([
            'username' => 'v166nopush',
            'email' => 'v166nopush@example.test',
            'password' => Hash::make('password123'),
        ]);

        $service = app(FirebasePushService::class);
        $this->assertFalse($service->isConfigured());
        $this->assertSame(0, $service->sendToUser($user, 'Test', 'No credentials'));
    }

    public function test_v166_mobile_polish_contracts_are_present(): void
    {
        $main = file_get_contents(base_path('../flutter_app/lib/main.dart'));
        $polish = file_get_contents(base_path('../flutter_app/lib/v166_polish.dart'));
        $voice = file_get_contents(base_path('../flutter_app/lib/services/voice_room_service.dart'));
        $notifications = file_get_contents(base_path('../flutter_app/lib/services/app_notifications.dart'));
        $push = file_get_contents(app_path('Services/Notifications/FirebasePushService.php'));
        $social = file_get_contents(app_path('Http/Controllers/MobileSocialController.php'));
        $games = file_get_contents(app_path('Http/Controllers/MobileGameController.php'));

        $this->assertStringContainsString('onDoubleTap:', $main);
        $this->assertStringContainsString('onVerticalDragEnd:', $main);
        $this->assertStringContainsString('_trickSeatWidgets()', $main);
        $this->assertStringContainsString('ActiveGameBanner', $main);
        $this->assertStringContainsString('showV166EmojiPicker', $main);
        $this->assertStringContainsString('RoundRewardReport', $polish);
        $this->assertStringContainsString('Permission.microphone.request()', $voice);
        $this->assertStringContainsString('Helper.setSpeakerphoneOn', $voice);
        $this->assertStringContainsString('onTokenRefresh', $notifications);
        $this->assertStringContainsString('fcm.googleapis.com/v1/projects/', $push);
        $this->assertStringContainsString('sendToUser($user', $social);
        $this->assertStringContainsString("'type' => 'room_message'", $games);
        $legacyLabel = 'الأراضي'.' الفلسطينية';
        $this->assertStringNotContainsString($legacyLabel, $main.$polish);
    }
}
