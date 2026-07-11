<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_players', function (Blueprint $table) {
            $table->timestamp('voice_joined_at')->nullable()->after('missed_turns');
            $table->timestamp('voice_last_seen_at')->nullable()->after('voice_joined_at');
            $table->boolean('voice_muted')->default(false)->after('voice_last_seen_at');
            $table->boolean('voice_deafened')->default(false)->after('voice_muted');
        });

        Schema::create('voice_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('signal_type', 24);
            $table->json('payload');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['room_id', 'recipient_id', 'delivered_at'], 'voice_signal_delivery_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_signals');
        Schema::table('room_players', function (Blueprint $table) {
            $table->dropColumn(['voice_joined_at', 'voice_last_seen_at', 'voice_muted', 'voice_deafened']);
        });
    }
};
