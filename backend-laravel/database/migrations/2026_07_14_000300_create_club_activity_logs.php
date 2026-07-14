<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('club_activity_logs')) {
            Schema::create('club_activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('club_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type', 80)->index();
                $table->string('description', 500);
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['club_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_logs');
    }
};
