<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('presence_sessions')){
   Schema::create('presence_sessions', function(Blueprint $t){
    $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('scope')->default('site');
    $t->string('room_code')->nullable(); $t->timestamp('last_seen_at')->nullable(); $t->json('meta')->nullable(); $t->timestamps();
    $t->index(['scope','room_code']); $t->index(['user_id','last_seen_at']);
   });
  }
  if(!Schema::hasTable('economy_seasons')){
   Schema::create('economy_seasons', function(Blueprint $t){
    $t->id(); $t->string('key')->unique(); $t->json('name'); $t->timestamp('starts_at')->nullable(); $t->timestamp('ends_at')->nullable();
    $t->boolean('active')->default(false); $t->json('rewards')->nullable(); $t->timestamps();
   });
  }
  if(!Schema::hasTable('store_offers')){
   Schema::create('store_offers', function(Blueprint $t){
    $t->id(); $t->string('key')->unique(); $t->json('title'); $t->json('description')->nullable(); $t->unsignedTinyInteger('discount_percent')->default(0);
    $t->timestamp('starts_at')->nullable(); $t->timestamp('ends_at')->nullable(); $t->boolean('active')->default(true); $t->json('item_keys')->nullable(); $t->timestamps();
   });
  }
  if(!Schema::hasTable('rare_collectibles')){
   Schema::create('rare_collectibles', function(Blueprint $t){
    $t->id(); $t->string('key')->unique(); $t->json('name'); $t->string('rarity')->default('rare'); $t->unsignedInteger('supply')->nullable();
    $t->unsignedInteger('claimed')->default(0); $t->json('payload')->nullable(); $t->boolean('active')->default(true); $t->timestamps();
   });
  }
  if(!Schema::hasTable('system_metrics')){
   Schema::create('system_metrics', function(Blueprint $t){
    $t->id(); $t->string('key'); $t->string('value')->nullable(); $t->json('meta')->nullable(); $t->timestamps(); $t->index('key');
   });
  }
 }
 public function down(): void {
  foreach(['system_metrics','rare_collectibles','store_offers','economy_seasons','presence_sessions'] as $table) Schema::dropIfExists($table);
 }
};
