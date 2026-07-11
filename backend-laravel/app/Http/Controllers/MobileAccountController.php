<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Services\Account\AccountCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};

class MobileAccountController extends Controller
{
    public function export(Request $request)
    {
        $user = $request->user()->load([
            'profile', 'wallet', 'inventoryItems', 'walletTransactions', 'notifications', 'sentMessages', 'receivedMessages',
        ]);
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            ],
            'profile' => $user->profile,
            'wallet' => $user->wallet,
            'inventory' => $user->inventoryItems,
            'wallet_transactions' => $user->walletTransactions()->latest()->limit(5000)->get(),
            'notifications' => $user->notifications()->latest()->limit(1000)->get(),
            'messages' => [
                'sent' => $user->sentMessages()->latest()->limit(2000)->get(),
                'received' => $user->receivedMessages()->latest()->limit(2000)->get(),
            ],
        ];

        return response()->json(['ok' => true, 'export' => $payload]);
    }

    public function sessions(Request $request)
    {
        $current = $request->user()->currentAccessToken()?->getKey();

        return response()->json([
            'ok' => true,
            'sessions' => $request->user()->tokens()->latest()->get()->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'current' => (int) $token->id === (int) $current,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
            ]),
        ]);
    }

    public function revokeSession(Request $request, int $tokenId)
    {
        $token = $request->user()->tokens()->whereKey($tokenId)->firstOrFail();
        $current = $request->user()->currentAccessToken()?->getKey();
        $token->delete();

        return response()->json([
            'ok' => true,
            'message' => (int) $current === $tokenId ? 'تم تسجيل الخروج من هذه الجلسة.' : 'تم إغلاق الجلسة.',
            'current_revoked' => (int) $current === $tokenId,
        ]);
    }

    public function requestDeletion(Request $request, AccountCancellationService $cancellation)
    {
        $user = $request->user();
        abort_if($user->is_admin, 403, 'لا يمكن إلغاء حساب المدير الرئيسي.');

        $data = $request->validate([
            'password' => 'required|string|max:120',
            'reason' => 'nullable|string|max:500',
            'confirmation' => 'sometimes|accepted',
        ]);
        abort_unless(Hash::check($data['password'], $user->password), 422, 'كلمة المرور غير صحيحة.');

        $deletion = $cancellation->request($user, $data['reason'] ?? null);
        $days = $cancellation->graceDays();

        return response()->json([
            'ok' => true,
            'account_cancelled' => true,
            'grace_days' => $days,
            'message' => "تم إلغاء الحساب وتسجيل الخروج. سيُحذف نهائياً إذا لم تفتحه وتسجل الدخول خلال {$days} يوماً.",
            'scheduled_for' => $deletion->scheduled_for?->toIso8601String(),
        ]);
    }

    public function cancelDeletion(Request $request, AccountCancellationService $cancellation)
    {
        $restored = $cancellation->reactivate($request->user());
        abort_unless($restored, 404, 'لا يوجد إلغاء حساب معلق.');

        return response()->json([
            'ok' => true,
            'message' => 'تمت استعادة الحساب وإلغاء الحذف النهائي.',
        ]);
    }
}
