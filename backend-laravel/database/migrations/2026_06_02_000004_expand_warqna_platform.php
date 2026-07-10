<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('profiles', function(Blueprint $t){
   $t->unsignedBigInteger('games_played')->default(0)->after('xp');
   $t->unsignedBigInteger('wins')->default(0)->after('games_played');
   $t->string('chat_color')->nullable()->after('text_color');
   $t->string('active_table_skin')->nullable()->after('badge');
   $t->unsignedTinyInteger('xp_boost_multiplier')->default(1)->after('pasha_days');
  });
  Schema::table('rooms', function(Blueprint $t){
   $t->unsignedInteger('entry_fee')->default(0)->after('password');
   $t->unsignedInteger('min_level')->default(1)->after('entry_fee');
   $t->string('target_score')->nullable()->after('max_players');
   $t->timestamp('started_at')->nullable()->after('status');
   $t->timestamp('finished_at')->nullable()->after('started_at');
  });
  Schema::table('messages', function(Blueprint $t){ $t->timestamp('read_at')->nullable()->after('body'); });
  Schema::create('anti_cheat_events', function(Blueprint $t){
   $t->id(); $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
   $t->string('event'); $t->unsignedTinyInteger('severity')->default(1); $t->json('meta')->nullable(); $t->string('ip')->nullable(); $t->timestamps();
  });
  Schema::create('tournament_entries', function(Blueprint $t){
   $t->id(); $t->foreignId('tournament_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->enum('status',['registered','checked_in','eliminated','winner'])->default('registered'); $t->timestamps(); $t->unique(['tournament_id','user_id']);
  });
  Schema::create('club_join_requests', function(Blueprint $t){
   $t->id(); $t->foreignId('club_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->enum('status',['pending','accepted','rejected'])->default('pending'); $t->timestamps(); $t->unique(['club_id','user_id']);
  });
  Schema::create('room_invites', function(Blueprint $t){
   $t->id(); $t->foreignId('room_id')->constrained()->cascadeOnDelete(); $t->foreignId('sender_id')->constrained('users')->cascadeOnDelete(); $t->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
   $t->enum('status',['pending','accepted','rejected'])->default('pending'); $t->timestamps(); $t->unique(['room_id','receiver_id']);
  });
 }
 public function down(): void {
  Schema::dropIfExists('room_invites'); Schema::dropIfExists('club_join_requests'); Schema::dropIfExists('tournament_entries'); Schema::dropIfExists('anti_cheat_events');
  Schema::table('messages', fn(Blueprint $t)=>$t->dropColumn('read_at'));
  Schema::table('rooms', fn(Blueprint $t)=>$t->dropColumn(['entry_fee','min_level','target_score','started_at','finished_at']));
  Schema::table('profiles', fn(Blueprint $t)=>$t->dropColumn(['games_played','wins','chat_color','active_table_skin','xp_boost_multiplier']));
 }
};
