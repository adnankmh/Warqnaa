<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', function (Blueprint $table): void {
                if (!Schema::hasColumn('clubs', 'logo')) {
                    $table->string('logo')->nullable();
                }
                if (!Schema::hasColumn('clubs', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('clubs', 'total_points')) {
                    $table->unsignedBigInteger('total_points')->default(0);
                }
                if (!Schema::hasColumn('clubs', 'capacity')) {
                    $table->unsignedInteger('capacity')->default(20);
                }
                if (!Schema::hasColumn('clubs', 'league_tier')) {
                    $table->string('league_tier')->default('bronze');
                }
                if (!Schema::hasColumn('clubs', 'visibility')) {
                    $table->string('visibility')->default('public');
                }
            });
        }

        if (Schema::hasTable('club_members')) {
            Schema::table('club_members', function (Blueprint $table): void {
                if (!Schema::hasColumn('club_members', 'permissions')) {
                    $table->json('permissions')->nullable();
                }
                if (!Schema::hasColumn('club_members', 'total_points')) {
                    $table->unsignedBigInteger('total_points')->default(0);
                }
                if (!Schema::hasColumn('club_members', 'last_active_at')) {
                    $table->timestamp('last_active_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Additive compatibility migration; intentionally non-destructive.
    }
};
