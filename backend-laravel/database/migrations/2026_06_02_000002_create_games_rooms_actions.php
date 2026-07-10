<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('games', function(Blueprint $t){$t->id();$t->string('key')->unique();$t->json('name');$t->unsignedTinyInteger('min_players');$t->unsignedTinyInteger('max_players');$t->boolean('partnership')->default(false);$t->json('rules')->nullable();$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('rooms', function(Blueprint $t){$t->id();$t->string('code')->unique();$t->foreignId('game_id')->constrained()->cascadeOnDelete();$t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();$t->enum('visibility',['public','friends','private'])->default('public');$t->string('password')->nullable();$t->enum('status',['waiting','bidding','playing','finished','closed'])->default('waiting');$t->unsignedTinyInteger('max_players')->default(4);$t->json('state')->nullable();$t->timestamps();});
  Schema::create('room_players', function(Blueprint $t){$t->id();$t->foreignId('room_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('bot_key')->nullable();$t->string('seat')->nullable();$t->boolean('is_bot')->default(false);$t->boolean('connected')->default(true);$t->unsignedTinyInteger('missed_turns')->default(0);$t->timestamps();});
  Schema::create('game_actions', function(Blueprint $t){$t->id();$t->foreignId('room_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('action');$t->json('payload')->nullable();$t->boolean('valid')->default(true);$t->string('ip')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('game_actions');Schema::dropIfExists('room_players');Schema::dropIfExists('rooms');Schema::dropIfExists('games');}
};
