<?php

namespace App\Console\Commands;

use App\Services\Account\AccountCancellationService;
use Illuminate\Console\Command;

class PurgeInactiveAccounts extends Command
{
    protected $signature = 'warqna:purge-inactive-accounts {--days=30} {--dry-run}';
    protected $description = 'Legacy safe alias. It purges only accounts explicitly cancelled by their owners, never ordinary inactive accounts.';

    public function handle(AccountCancellationService $cancellation): int
    {
        if ($this->option('dry-run')) {
            $this->info($cancellation->dueCount().' explicitly cancelled account(s) are due for deletion.');
            return self::SUCCESS;
        }

        $deleted = $cancellation->purgeDue();
        $this->info("Permanently deleted {$deleted} explicitly cancelled account(s).");

        return self::SUCCESS;
    }
}
