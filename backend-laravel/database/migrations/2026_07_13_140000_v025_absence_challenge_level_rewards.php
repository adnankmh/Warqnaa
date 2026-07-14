<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_players', function (Blueprint $table) {
            if (!Schema::hasColumn('room_players', 'manual_exit_count')) {
                $table->unsignedTinyInteger('manual_exit_count')->default(0)->after('missed_turns');
            }
            if (!Schema::hasColumn('room_players', 'absence_ejections')) {
                $table->unsignedTinyInteger('absence_ejections')->default(0)->after('manual_exit_count');
            }
            if (!Schema::hasColumn('room_players', 'return_blocked')) {
                $table->boolean('return_blocked')->default(false)->after('absence_ejections');
            }
        });

        Schema::create('challenge_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game_key', 80);
            $table->unsignedTinyInteger('stages_total')->default(12);
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->unsignedTinyInteger('attempts_left')->default(5);
            $table->string('status', 20)->default('active');
            $table->foreignId('current_opponent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_opponent_name', 80)->nullable();
            $table->json('stage_rewards');
            $table->json('claimed_stages')->nullable();
            $table->string('last_result', 20)->nullable();
            $table->string('last_client_result_id', 120)->nullable();
            $table->json('processed_result_ids')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('level_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('level');
            $table->string('reward_type', 40);
            $table->unsignedInteger('amount')->default(0);
            $table->json('reward_payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_reward_claims');
        Schema::dropIfExists('challenge_runs');
        Schema::table('room_players', function (Blueprint $table) {
            foreach (['manual_exit_count', 'absence_ejections', 'return_blocked'] as $column) {
                if (Schema::hasColumn('room_players', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
