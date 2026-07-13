<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prize_boxes')) {
            return;
        }

        Schema::create('prize_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('box_key', 80);
            $table->string('source_type', 40)->default('game_win');
            $table->string('source_key', 191);
            $table->date('awarded_date');
            $table->timestamp('opened_at')->nullable();
            $table->string('reward_type', 60)->nullable();
            $table->string('reward_key', 191)->nullable();
            $table->unsignedInteger('duration_hours')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'source_key'], 'prize_boxes_user_source_unique');
            $table->index(['user_id', 'awarded_date'], 'prize_boxes_user_day_index');
            $table->index(['user_id', 'opened_at'], 'prize_boxes_user_opened_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prize_boxes');
    }
};
