<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) return;

        DB::table('users')
            ->whereRaw('LOWER(username) = ?', ['adnan'])
            ->update(['is_admin' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // The primary administrator flag is intentionally not revoked on rollback.
    }
};
