<?php
namespace App\Services\Platform;

use Illuminate\Support\Facades\{Cache,DB,Schema};

class PlatformHealthService
{
    public function snapshot(): array
    {
        $tables = ['users','games','rooms','messages','notifications','store_items','inventory_items','friendships','feature_flags','app_releases'];
        $checks = [];
        foreach ($tables as $table) {
            try { $checks[$table] = Schema::hasTable($table); } catch (\Throwable) { $checks[$table] = false; }
        }
        $databaseConnected = false;
        try { DB::connection()->getPdo(); $databaseConnected = true; } catch (\Throwable) {}
        $cacheConnected = false;
        try { Cache::put('warqna-health', 'ok', 10); $cacheConnected = Cache::get('warqna-health') === 'ok'; } catch (\Throwable) {}
        $ok = $databaseConnected && $cacheConnected && !in_array(false, $checks, true);
        return [
            'ok' => $ok,
            'version' => config('warqna.version', '1.61.0'),
            'build' => (int) config('warqna.build', 161),
            'environment' => app()->environment(),
            'database_connected' => $databaseConnected,
            'cache_connected' => $cacheConnected,
            'database' => $checks,
            'counts' => config('warqna.health_show_counts') ? $this->counts() : null,
            'voice_turn_configured' => count((array) config('voice.turn_urls', [])) > 0,
            'queue' => config('queue.default'),
            'time' => now()->toIso8601String(),
        ];
    }

    private function counts(): array
    {
        $out = [];
        foreach (['users','games','rooms','messages','notifications','store_items','user_reports'] as $table) {
            try { $out[$table] = Schema::hasTable($table) ? DB::table($table)->count() : 0; } catch (\Throwable) { $out[$table] = 0; }
        }
        return $out;
    }
}
