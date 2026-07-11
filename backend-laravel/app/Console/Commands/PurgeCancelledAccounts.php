<?php

namespace App\Console\Commands;

use App\Services\Account\AccountCancellationService;
use Illuminate\Console\Command;

class PurgeCancelledAccounts extends Command
{
    protected $signature = 'warqna:purge-cancelled-accounts {--dry-run}';
    protected $description = 'Permanently delete only accounts whose 30-day cancellation grace period has expired.';

    public function handle(AccountCancellationService $cancellation): int
    {
        if ($this->option('dry-run')) {
            $this->info($cancellation->dueCount().' cancelled account(s) are due for permanent deletion.');
            return self::SUCCESS;
        }

        $deleted = $cancellation->purgeDue();
        $this->info("Permanently deleted {$deleted} cancelled account(s).");

        return self::SUCCESS;
    }
}
