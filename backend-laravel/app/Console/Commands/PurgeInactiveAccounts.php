<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeInactiveAccounts extends Command
{
    protected $signature = 'warqna:purge-inactive-accounts {--days=30} {--dry-run}';
    protected $description = 'Delete non-admin accounts that have not been opened during the configured retention window.';

    public function handle(): int
    {
        $days = max(1, (int)$this->option('days'));
        $cutoff = now()->subDays($days);
        $query = User::query()->where('is_admin', false)->where(function ($q) use ($cutoff) {
            $q->where('last_seen_at','<',$cutoff)
              ->orWhere(function ($nested) use ($cutoff) {
                  $nested->whereNull('last_seen_at')->where('created_at','<',$cutoff);
              });
        });
        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("{$count} account(s) would be deleted.");
            return self::SUCCESS;
        }
        $query->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) $user->delete();
        });
        $this->info("Deleted {$count} inactive account(s).");
        return self::SUCCESS;
    }
}
