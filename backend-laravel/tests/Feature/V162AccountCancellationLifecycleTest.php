<?php

namespace Tests\Feature;

use App\Models\{AccountDeletionRequest, User, Wallet};
use App\Services\Account\AccountCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V162AccountCancellationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_account_cancellation_starts_a_30_day_grace_period_and_revokes_sessions(): void
    {
        Carbon::setTestNow('2026-07-11 12:00:00');
        $user = $this->makeUser('cancel-me');
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/mobile/v1/account/deletion-request', [
                'password' => 'password123',
                'reason' => 'Taking a break',
            ])
            ->assertOk()
            ->assertJsonPath('account_cancelled', true)
            ->assertJsonPath('grace_days', 30)
            ->assertJsonStructure(['scheduled_for']);

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $request = AccountDeletionRequest::where('user_id', $user->id)->where('status', 'pending')->firstOrFail();
        $this->assertTrue($request->scheduled_for->equalTo(Carbon::parse('2026-08-10 12:00:00')));
        $this->assertNotNull($user->fresh()->deletion_requested_at);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_successful_login_within_30_days_reactivates_the_account(): void
    {
        Carbon::setTestNow('2026-07-11 12:00:00');
        $user = $this->makeUser('restore-me');
        app(AccountCancellationService::class)->request($user, 'Temporary cancellation');

        Carbon::setTestNow('2026-07-25 09:30:00');
        $this->postJson('/api/mobile/v1/login', [
            'login' => 'restore-me',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('account_reactivated', true);

        $this->assertNull($user->fresh()->deletion_requested_at);
        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }


    public function test_login_after_the_30_day_deadline_is_rejected_and_account_is_deleted(): void
    {
        Carbon::setTestNow('2026-07-11 12:00:00');
        $user = $this->makeUser('too-late');
        app(AccountCancellationService::class)->request($user, 'Leaving');

        Carbon::setTestNow('2026-08-10 12:00:01');
        $this->postJson('/api/mobile/v1/login', [
            'login' => 'too-late',
            'password' => 'password123',
        ])->assertStatus(410);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_purge_removes_only_expired_cancelled_accounts_not_ordinary_inactive_users(): void
    {
        Carbon::setTestNow('2026-08-20 03:30:00');
        $ordinaryInactive = $this->makeUser('ordinary-inactive', now()->subDays(90));
        $expired = $this->makeUser('expired-cancelled', now()->subDays(45));
        $future = $this->makeUser('future-cancelled', now()->subDays(5));

        $expired->update(['deletion_requested_at' => now()->subDays(31)]);
        AccountDeletionRequest::create([
            'user_id' => $expired->id,
            'status' => 'pending',
            'requested_at' => now()->subDays(31),
            'scheduled_for' => now()->subDay(),
        ]);

        $future->update(['deletion_requested_at' => now()->subDays(5)]);
        AccountDeletionRequest::create([
            'user_id' => $future->id,
            'status' => 'pending',
            'requested_at' => now()->subDays(5),
            'scheduled_for' => now()->addDays(25),
        ]);

        $this->artisan('warqna:purge-cancelled-accounts')->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $expired->id]);
        $this->assertDatabaseHas('users', ['id' => $ordinaryInactive->id]);
        $this->assertDatabaseHas('users', ['id' => $future->id]);
    }


    public function test_explicitly_unaccepted_confirmation_is_rejected(): void
    {
        $user = $this->makeUser('not-confirmed');
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/mobile/v1/account/deletion-request', [
                'password' => 'password123',
                'confirmation' => false,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['confirmation']);

        $this->assertNull($user->fresh()->deletion_requested_at);
    }

    public function test_admin_account_cannot_be_cancelled(): void
    {
        $admin = $this->makeUser('protected-admin');
        $admin->update(['is_admin' => true]);
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/mobile/v1/account/deletion-request', ['password' => 'password123'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    private function makeUser(string $username, ?Carbon $lastSeenAt = null): User
    {
        $user = User::create([
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
            'last_seen_at' => $lastSeenAt ?? now(),
        ]);
        Wallet::create(['user_id' => $user->id, 'tokens' => 500, 'gems' => 0]);

        return $user;
    }
}
