<?php

namespace Tests\Feature;

use App\Models\{Friendship, User, Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V142MobileRealEnginesSocialEconomyTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_exposes_server_authoritative_free_play_engines(): void
    {
        $response = $this->getJson('/api/mobile/v1/games/catalog')->assertOk();
        $games = collect($response->json('games'));

        $this->assertTrue($games->contains(fn ($game) =>
            $game['key'] === 'tarneeb'
            && $game['hand_size'] === 13
            && $game['deck_size'] === 52
            && $game['free_play'] === true
            && $game['server_authoritative'] === true
        ));
        $this->assertTrue($games->contains(fn ($game) => $game['key'] === 'baloot'));
        $this->assertTrue($games->contains(fn ($game) => $game['key'] === 'trix_complex'));
    }

    public function test_admin_seed_contract_uses_requested_credentials_and_balance(): void
    {
        $this->seed();
        $admin = User::where('username', 'Adnan')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertSame('1000000000000000000', (string) $admin->wallet->tokens);
    }

    public function test_token_transfer_charges_sender_ten_percent_and_credits_admin(): void
    {
        $admin = User::create(['username' => 'Adnan', 'email' => 'adnan@example.test', 'password' => Hash::make('Adnan123'), 'is_admin' => true]);
        $sender = User::create(['username' => 'sender', 'email' => 'sender@example.test', 'password' => Hash::make('password')]);
        $receiver = User::create(['username' => 'receiver', 'email' => 'receiver@example.test', 'password' => Hash::make('password')]);
        Wallet::create(['user_id' => $admin->id, 'tokens' => 0, 'gems' => 0]);
        Wallet::create(['user_id' => $sender->id, 'tokens' => 2000, 'gems' => 0]);
        Wallet::create(['user_id' => $receiver->id, 'tokens' => 0, 'gems' => 0]);
        Friendship::create(['requester_id' => $sender->id, 'addressee_id' => $receiver->id, 'status' => 'accepted']);

        $token = $sender->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/mobile/v1/social/transfer', [
            'receiver' => 'receiver',
            'amount' => 1000,
        ])->assertOk()
            ->assertJsonPath('fee', 100)
            ->assertJsonPath('total_debited', 1100)
            ->assertJsonPath('wallet.tokens', 900);

        $this->assertSame('900', (string) $sender->wallet()->firstOrFail()->tokens);
        $this->assertSame('1000', (string) $receiver->wallet()->firstOrFail()->tokens);
        $this->assertSame('100', (string) $admin->wallet()->firstOrFail()->tokens);
    }
}
