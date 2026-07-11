<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_releases')) {
            return;
        }

        foreach (['web', 'android', 'ios'] as $platform) {
            DB::table('app_releases')
                ->where('platform', $platform)
                ->where('build_number', '<', 154)
                ->update(['active' => false, 'updated_at' => now()]);

            DB::table('app_releases')->updateOrInsert(
                ['platform' => $platform, 'version' => '1.54.0', 'build_number' => 154],
                [
                    'required' => false,
                    'active' => true,
                    'notes' => 'Warqna v154 merge-safe production package',
                    'download_url' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_releases')) {
            return;
        }

        DB::table('app_releases')
            ->where('version', '1.54.0')
            ->where('build_number', 154)
            ->delete();

        DB::table('app_releases')
            ->where('version', '1.53.0')
            ->where('build_number', 153)
            ->update(['active' => true, 'updated_at' => now()]);
    }
};
