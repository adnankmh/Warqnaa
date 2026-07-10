<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('game_ratings')){
   Schema::create('game_ratings', function(Blueprint $t){
    $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('game_key'); $t->integer('elo')->default(1200); $t->unsignedInteger('wins')->default(0); $t->unsignedInteger('losses')->default(0); $t->timestamps(); $t->unique(['user_id','game_key']);
   });
  }
  if(!Schema::hasTable('throwable_events')){
   Schema::create('throwable_events', function(Blueprint $t){
    $t->id(); $t->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); $t->string('item_key'); $t->unsignedInteger('cost')->default(0); $t->json('payload')->nullable(); $t->timestamps();
   });
  }
  if(!Schema::hasTable('daily_reward_claims')){
   Schema::create('daily_reward_claims', function(Blueprint $t){
    $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->date('claim_date'); $t->unsignedInteger('streak')->default(1); $t->unsignedInteger('coins')->default(0); $t->json('payload')->nullable(); $t->timestamps(); $t->unique(['user_id','claim_date']);
   });
  }
  if(!Schema::hasTable('club_wars')){
   Schema::create('club_wars', function(Blueprint $t){
    $t->id(); $t->string('key')->unique(); $t->foreignId('club_a_id')->nullable()->constrained('clubs')->nullOnDelete(); $t->foreignId('club_b_id')->nullable()->constrained('clubs')->nullOnDelete(); $t->timestamp('starts_at')->nullable(); $t->timestamp('ends_at')->nullable(); $t->string('status')->default('scheduled'); $t->json('score')->nullable(); $t->json('rewards')->nullable(); $t->timestamps();
   });
  }
  if(!Schema::hasTable('purchase_receipts')){
   Schema::create('purchase_receipts', function(Blueprint $t){
    $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('provider')->default('manual'); $t->string('package_key'); $t->string('receipt_token')->nullable()->unique(); $t->string('status')->default('pending'); $t->json('payload')->nullable(); $t->timestamps();
   });
  }
 }
 public function down(): void {
  foreach(['purchase_receipts','club_wars','daily_reward_claims','throwable_events','game_ratings'] as $table) Schema::dropIfExists($table);
 }
};
