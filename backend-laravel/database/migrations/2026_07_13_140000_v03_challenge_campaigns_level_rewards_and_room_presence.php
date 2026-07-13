<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            if (!Schema::hasColumn('clubs', 'visibility')) $table->string('visibility', 20)->default('public')->index();
            if (!Schema::hasColumn('clubs', 'logo')) $table->string('logo', 500)->nullable();
            if (!Schema::hasColumn('clubs', 'description')) $table->string('description', 500)->nullable();
            if (!Schema::hasColumn('clubs', 'total_points')) $table->unsignedBigInteger('total_points')->default(0);
            if (!Schema::hasColumn('clubs', 'capacity')) $table->unsignedSmallInteger('capacity')->default(50);
            if (!Schema::hasColumn('clubs', 'league_tier')) $table->string('league_tier', 30)->default('bronze');
        });

        Schema::table('room_players', function (Blueprint $table) {
            if (!Schema::hasColumn('room_players', 'voluntary_leave_count')) $table->unsignedTinyInteger('voluntary_leave_count')->default(0);
            if (!Schema::hasColumn('room_players', 'rejoin_blocked')) $table->boolean('rejoin_blocked')->default(false);
            if (!Schema::hasColumn('room_players', 'away_since')) $table->timestamp('away_since')->nullable();
        });

        Schema::create('challenge_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('name');
            $table->unsignedTinyInteger('stage_count')->default(12);
            $table->unsignedTinyInteger('starting_lives')->default(5);
            $table->boolean('active')->default(true);
            $table->json('rewards')->nullable();
            $table->timestamps();
        });

        Schema::create('challenge_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('game_key', 80)->index();
            $table->unsignedTinyInteger('stage')->default(1);
            $table->unsignedTinyInteger('lives')->default(5);
            $table->unsignedSmallInteger('wins')->default(0);
            $table->unsignedSmallInteger('losses')->default(0);
            $table->enum('status', ['active','completed','failed','abandoned'])->default('active')->index();
            $table->foreignId('opponent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('claimed_stages')->nullable();
            $table->json('history')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id','status']);
        });

        Schema::create('level_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('level');
            $table->string('reward_type', 50);
            $table->string('reward_key', 150)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id','level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_reward_claims');
        Schema::dropIfExists('challenge_runs');
        Schema::dropIfExists('challenge_campaigns');

        Schema::table('room_players', function (Blueprint $table) {
            foreach (['voluntary_leave_count','rejoin_blocked','away_since'] as $column) {
                if (Schema::hasColumn('room_players', $column)) $table->dropColumn($column);
            }
        });
    }
};
