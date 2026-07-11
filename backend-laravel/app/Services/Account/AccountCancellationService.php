<?php

namespace App\Services\Account;

use App\Models\{AccountDeletionRequest, User};
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class AccountCancellationService
{
    public function graceDays(): int
    {
        return max(30, (int) config('warqna.account_deletion_grace_days', 30));
    }

    public function request(User $user, ?string $reason = null): AccountDeletionRequest
    {
        abort_if($user->is_admin, 403, 'لا يمكن إلغاء حساب المدير الرئيسي.');

        return DB::transaction(function () use ($user, $reason) {
            $now = now();
            $scheduledFor = $now->copy()->addDays($this->graceDays());

            AccountDeletionRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => $now,
                    'updated_at' => $now,
                ]);

            $request = AccountDeletionRequest::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'requested_at' => $now,
                'scheduled_for' => $scheduledFor,
                'reason' => $reason,
            ]);

            $user->forceFill(['deletion_requested_at' => $now])->save();
            $user->tokens()->delete();

            return $request;
        });
    }

    public function reactivate(User $user): bool
    {
        $pending = AccountDeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$pending && $user->deletion_requested_at === null) {
            return false;
        }

        if ($pending?->scheduled_for && !$pending->scheduled_for->isFuture()) {
            DB::transaction(function () use ($pending, $user) {
                $locked = AccountDeletionRequest::query()->lockForUpdate()->find($pending->id);
                $lockedUser = User::query()->lockForUpdate()->find($user->id);
                if ($locked && $locked->status === 'pending') {
                    $locked->update(['status' => 'completed', 'completed_at' => now()]);
                }
                if ($lockedUser && !$lockedUser->is_admin) {
                    $lockedUser->tokens()->delete();
                    $lockedUser->delete();
                }
            });

            throw new GoneHttpException('انتهت مهلة استعادة الحساب البالغة 30 يوماً وتم حذف الحساب نهائياً.');
        }

        DB::transaction(function () use ($user) {
            $now = now();
            AccountDeletionRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => $now,
                    'updated_at' => $now,
                ]);
            $user->forceFill(['deletion_requested_at' => null])->save();
        });

        return true;
    }

    public function dueCount(): int
    {
        return AccountDeletionRequest::query()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->count();
    }

    public function purgeDue(): int
    {
        $deleted = 0;

        AccountDeletionRequest::query()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->orderBy('id')
            ->chunkById(50, function ($requests) use (&$deleted) {
                foreach ($requests as $request) {
                    DB::transaction(function () use ($request, &$deleted) {
                        $locked = AccountDeletionRequest::query()->lockForUpdate()->find($request->id);
                        if (!$locked || $locked->status !== 'pending' || !$locked->scheduled_for?->isPast()) {
                            return;
                        }

                        $user = User::query()->lockForUpdate()->find($locked->user_id);
                        if (!$user || $user->is_admin) {
                            $locked->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                            return;
                        }

                        $locked->update(['status' => 'completed', 'completed_at' => now()]);
                        $user->tokens()->delete();
                        $user->delete();
                        $deleted++;
                    });
                }
            });

        return $deleted;
    }
}
