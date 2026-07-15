<?php
namespace App\Services\Wallet;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    private const MAX_TRANSACTION_AMOUNT = 1000000000000; // 1 trillion per operation guardrail.

    private function validateAmount(int $amount): void
    {
        if ($amount < 0 || $amount > self::MAX_TRANSACTION_AMOUNT) {
            throw new RuntimeException('Invalid wallet amount');
        }
    }

    public function debit(User $user, int $amount, string $type, array $meta=[]): void
    {
        $this->validateAmount($amount);
        DB::transaction(function() use($user,$amount,$type,$meta){
            $w=$user->wallet()->lockForUpdate()->first();
            if(!$w || $w->tokens < $amount) throw new RuntimeException('Insufficient tokens');
            if($amount > 0) $w->decrement('tokens',$amount);
            $user->walletTransactions()->create(['type'=>$type,'amount'=>-$amount,'meta'=>$meta]);
        });
    }

    public function credit(User $user, int $amount, string $type, array $meta=[]): void
    {
        $this->validateAmount($amount);
        DB::transaction(function() use($user,$amount,$type,$meta){
            $w=$user->wallet()->lockForUpdate()->firstOrCreate(['user_id'=>$user->id],['tokens'=>50]);
            if($amount > 0) $w->increment('tokens',$amount);
            $user->walletTransactions()->create(['type'=>$type,'amount'=>$amount,'meta'=>$meta]);
        });
    }

    /**
     * Credits every paid game-economy transaction to the primary Adnan admin.
     * The buyer is never credited back and self-purchases do not create income.
     */
    public function creditPrimaryAdminRevenue(User $buyer, int $amount, string $type='store_sale_income', array $meta=[]): void
    {
        $this->validateAmount($amount);
        if ($amount <= 0) return;
        $admin = User::whereRaw('LOWER(username) = ?', ['adnan'])->where('is_admin', true)->first()
            ?? User::where('is_admin', true)->orderBy('id')->first();
        if (!$admin || (int)$admin->id === (int)$buyer->id) return;
        $this->credit($admin, $amount, $type, array_merge($meta, ['buyer_id'=>(int)$buyer->id]));
    }
}
