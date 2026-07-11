<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('store_items') || !Schema::hasColumn('store_items', 'category')) {
            return;
        }

        // The original column was an enum/check constraint. New releases add
        // themes and profile covers, so convert it once to an extensible string.
        if (!Schema::hasColumn('store_items', 'category_v156')) {
            Schema::table('store_items', function (Blueprint $table) {
                $table->string('category_v156', 40)->default('effect');
            });
        }

        DB::table('store_items')->update([
            'category_v156' => DB::raw('category'),
        ]);

        try {
            Schema::table('store_items', function (Blueprint $table) {
                $table->dropIndex('idx_store_items_category_active');
            });
        } catch (Throwable) {
            // The index may not exist on an older local database.
        }

        Schema::table('store_items', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->renameColumn('category_v156', 'category');
        });

        try {
            Schema::table('store_items', function (Blueprint $table) {
                $table->index(['category', 'active'], 'idx_store_items_category_active');
            });
        } catch (Throwable) {
            // Keep the migration idempotent if an equivalent index already exists.
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: restoring the old enum could invalidate
        // newer categories and delete legitimate store data.
    }
};
