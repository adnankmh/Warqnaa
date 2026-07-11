<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB,Schema};

class ProductionCheck extends Command
{
    protected $signature = 'warqna:production-check {--strict : Fail when a production requirement is missing}';
    protected $description = 'Check Warqna production configuration, database, storage, security, voice, and release readiness.';

    public function handle(): int
    {
        $strict = (bool) $this->option('strict');
        $failures = 0;
        $checks = [
            ['APP_KEY configured', (string) config('app.key') !== ''],
            ['APP_DEBUG disabled', !app()->environment('production') || !config('app.debug')],
            ['APP_URL uses HTTPS', !app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')],
            ['Database is reachable', $this->databaseReady()],
            ['Core tables exist', $this->tablesReady()],
            ['Storage is writable', is_writable(storage_path()) && is_writable(base_path('bootstrap/cache'))],
            ['CORS is restricted', !app()->environment('production') || !in_array('*', (array) config('cors.allowed_origins'), true)],
            ['Admin password changed', !app()->environment('production') || !in_array((string) env('ADMIN_PASSWORD'), ['', 'Adnan123', 'password'], true)],
            ['Local demo disabled', !app()->environment('production') || !config('warqna.allowed_local_demo')],
            ['TURN configured for voice', count((array) config('voice.turn_urls', [])) > 0 || !app()->environment('production')],
            ['Queue is not sync', !app()->environment('production') || config('queue.default') !== 'sync'],
            ['Inactive deletion starts in dry-run', !app()->environment('production') || config('warqna.inactive_account_dry_run')],
        ];
        foreach ($checks as [$label, $ok]) {
            $ok ? $this->components->info($label) : $this->components->error($label);
            if (!$ok) $failures++;
        }
        $this->newLine();
        $this->line("Version: ".config('warqna.version').' (build '.config('warqna.build').')');
        $this->line('Environment: '.app()->environment());
        $this->line("Failures: {$failures}");
        return ($strict && $failures > 0) ? self::FAILURE : self::SUCCESS;
    }

    private function databaseReady(): bool
    {
        try { DB::connection()->getPdo(); return true; } catch (\Throwable) { return false; }
    }

    private function tablesReady(): bool
    {
        try {
            foreach (['users','profiles','wallets','games','rooms','store_items','feature_flags'] as $table) {
                if (!Schema::hasTable($table)) return false;
            }
            return true;
        } catch (\Throwable) { return false; }
    }
}
