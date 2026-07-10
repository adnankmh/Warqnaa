<?php

use App\Services\GameEngine\EngineRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('games')) return;
        $now = now();
        foreach (EngineRegistry::all() as $key => $meta) {
            DB::table('games')->updateOrInsert(
                ['key' => $key],
                [
                    'name' => json_encode($meta['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'min_players' => $meta['min'],
                    'max_players' => $meta['max'],
                    'partnership' => $meta['partnership'],
                    'rules' => json_encode([
                        'engine' => $meta['engine'],
                        'summary' => $meta['rules'],
                        'actions' => $meta['actions'],
                        'hand_size' => $meta['hand'],
                        'deck_size' => $meta['deck'],
                        'server_authoritative' => true,
                        'free_play' => true,
                        'catalog_version' => 'v142',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        // Keep game records to avoid deleting rooms, history or tournament relations.
    }
};
