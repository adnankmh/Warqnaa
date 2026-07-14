<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'admin_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('admin_permissions')->nullable()->after('is_admin');
            });
        }

        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', function (Blueprint $table) {
                if (!Schema::hasColumn('clubs', 'image_url')) $table->text('image_url')->nullable();
                if (!Schema::hasColumn('clubs', 'banner_url')) $table->text('banner_url')->nullable();
                if (!Schema::hasColumn('clubs', 'settings')) $table->json('settings')->nullable();
            });
        }

        if (!Schema::hasTable('club_activity_logs')) {
            Schema::create('club_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('club_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event', 80);
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->index(['club_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('challenge_runs')) {
            Schema::create('challenge_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('game_key', 80);
                $table->unsignedTinyInteger('stage')->default(0);
                $table->unsignedTinyInteger('lives')->default(5);
                $table->unsignedTinyInteger('stages_total')->default(15);
                $table->enum('status', ['active', 'completed', 'failed'])->default('active');
                $table->json('claimed_stages')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('level_reward_claims')) {
            Schema::create('level_reward_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('level');
                $table->string('reward_type', 50);
                $table->string('reward_value', 190)->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'level']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('level_reward_claims');
        Schema::dropIfExists('challenge_runs');
        Schema::dropIfExists('club_activity_logs');
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', function (Blueprint $table) {
                foreach (['image_url', 'banner_url', 'settings'] as $column) {
                    if (Schema::hasColumn('clubs', $column)) $table->dropColumn($column);
                }
            });
        }
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'admin_permissions')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('admin_permissions'));
        }
    }
};
