<?php

use App\Models\{AccountDeletionRequest,User};
use Illuminate\Support\Facades\Schedule;

$purgeDays = max(1, (int) config('warqna.inactive_account_purge_days', 30));
$dryRun = (bool) config('warqna.inactive_account_dry_run', true);
Schedule::command('warqna:purge-inactive-accounts --days='.$purgeDays.($dryRun ? ' --dry-run' : ''))
    ->dailyAt('03:30')
    ->withoutOverlapping();

Schedule::command('warqna:cleanup-voice')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::call(function () {
    AccountDeletionRequest::query()
        ->where('status', 'pending')
        ->whereNotNull('scheduled_for')
        ->where('scheduled_for', '<=', now())
        ->with('user')
        ->chunkById(50, function ($requests) {
            foreach ($requests as $request) {
                $user = $request->user;
                if (!$user || $user->is_admin) {
                    $request->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    continue;
                }
                $request->update(['status' => 'completed', 'completed_at' => now()]);
                $user->delete();
            }
        });
})->hourly()->name('warqna-account-deletions')->withoutOverlapping();
